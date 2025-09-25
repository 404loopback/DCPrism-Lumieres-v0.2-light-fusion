<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
// use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Session;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Nomenclature;
use Modules\Fresnel\app\Models\Parameter;
use Modules\Fresnel\app\Services\FestivalAccessService;
use UnitEnum;

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
                        // En création : Sélecteur de paramètre
                        Select::make('festival_parameter_id')
                            ->label('Paramètre Festival')
                            ->options(function () {
                                $festivalId = Session::get('selected_festival_id');
                                if (!$festivalId) {
                                    return [];
                                }
                                return \Modules\Fresnel\app\Models\FestivalParameter::where('festival_id', $festivalId)
                                    ->with('parameter')
                                    ->get()
                                    ->mapWithKeys(function ($fp) {
                                        $label = $fp->parameter->name;
                                        if ($fp->is_system) {
                                            $label .= ' (Système)';
                                        }
                                        if (!$fp->is_visible_in_nomenclature) {
                                            $label .= ' [Masqué]';
                                        }
                                        return [$fp->id => $label];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Paramètre du festival utilisé dans la nomenclature')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $fp = \Modules\Fresnel\app\Models\FestivalParameter::with('parameter')->find($state);
                                    if ($fp) {
                                        // Auto-remplir le parameter_id pour compatibilité
                                        $set('parameter_id', $fp->parameter_id);
                                        // Pré-remplir certains champs basés sur les valeurs du festival
                                        if ($fp->custom_default_value) {
                                            $set('default_value', $fp->custom_default_value);
                                        }
                                    }
                                }
                            })
                            ->hiddenOn('edit'), // Masqué en mode édition
                            
                        // En édition : Affichage en lecture seule du paramètre
                        \Filament\Forms\Components\Placeholder::make('parameter_info')
                            ->label('Paramètre Festival')
                            ->content(function ($record) {
                                if (!$record) return 'Nouveau paramètre';
                                $parameter = $record->resolveParameter();
                                if (!$parameter) return 'Paramètre inconnu';
                                
                                // Icône et couleur du paramètre
                                $icon = $parameter->icon ? "⚙️" : "🔧";
                                $colorIcon = match($parameter->color ?? 'gray') {
                                    'blue' => '🔵', 'green' => '🟢', 'purple' => '🟣',
                                    'orange' => '🟠', 'yellow' => '🟡', 'red' => '🔴',
                                    default => '⚪'
                                };
                                
                                $badges = [];
                                if ($record->festivalParameter?->is_system) {
                                    $badges[] = '🔒 Système';
                                }
                                if ($record->festivalParameter && !$record->festivalParameter->is_visible_in_nomenclature) {
                                    $badges[] = '👁️ Masqué';
                                }
                                
                                $badgeText = $badges ? ' (' . implode(', ', $badges) . ')' : '';
                                return $colorIcon . ' ' . $parameter->name . ' (' . $parameter->code . ')' . $badgeText;
                            })
                            ->visibleOn('edit'), // Visible seulement en mode édition
                            
                        // Champ caché pour maintenir la compatibilité
                        \Filament\Forms\Components\Hidden::make('parameter_id'),

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
                Tables\Columns\IconColumn::make('parameter_icon')
                    ->label('')
                    ->getStateUsing(function ($record) {
                        $parameter = $record->festivalParameter?->parameter ?? $record->parameter;
                        return $parameter?->icon;
                    })
                    ->icon(fn ($state): string => $state ? "heroicon-o-{$state}" : 'heroicon-o-cog')
                    ->color(function ($record): string {
                        $parameter = $record->festivalParameter?->parameter ?? $record->parameter;
                        return $parameter?->color ?? 'gray';
                    })
                    ->tooltip(function ($record): string {
                        $parameter = $record->festivalParameter?->parameter ?? $record->parameter;
                        return $parameter?->short_description ?? $parameter?->name ?? 'Paramètre';
                    })
                    ->width('40px'),
                    
                TextColumn::make('order_position')
                    ->label('Position')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('festivalParameter.parameter.name')
                    ->label('Paramètre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(function ($record) {
                        $name = $record->festivalParameter?->parameter->name ?? $record->parameter?->name ?? 'N/A';
                        $badges = [];
                        if ($record->festivalParameter?->is_system) {
                            $badges[] = '🔒 Système';
                        }
                        if ($record->festivalParameter && !$record->festivalParameter->is_visible_in_nomenclature) {
                            $badges[] = '👁️ Masqué';
                        }
                        return $name . ($badges ? ' (' . implode(', ', $badges) . ')' : '');
                    }),

                TextColumn::make('parameter.code')
                    ->label('Code')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->festivalParameter?->parameter->code;
                    }),


                TextColumn::make('separator')
                    ->label('Séparateur')
                    ->placeholder('-'),

                TextColumn::make('preview')
                    ->label('Aperçu')
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
                ]),
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
     * Filtrer les nomenclatures selon l'accès et le festival sélectionné
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Si l'utilisateur est super admin ou admin, respecter quand même la sélection de festival
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            $selectedFestivalId = Session::get('selected_festival_id');
            
            if (!$selectedFestivalId) {
                // Prendre le premier festival actif par ID par défaut pour les admins
                $defaultFestival = Festival::where('is_active', true)->orderBy('id')->first();
                if ($defaultFestival) {
                    $selectedFestivalId = $defaultFestival->id;
                    Session::put('selected_festival_id', $selectedFestivalId);
                } else {
                    return parent::getEloquentQuery()->whereRaw('1 = 0');
                }
            }
            
            return parent::getEloquentQuery()
                ->where('festival_id', $selectedFestivalId)
                ->with(['festivalParameter.parameter']);
        }

        // Pour les autres utilisateurs : combiner les restrictions
        $selectedFestivalId = Session::get('selected_festival_id');
        
        // Récupérer les IDs des festivals accessibles par l'utilisateur
        $accessibleFestivalIds = $user->festivals()->pluck('festivals.id')->toArray();
        
        if (empty($accessibleFestivalIds)) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        // Si un festival spécifique est sélectionné, le filtrer en plus
        if ($selectedFestivalId) {
            // Vérifier que l'utilisateur a accès à ce festival
            if (in_array($selectedFestivalId, $accessibleFestivalIds)) {
                $festivalId = $selectedFestivalId;
            } else {
                // Festival sélectionné non autorisé pour cet utilisateur
                return parent::getEloquentQuery()->whereRaw('1 = 0');
            }
        } else {
            // Aucun festival sélectionné, prendre le premier accessible
            $defaultFestival = Festival::whereIn('id', $accessibleFestivalIds)
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
            if ($defaultFestival) {
                $festivalId = $defaultFestival->id;
                Session::put('selected_festival_id', $festivalId);
            } else {
                return parent::getEloquentQuery()->whereRaw('1 = 0');
            }
        }
        
        return parent::getEloquentQuery()
            ->where('festival_id', $festivalId)
            ->with(['festivalParameter.parameter']);
    }

    /**
     * Hook avant création : associer au festival sélectionné
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $festivalId = Session::get('selected_festival_id');

        if (! $festivalId) {
            throw new \Exception('Aucun festival sélectionné. Veuillez d\'abord choisir un festival à administrer.');
        }

        $data['festival_id'] = $festivalId;
        return $data;
    }
    
    /**
     * Hook avant mise à jour : prévenir le changement de paramètre
     */
    public static function mutateFormDataBeforeSave(array $data): array
    {
        // En mode édition, empêcher le changement de festival_parameter_id
        if (request()->route()?->parameter('record')) {
            $record = request()->route()->parameter('record');
            if ($record && isset($data['festival_parameter_id']) && $record->festival_parameter_id) {
                // Restaurer la valeur originale
                $data['festival_parameter_id'] = $record->festival_parameter_id;
            }
        }
        
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
