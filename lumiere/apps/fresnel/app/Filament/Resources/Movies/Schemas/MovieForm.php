<?php

namespace App\Filament\Resources\Movies\Schemas;

use App\Filament\Shared\Forms\Fields;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use App\Models\Movie;
use App\Models\Festival;
use App\Models\Parameter;
use App\Models\FestivalParameter;
use App\Models\MovieParameter;
use App\Models\Nomenclature;
use Illuminate\Support\Facades\Session;

class MovieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du Film')
                    ->description('Informations générales sur le film')
                    ->icon('heroicon-o-film')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre du Film')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                // Section Versions avec création dynamique
                Section::make('Versions')
                    ->description('Création des versions du film avec paramètres dynamiques')
                    ->icon('heroicon-o-film')
                    ->schema([
                        // Repeater pour les versions en création
                        Repeater::make('versions')
                            ->label('Versions du film')
                            ->relationship()
                            ->schema([
                                // Nom de version généré automatiquement
                                TextInput::make('type')
                                    ->label('Nom de la version')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default('Nouvelle version')
                                    ->helperText('Le nom sera généré automatiquement selon vos paramètres')
                                    ->columnSpanFull(),
                                    
                                // Langues de base
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('audio_lang')
                                            ->label('Langue audio')
                                            ->placeholder('Ex: fr, en, original')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                static::updateVersionName($set, $get);
                                            }),
                                            
                                        TextInput::make('sub_lang')
                                            ->label('Sous-titres')
                                            ->placeholder('Ex: fr, en, none')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                static::updateVersionName($set, $get);
                                            }),
                                    ]),
                                    
                                Select::make('accessibility')
                                    ->label('Accessibilité')
                                    ->options([
                                        'none' => 'Aucune',
                                        'audiodescription' => 'Audiodescription', 
                                        'subtitles_hard' => 'Sous-titres intégrés',
                                        'both' => 'Audiodescription + Sous-titres'
                                    ])
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        static::updateVersionName($set, $get);
                                    }),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? 'Nouvelle version')
                            ->addActionLabel('Ajouter une version')
                            ->collapsible()
                            ->cloneable()
                            ->deletable()
                            ->visible(fn ($operation) => $operation === 'create'),
                            
                        // Tableau des versions existantes pour l'édition
                        \Filament\Forms\Components\ViewField::make('versions_table')
                            ->view('filament.forms.components.versions-table')
                            ->viewData(fn ($record) => [
                                'versions' => $record ? $record->versions()->with(['movie'])->get() : collect(),
                                'operation' => 'edit'
                            ])
                            ->columnSpanFull()
                            ->visible(fn ($operation) => $operation === 'edit'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($operation) => $operation === 'create'),

                Section::make('Contact Source')
                    ->description('Informations du contact pour ce film')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('source_email')
                            ->label('Email de la Source')
                            ->email()
                            ->required()
                            ->helperText('Un compte utilisateur sera créé automatiquement si cet email n\'existe pas')
                            ->suffixIcon('heroicon-o-at-symbol')
                            ->columnSpanFull(),
                    ]),

                // Sections des paramètres du festival
                ...static::getConditionalParametersSections(),
                    
            ]);
    }

    /**
     * Méthode pour mettre à jour dynamiquement le nom de version
     */
    protected static function updateVersionName(callable $set, callable $get): void
    {
        $audioLang = $get('audio_lang');
        $subLang = $get('sub_lang');
        $accessibility = $get('accessibility');
        
        // Générer le nom de version selon la logique de nomenclature
        $versionName = static::generateVersionTypeName($audioLang, $subLang, $accessibility);
        
        $set('type', $versionName);
    }
    
    /**
     * Générer le nom de type de version selon les paramètres
     */
    protected static function generateVersionTypeName(?string $audioLang, ?string $subLang, ?string $accessibility): string
    {
        $parts = [];
        
        // Langue audio
        if ($audioLang) {
            if ($audioLang === 'original') {
                $parts[] = 'VO';
            } elseif ($audioLang === 'fr') {
                $parts[] = 'VF';
            } else {
                $parts[] = strtoupper($audioLang);
            }
        }
        
        // Sous-titres
        if ($subLang && $subLang !== 'none') {
            if ($subLang === 'fr') {
                $parts[] = 'STFR';
            } elseif ($subLang === 'en') {
                $parts[] = 'STEN';
            } else {
                $parts[] = 'ST' . strtoupper($subLang);
            }
        }
        
        // Accessibilité
        if ($accessibility && $accessibility !== 'none') {
            switch ($accessibility) {
                case 'audiodescription':
                    $parts[] = 'AD';
                    break;
                case 'subtitles_hard':
                    $parts[] = 'STI'; // Sous-titres intégrés
                    break;
                case 'both':
                    $parts[] = 'AD+STI';
                    break;
            }
        }
        
        return !empty($parts) ? implode('_', $parts) : 'Nouvelle version';
    }
    
    /**
     * Obtenir les sections de paramètres conditionnelles
     */
    protected static function getConditionalParametersSections(): array
    {
        // Pour la version Movies/MovieResource, on peut retourner les sections des paramètres
        // si nécessaire. Pour l'instant, on retourne un tableau vide.
        return [];
    }
}
