<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources;

use Modules\Fresnel\app\Models\Nomenclature;
use Modules\Fresnel\app\Models\Parameter;
use Modules\Fresnel\app\Models\Festival;
use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
// use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class NomenclatureResource extends Resource
{
    protected static ?string $model = Nomenclature::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Nomenclature Festival';
    
    protected static ?string $modelLabel = 'Nomenclature Festival';
    
    protected static ?string $pluralModelLabel = 'Nomenclature Festival';
    
    protected static ?int $navigationSort = 4;
    
    protected static string|UnitEnum|null $navigationGroup = 'Configuration Festival';
    
    // protected static bool $shouldRegisterNavigation = false; // Masquer de la navigation

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Configuration de la Nomenclature')
                    ->description('Définition de la nomenclature pour ce festival')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('parameter_id')
                            ->label('Paramètre')
                            ->options(Parameter::active()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->helperText('Paramètre utilisé dans la nomenclature'),

                        TextInput::make('order_position')
                            ->label('Position')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->helperText('Position dans la nomenclature finale (1, 2, 3...)'),

                        TextInput::make('separator')
                            ->label('Séparateur')
                            ->maxLength(10)
                            ->default('_')
                            ->helperText('Caractère séparateur après ce paramètre'),
                    ])->columns(3),

                Section::make('Formatage')
                    ->description('Préfixes, suffixes et formatage du paramètre')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        TextInput::make('prefix')
                            ->label('Préfixe')
                            ->maxLength(20)
                            ->helperText('Texte ajouté avant la valeur du paramètre'),

                        TextInput::make('suffix')
                            ->label('Suffixe')
                            ->maxLength(20)
                            ->helperText('Texte ajouté après la valeur du paramètre'),

                        TextInput::make('default_value')
                            ->label('Valeur par Défaut')
                            ->helperText('Valeur utilisée si le paramètre est vide'),

                        KeyValue::make('formatting_rules')
                            ->label('Règles de Formatage')
                            ->helperText('Règles de transformation (uppercase, lowercase, etc.)')
                            ->keyLabel('Règle')
                            ->valueLabel('Configuration')
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Règles Conditionnelles')
                    ->description('Logique conditionnelle avancée')
                    ->icon('heroicon-o-code-bracket')
                    ->collapsible()
                    ->schema([
                        Repeater::make('conditional_rules')
                            ->label('Règles Conditionnelles')
                            ->schema([
                                Select::make('field')
                                    ->label('Champ Condition')
                                    ->options([
                                        'format' => 'Format de contenu',
                                        'genre' => 'Genre',
                                        'duration' => 'Durée',
                                        'year' => 'Année',
                                        'country' => 'Pays',
                                    ]),

                                Select::make('operator')
                                    ->label('Opérateur')
                                    ->options([
                                        '=' => 'Égal à',
                                        '!=' => 'Différent de',
                                        '>' => 'Supérieur à',
                                        '<' => 'Inférieur à',
                                        'contains' => 'Contient',
                                        'starts_with' => 'Commence par',
                                        'ends_with' => 'Finit par',
                                    ]),

                                TextInput::make('value')
                                    ->label('Valeur Attendue'),

                                TextInput::make('then')
                                    ->label('Alors (valeur de remplacement)'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Options')
                    ->description('Paramètres d\'activation')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->helperText('Cette règle de nomenclature est active'),

                        Toggle::make('is_required')
                            ->label('Requis')
                            ->helperText('Ce paramètre doit obligatoirement être présent dans la nomenclature'),
                    ])->columns(2),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('order_position')
                    ->label('Position')
                    ->sortable()
                    ->badge(),

                TextColumn::make('parameter.name')
                    ->label('Paramètre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('parameter.code')
                    ->label('Code')
                    ->badge(),

                TextColumn::make('prefix')
                    ->label('Préfixe')
                    ->placeholder('-'),

                TextColumn::make('suffix')
                    ->label('Suffixe')
                    ->placeholder('-'),

                TextColumn::make('separator')
                    ->label('Séparateur')
                    ->placeholder('-'),

                TextColumn::make('preview')
                    ->label('Aperçu')
                    ->getStateUsing(fn ($record) => $record->getPreview())
                    ->badge()
                    ->color('success'),

                BooleanColumn::make('is_required')
                    ->label('Requis'),

                BooleanColumn::make('is_active')
                    ->label('Actif'),
            ])
            ->filters([
                SelectFilter::make('parameter_id')
                    ->label('Paramètre')
                    ->options(Parameter::active()->pluck('name', 'id')),

                SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Éditer'),
                    DeleteAction::make()
                        ->label('Supprimer'),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order_position')
            ->reorderable('order_position');
    }

    /**
     * Filtrer les nomenclatures pour le festival sélectionné
     */
    public static function getEloquentQuery(): Builder
    {
        $festivalId = Session::get('selected_festival_id');
        
        if (!$festivalId) {
            // Si aucun festival sélectionné, retourner une query vide
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        return parent::getEloquentQuery()
            ->where('festival_id', $festivalId)
            ->with(['parameter']);
    }

    /**
     * Hook avant création : associer au festival sélectionné
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $festivalId = Session::get('selected_festival_id');
        
        if (!$festivalId) {
            throw new \Exception('Aucun festival sélectionné. Veuillez d\'abord choisir un festival à administrer.');
        }
        
        $data['festival_id'] = $festivalId;
        
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => NomenclatureResource\Pages\ListNomenclatures::route('/'),
            'create' => NomenclatureResource\Pages\CreateNomenclature::route('/create'),
            'edit' => NomenclatureResource\Pages\EditNomenclature::route('/{record}/edit'),
        ];
    }
}
