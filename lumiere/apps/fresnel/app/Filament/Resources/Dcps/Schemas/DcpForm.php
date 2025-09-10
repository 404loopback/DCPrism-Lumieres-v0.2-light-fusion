<?php

namespace App\Filament\Resources\Dcps\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use App\Models\Dcp;

class DcpForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de base')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('movie_id')
                                    ->label('Film')
                                    ->relationship('movie', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                    
                                Select::make('version_id')
                                    ->label('Version')
                                    ->relationship('version', 'type')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                                        $record->type . ' - ' . $record->audio_lang
                                    )
                                    ->searchable()
                                    ->preload(),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_ov')
                                    ->label('Version originale')
                                    ->default(false),
                                    
                                Select::make('status')
                                    ->label('Statut')
                                    ->options(Dcp::STATUSES)
                                    ->default(Dcp::STATUS_UPLOADED)
                                    ->required(),
                                    
                                Toggle::make('is_valid')
                                    ->label('Validé')
                                    ->default(false),
                            ]),
                    ]),
                    
                Section::make('Configuration linguistique')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('audio_lang')
                                    ->label('Langue audio')
                                    ->relationship('audioLanguage', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                                    ->searchable()
                                    ->preload(),
                                    
                                Select::make('subtitle_lang')
                                    ->label('Langue sous-titres')
                                    ->relationship('subtitleLanguage', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),
                    
                Section::make('Fichier et stockage')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('backblaze_file_id')
                                    ->label('ID fichier Backblaze')
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Généré automatiquement après upload'),
                                    
                                TextInput::make('file_size')
                                    ->label('Taille du fichier (bytes)')
                                    ->numeric()
                                    ->suffix('bytes')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                            
                        FileUpload::make('file_upload')
                            ->label('Upload DCP')
                            ->disk('local')
                            ->directory('dcps/temp')
                            ->acceptedFileTypes(['application/zip', 'application/x-tar', 'application/octet-stream'])
                            ->maxSize(50 * 1024 * 1024) // 50GB
                            ->columnSpanFull()
                            ->helperText('Formats acceptés : ZIP, TAR - L\'upload sera traité via Backblaze automatiquement')
                            ->afterStateUpdated(function (callable $set, \Filament\Forms\Get $get, $state) {
                                if ($state) {
                                    // Déclencher l'upload Backblaze en arrière-plan
                                    $set('status', \App\Models\Dcp::STATUS_PROCESSING);
                                    $set('uploaded_at', now());
                                }
                            })
                            ->reactive(),
                            
                        TextInput::make('file_path')
                            ->label('Chemin Backblaze')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Généré automatiquement après upload'),
                    ])
                    ->collapsible(),
                    
                Section::make('Métadonnées techniques')
                    ->schema([
                        KeyValue::make('technical_metadata')
                            ->label('Métadonnées DCP')
                            ->keyLabel('Propriété')
                            ->valueLabel('Valeur')
                            ->reorderable()
                            ->addActionLabel('Ajouter une métadonnée')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
                    
                Section::make('Validation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('uploaded_by')
                                    ->label('Uploadé par')
                                    ->relationship('uploader', 'name')
                                    ->searchable()
                                    ->preload(),
                                    
                                DateTimePicker::make('uploaded_at')
                                    ->label('Uploadé le')
                                    ->timezone('Europe/Brussels'),
                            ]),
                            
                        DateTimePicker::make('validated_at')
                            ->label('Validé le')
                            ->timezone('Europe/Brussels'),
                            
                        Textarea::make('validation_notes')
                            ->label('Notes de validation')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
