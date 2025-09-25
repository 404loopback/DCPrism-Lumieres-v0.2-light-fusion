<?php

namespace Modules\Fresnel\app\Filament\Resources;

use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Filament\Resources\ActivityLogResource\Pages;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Logs d\'audit';

    protected static ?string $modelLabel = 'Log d\'audit';

    protected static ?string $pluralModelLabel = 'Logs d\'audit';

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('log_name')
                            ->label('Catégorie de log')
                            ->disabled(),
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject_type')
                            ->label('Type de sujet')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject_id')
                            ->label('ID du sujet')
                            ->disabled(),
                        Forms\Components\TextInput::make('causer_type')
                            ->label('Type d\'auteur')
                            ->disabled(),
                        Forms\Components\TextInput::make('causer_id')
                            ->label('ID de l\'auteur')
                            ->disabled(),
                    ])->columns(2),
                Section::make('Propriétés')
                    ->schema([
                        Forms\Components\KeyValue::make('properties')
                            ->label('Propriétés JSON')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('log_name')
                    ->label('Catégorie')
                    ->colors([
                        'primary' => 'default',
                        'success' => 'authentication',
                        'warning' => 'admin',
                        'danger' => 'security',
                        'info' => 'system',
                        'secondary' => 'dcp_processing',
                    ])
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('causer.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->placeholder('Système'),
                TextColumn::make('subject_type')
                    ->label('Sujet')
                    ->formatStateUsing(fn ($state) => class_basename($state ?? 'N/A'))
                    ->searchable(),
                TextColumn::make('properties')
                    ->label('IP')
                    ->formatStateUsing(fn ($record) => $record->properties['ip_address'] ?? 'N/A')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Catégorie')
                    ->options([
                        'authentication' => 'Authentification',
                        'admin' => 'Administration',
                        'security' => 'Sécurité',
                        'system' => 'Système',
                        'dcp_processing' => 'Traitement DCP',
                        'default' => 'Général',
                    ]),
                Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Du '.Carbon::parse($data['created_from'])->format('d/m/Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Au '.Carbon::parse($data['created_until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Voir'),
            ])
            ->bulkActions([
                // Pas d'actions bulk pour préserver l'intégrité des logs
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Les logs d'audit ne peuvent pas être créés manuellement
    }

    public static function canEdit($record): bool
    {
        return false; // Les logs d'audit ne peuvent pas être modifiés
    }

    public static function canDelete($record): bool
    {
        return false; // Les logs d'audit ne peuvent pas être supprimés individuellement
    }
}
