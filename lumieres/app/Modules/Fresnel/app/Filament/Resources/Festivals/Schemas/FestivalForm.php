<?php

namespace Modules\Fresnel\app\Filament\Resources\Festivals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FestivalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom du festival')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('subdomain')
                                    ->label('Sous-domaine')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->helperText('Utilisé pour l\'URL dédiée au festival'),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('email')
                                    ->label('Email de contact')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('contact_phone')
                                    ->label('Téléphone')
                                    ->maxLength(50),
                            ]),

                        TextInput::make('website')
                            ->label('Site web')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),

                Section::make('Dates et configuration')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Date de début')
                                    ->required()
                                    ->displayFormat('d/m/Y'),

                                DatePicker::make('end_date')
                                    ->label('Date de fin')
                                    ->required()
                                    ->displayFormat('d/m/Y')
                                    ->afterOrEqual('start_date'),
                            ]),

                        DateTimePicker::make('submission_deadline')
                            ->label('Date limite de soumission')
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('Europe/Brussels')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Festival actif')
                                    ->default(true)
                                    ->helperText('Désactiver pour fermer le festival'),

                                Toggle::make('accept_submissions')
                                    ->label('Accepter les soumissions')
                                    ->default(true)
                                    ->helperText('Permet aux utilisateurs de soumettre des films'),
                            ]),
                    ])
                    ->columnSpan(1),

                Section::make('Stockage et limites')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_storage')
                                    ->label('Stockage maximum (GB)')
                                    ->numeric()
                                    ->suffix('GB')
                                    ->helperText('Limite de stockage pour ce festival'),

                                TextInput::make('max_file_size')
                                    ->label('Taille max fichier (GB)')
                                    ->numeric()
                                    ->suffix('GB')
                                    ->helperText('Taille maximale par fichier DCP'),
                            ]),

                        TextInput::make('backblaze_folder')
                            ->label('Dossier Backblaze')
                            ->maxLength(255)
                            ->helperText('Nom du dossier de stockage sur Backblaze B2')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->collapsible(),

                Section::make('Formats acceptés')
                    ->schema([
                        TagsInput::make('accepted_formats')
                            ->label('Formats DCP acceptés')
                            ->placeholder('Ajoutez un format')
                            ->suggestions([
                                'FTR' => 'Feature',
                                'SHR' => 'Short',
                                'TRL' => 'Trailer',
                                'TST' => 'Test',
                            ])
                            ->helperText('Formats de films acceptés pour ce festival')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->collapsible(),

                Section::make('Exigences techniques')
                    ->schema([
                        KeyValue::make('technical_requirements')
                            ->label('Exigences techniques')
                            ->keyLabel('Critère')
                            ->valueLabel('Exigence')
                            ->reorderable()
                            ->addActionLabel('Ajouter une exigence')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Configuration de nomenclature')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nomenclature_separator')
                                    ->label('Séparateur de nomenclature')
                                    ->maxLength(5)
                                    ->default('_')
                                    ->helperText('Caractère utilisé pour séparer les éléments de nomenclature'),

                                Select::make('storage_status')
                                    ->label('Statut stockage')
                                    ->options([
                                        'active' => 'Actif',
                                        'full' => 'Plein',
                                        'error' => 'Erreur',
                                        'maintenance' => 'Maintenance',
                                    ])
                                    ->default('active'),
                            ]),

                        Textarea::make('nomenclature_template')
                            ->label('Template de nomenclature')
                            ->placeholder('{title}_{format}_{year}_{language}')
                            ->helperText('Template par défaut pour la génération automatique')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
