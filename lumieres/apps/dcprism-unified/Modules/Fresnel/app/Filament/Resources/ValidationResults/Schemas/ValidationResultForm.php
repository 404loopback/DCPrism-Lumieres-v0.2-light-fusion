<?php

namespace Modules\Fresnel\app\Filament\Resources\ValidationResults\Schemas;

use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Parameter;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ValidationResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Association')
                    ->schema([
                        Forms\Components\Select::make('movie_id')
                            ->label('Film')
                            ->options(Movie::query()
                                ->whereNotNull('title')
                                ->orderBy('title')
                                ->get()
                                ->mapWithKeys(fn ($movie) => [
                                    $movie->id => "{$movie->title} ({$movie->festival?->name})"
                                ]))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('parameter_id')
                            ->label('Paramètre (optionnel)')
                            ->options(Parameter::where('is_active', true)
                                ->orderBy('category')
                                ->orderBy('title')
                                ->get()
                                ->mapWithKeys(fn ($param) => [
                                    $param->id => "{$param->category} - {$param->title}"
                                ]))
                            ->searchable()
                            ->helperText('Laisser vide pour les validations générales'),
                    ])->columns(2),
                    
                Section::make('Type de validation')
                    ->schema([
                        Forms\Components\Select::make('validation_type')
                            ->label('Type')
                            ->options([
                                'nomenclature' => 'Nomenclature',
                                'technical' => 'Technique',
                                'conformity' => 'Conformité',
                                'metadata' => 'Métadonnées',
                                'structure' => 'Structure DCP',
                                'audio' => 'Audio',
                                'video' => 'Vidéo',
                                'subtitles' => 'Sous-titres',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('rule_name')
                            ->label('Nom de la règle')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Nom technique de la règle de validation'),
                    ])->columns(2),
                    
                Section::make('Résultat')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'passed' => 'Réussi',
                                'failed' => 'Échoué',
                                'warning' => 'Avertissement',
                                'pending' => 'En attente',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('severity')
                            ->label('Sévérité')
                            ->options([
                                'error' => 'Erreur',
                                'warning' => 'Avertissement',
                                'info' => 'Information',
                            ])
                            ->default('info')
                            ->required(),
                    ])->columns(2),
                    
                Section::make('Détails de validation')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->helperText('Description lisible du problème ou du résultat'),
                        Forms\Components\Textarea::make('expected_value')
                            ->label('Valeur attendue')
                            ->rows(2)
                            ->helperText('Ce qui était attendu'),
                        Forms\Components\Textarea::make('actual_value')
                            ->label('Valeur réelle')
                            ->rows(2)
                            ->helperText('Ce qui a été trouvé'),
                    ]),
                    
                Section::make('Résolution')
                    ->schema([
                        Forms\Components\Textarea::make('suggestion')
                            ->label('Suggestion de correction')
                            ->rows(3)
                            ->helperText('Comment résoudre le problème'),
                        Forms\Components\Toggle::make('can_auto_fix')
                            ->label('Correction automatique possible')
                            ->helperText('Peut être corrigé automatiquement par le système'),
                    ]),
                    
                Section::make('Détails techniques')
                    ->schema([
                        Forms\Components\KeyValue::make('details')
                            ->label('Détails JSON')
                            ->helperText('Informations techniques supplémentaires'),
                        Forms\Components\DateTimePicker::make('validated_at')
                            ->label('Validé le')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('validator_version')
                            ->label('Version du validateur')
                            ->maxLength(20)
                            ->helperText('Version de l\'outil de validation utilisé'),
                    ])->columns(2),
            ]);
    }
}
