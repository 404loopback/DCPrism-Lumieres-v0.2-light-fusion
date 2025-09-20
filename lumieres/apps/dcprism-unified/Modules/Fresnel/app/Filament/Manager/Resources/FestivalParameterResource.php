<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\FestivalParameter;
use Modules\Fresnel\app\Models\Parameter;
use UnitEnum;

class FestivalParameterResource extends Resource
{
    protected static ?string $model = FestivalParameter::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Paramètres du Festival';

    protected static ?string $modelLabel = 'Paramètre';

    protected static ?string $pluralModelLabel = 'Paramètres';

    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration Festival';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Personnalisation Festival')
                    ->description('Personnalisez ce paramètre pour votre festival')
                    ->icon('heroicon-o-star')
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('Activé pour ce Festival')
                            ->default(true)
                            ->helperText('Le paramètre sera-t-il utilisé dans la nomenclature de ce festival ?'),

                        TextInput::make('display_order')
                            ->label('Ordre d\'Affichage')
                            ->numeric()
                            ->default(0)
                            ->helperText('Position dans la nomenclature (0 = premier)'),

                        TextInput::make('custom_default_value')
                            ->label('Valeur par Défaut Personnalisée')
                            ->helperText('Remplace la valeur par défaut du paramètre global pour ce festival'),

                        KeyValue::make('custom_formatting_rules')
                            ->label('Règles de Formatage Personnalisées')
                            ->keyLabel('Règle')
                            ->valueLabel('Valeur')
                            ->helperText('Règles spécifiques au festival, complètent les règles globales')
                            ->columnSpanFull(),

                        Textarea::make('festival_specific_notes')
                            ->label('Notes Spécifiques au Festival')
                            ->rows(3)
                            ->helperText('Documentation interne pour ce paramètre dans ce festival')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parameter.name')
                    ->label('Paramètre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (FestivalParameter $record): string => $record->parameter->description ?? ''
                    ),

                TextColumn::make('parameter.category')
                    ->label('Catégorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'technical' => 'primary',
                        'video' => 'success',
                        'audio' => 'warning',
                        'subtitle' => 'danger',
                        'content' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('parameter.type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Parameter::getAvailableTypes()[$state] ?? $state),

                TextColumn::make('display_order')
                    ->label('Ordre')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('custom_default_value')
                    ->label('Valeur Personnalisée')
                    ->limit(20)
                    ->placeholder('Valeur globale'),

                TextColumn::make('parameter_status')
                    ->label('Type')
                    ->badge()
                    ->getStateUsing(function (FestivalParameter $record): string {
                        if ($record->parameter->is_system) {
                            return 'Système';
                        }
                        if ($record->parameter->is_required) {
                            return 'Obligatoire';
                        }

                        return 'Optionnel';
                    })
                    ->color(function (FestivalParameter $record): string {
                        if ($record->parameter->is_system) {
                            return 'danger';
                        }
                        if ($record->parameter->is_required) {
                            return 'warning';
                        }

                        return 'success';
                    }),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parameter.category')
                    ->label('Catégorie')
                    ->options(Parameter::getAvailableCategories()),

                SelectFilter::make('parameter.is_system')
                    ->label('Type de Paramètre')
                    ->options([
                        1 => 'Paramètres Système',
                        0 => 'Paramètres Optionnels',
                    ]),

            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Éditer'),
                    DeleteAction::make()
                        ->label('Supprimer')
                        ->visible(fn (FestivalParameter $record): bool => ! $record->parameter->is_system && ! $record->parameter->is_required
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer le paramètre')
                        ->modalDescription('Voulez-vous vraiment supprimer ce paramètre du festival ? Cette action est irréversible.')
                        ->modalSubmitActionLabel('Supprimer'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('display_order')
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['parameter'])
                    ->where('festival_id', auth()->user()->current_festival_id ?? 1);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => FestivalParameterResource\Pages\ListFestivalParameters::route('/'),
            'edit' => FestivalParameterResource\Pages\EditFestivalParameter::route('/{record}/edit'),
        ];
    }
}
