<?php

namespace Modules\Fresnel\app\Filament\Resources\MovieMetadata\Schemas;

use Modules\Fresnel\app\Models\Movie;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class MovieMetadataForm
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
                    ]),
                    
                Section::make('Métadonnée technique')
                    ->schema([
                        Forms\Components\TextInput::make('metadata_key')
                            ->label('Clé technique')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Nom technique du paramètre (ex: frame_rate, resolution, audio_channels)'),
                        Forms\Components\Select::make('data_type')
                            ->label('Type de donnée')
                            ->options([
                                'string' => 'Texte',
                                'number' => 'Nombre',
                                'boolean' => 'Booléen',
                                'date' => 'Date',
                                'file' => 'Fichier',
                            ])
                            ->default('string')
                            ->required()
                            ->live(),
                    ])->columns(2),
                    
                Section::make('Valeur')
                    ->schema([
                        Forms\Components\Textarea::make('metadata_value')
                            ->label('Valeur')
                            ->required()
                            ->rows(3)
                            ->helperText('Valeur de la métadonnée (peut être longue)'),
                    ]),
                    
                Section::make('Source et validité')
                    ->schema([
                        Forms\Components\Select::make('source')
                            ->label('Source')
                            ->options([
                                'dcp_analyzer' => 'DCP Analyzer',
                                'manual' => 'Saisie manuelle',
                                'import' => 'Import',
                                'ffprobe' => 'FFProbe',
                                'mediainfo' => 'MediaInfo',
                                'automatic' => 'Détection automatique',
                            ])
                            ->helperText('Outil ou méthode d\'extraction'),
                        Forms\Components\DateTimePicker::make('extracted_at')
                            ->label('Extrait le')
                            ->helperText('Date/heure d\'extraction de la métadonnée'),
                    ])->columns(2),
                    
                Section::make('Validation et règles')
                    ->schema([
                        Forms\Components\KeyValue::make('validation_rules')
                            ->label('Règles de validation')
                            ->helperText('Règles JSON pour valider cette métadonnée'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->helperText('Notes techniques ou commentaires'),
                    ]),
                    
                Section::make('État')
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Vérifié')
                            ->helperText('La métadonnée a été vérifiée manuellement'),
                        Forms\Components\Toggle::make('is_critical')
                            ->label('Critique')
                            ->helperText('Indispensable pour la validation du DCP'),
                    ])->columns(2),
            ]);
    }
}
