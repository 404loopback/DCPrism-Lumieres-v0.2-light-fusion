<?php

namespace Modules\Fresnel\app\Filament\Resources\Parameters\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Fresnel\app\Models\Parameter;

class ParameterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Information générale')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\Select::make('category')
                            ->label('Catégorie')
                            ->options([
                                'technical' => 'Technique',
                                'metadata' => 'Métadonnées',
                                'naming' => 'Nomenclature',
                                'validation' => 'Validation',
                                'display' => 'Affichage',
                            ])
                            ->required(),
                    ])->columns(2),

                Section::make('Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'string' => 'Texte',
                                'integer' => 'Entier',
                                'float' => 'Décimal',
                                'boolean' => 'Booléen',
                                'select' => 'Sélection',
                                'date' => 'Date',
                                'datetime' => 'Date/Heure',
                                'file' => 'Fichier',
                                'json' => 'JSON',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                        Forms\Components\KeyValue::make('possible_values')
                            ->label('Valeurs possibles')
                            ->visible(fn (Get $get) => $get('type') === 'select'),
                        Forms\Components\TextInput::make('default_value')
                            ->label('Valeur par défaut'),
                    ])->columns(2),

                Section::make('Règles et validation')
                    ->schema([
                        Forms\Components\TagsInput::make('format_rules_array')
                            ->label('Règles de formatage')
                            ->suggestions(array_keys(Parameter::getAvailableFormatRules()))
                            ->helperText('Tapez ou sélectionnez les règles de formatage : no_spacing, upper_case, brackets, etc.')
                            ->placeholder('Exemple: no_spacing,upper_case,brackets')
                            ->columnSpanFull()
                            ->dehydrateStateUsing(fn ($state) => is_array($state) ? implode(',', $state) : $state)
                            ->afterStateHydrated(function ($component, $state) {
                                if (is_string($state) && !empty($state)) {
                                    $component->state(explode(',', $state));
                                }
                            })
                            ->mutateDehydratedStateUsing(fn ($state) => $state)
                            ->statePath('format_rules')
                            ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false),
                            
                        Forms\Components\TextInput::make('format_rules')
                            ->label('Règles de formatage actives')
                            ->helperText('Les règles de formatage sont configurées par les administrateurs')
                            ->disabled()
                            ->placeholder('Aucune règle définie')
                            ->columnSpanFull()
                            ->visible(fn () => !auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? true),
                        Forms\Components\KeyValue::make('validation_rules')
                            ->label('Règles de validation'),
                    ]),

                Section::make('État et organisation')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Position')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Disponible pour sélection')
                            ->helperText('Si activé, ce paramètre sera disponible pour les managers de festivals')
                            ->default(true),
                        Forms\Components\Toggle::make('is_system')
                            ->label('Paramètre système')
                            ->helperText('Paramètre du système, ne peut pas être supprimé et est automatiquement activé'),
                    ])->columns(2),
            ]);
    }
}
