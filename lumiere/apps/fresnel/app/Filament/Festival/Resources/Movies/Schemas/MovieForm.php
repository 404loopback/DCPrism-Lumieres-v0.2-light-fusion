<?php

namespace App\Filament\Festival\Resources\Movies\Schemas;

use App\Models\Movie;
use App\Services\{BackblazeService, UnifiedNomenclatureService};
use Filament\Schemas\Schema;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class MovieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                Section::make('Informations du film')
                    ->icon('heroicon-o-film')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => 
                                $set('computed_nomenclature', self::generatePreviewNomenclature($state))
                            ),
                            
                        Select::make('format')
                            ->label('Format')
                            ->required()
                            ->options([
                                'FTR' => 'Feature (Long métrage)',
                                'SHR' => 'Short (Court métrage)', 
                                'EPS' => 'Episode',
                                'TST' => 'Test',
                                'TRL' => 'Trailer (Bande annonce)',
                                'RTG' => 'Rating',
                                'POL' => 'Policy',
                                'PSA' => 'Public Service Announcement',
                                'ADV' => 'Advertisement (Publicité)'
                            ])
                            ->default('FTR'),
                            
                        TextInput::make('duration')
                            ->label('Durée (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(500),
                            
                        TextInput::make('year')
                            ->label('Année de production')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y') + 2),
                            
                        TextInput::make('genre')
                            ->label('Genre')
                            ->maxLength(100),
                            
                        TextInput::make('country')
                            ->label('Pays')
                            ->maxLength(100),
                            
                        TextInput::make('language')
                            ->label('Langue')
                            ->maxLength(50),
                            
                        Select::make('status')
                            ->label('Statut')
                            ->options(Movie::getStatuses())
                            ->default('pending')
                            ->disabled(fn ($context) => $context === 'create'),
                    ]),
                    
                Section::make('Description')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description du film')
                            ->rows(4)
                            ->maxLength(2000),
                    ]),
                    
                Section::make('Upload DCP')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        FileUpload::make('dcp_upload')
                            ->label('Fichier DCP')
                            ->acceptedFileTypes(['application/zip', 'application/x-tar'])
                            ->maxSize(50 * 1024 * 1024) // 50GB in KB
                            ->disk('dcp')
                            ->directory(fn () => 'uploads/' . Auth::user()?->email)
                            ->visibility('private')
                            ->uploadingMessage('Upload en cours...')
                            ->afterStateUpdated(function ($state, $record, $set) {
                                if ($state && $record) {
                                    self::handleFileUpload($state, $record, $set);
                                }
                            })
                            ->helperText(new HtmlString(
                                '<strong>Formats acceptés :</strong> ZIP, TAR<br>' .
                                '<strong>Taille maximale :</strong> 50GB<br>' .
                                '<strong>Note :</strong> L\'upload se fait en arrière-plan avec progression.'
                            )),
                    ])
                    ->visible(fn ($context) => $context === 'edit'),
                    
                Section::make('Nomenclature')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Placeholder::make('computed_nomenclature')
                            ->label('Nomenclature générée')
                            ->content(fn ($get, $record) => self::getComputedNomenclature($get, $record))
                            ->extraAttributes(['class' => 'text-lg font-mono bg-gray-100 p-3 rounded border']),
                            
                        Actions::make([
                            Action::make('generate_nomenclature')
                                ->label('Régénérer la nomenclature')
                                ->icon('heroicon-o-arrow-path')
                                ->color(Color::Blue)
                                ->action(function ($record, $set) {
                                    if ($record) {
                                        self::regenerateNomenclature($record, $set);
                                    }
                                })
                                ->visible(fn ($context) => $context === 'edit'),
                                
                            Action::make('preview_nomenclature')
                                ->label('Prévisualiser')
                                ->icon('heroicon-o-eye')
                                ->color(Color::Gray)
                                ->action(function ($get, $record) {
                                    self::showNomenclaturePreview($get, $record);
                                }),
                        ])
                    ]),
                    
                Section::make('Métadonnées techniques')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed()
                    ->schema([
                        Textarea::make('technical_notes')
                            ->label('Notes techniques')
                            ->rows(3)
                            ->maxLength(1000),
                            
                        TextInput::make('original_filename')
                            ->label('Nom de fichier original')
                            ->disabled()
                            ->visible(fn ($record) => $record?->original_filename),
                            
                        Placeholder::make('file_size_display')
                            ->label('Taille du fichier')
                            ->content(fn ($record) => $record?->file_size ? self::formatFileSize($record->file_size) : 'N/A')
                            ->visible(fn ($record) => $record?->file_size),
                            
                        Placeholder::make('uploaded_at_display')
                            ->label('Date d\'upload')
                            ->content(fn ($record) => $record?->uploaded_at?->format('d/m/Y à H:i:s') ?? 'N/A')
                            ->visible(fn ($record) => $record?->uploaded_at),
                    ])
                    ->visible(fn ($context) => $context === 'edit'),
            ]);
    }
    
    /**
     * Générer un aperçu de nomenclature en temps réel
     */
    private static function generatePreviewNomenclature(?string $title): string
    {
        if (!$title) return '';
        
        $timestamp = now()->format('Ymd');
        $safeTitle = \Illuminate\Support\Str::slug($title, '_');
        
        return "{$safeTitle}_{$timestamp}_PREVIEW";
    }
    
    /**
     * Obtenir la nomenclature calculée pour un film
     */
    private static function getComputedNomenclature($get, $record): string
    {
        if (!$record) {
            return self::generatePreviewNomenclature($get('title'));
        }
        
        try {
            $nomenclatureService = app(UnifiedNomenclatureService::class);
            $festival = Auth::user()?->festivals()?->first();
            
            if (!$festival) {
                return 'Festival non trouvé';
            }
            
            return $nomenclatureService->generateMovieNomenclature($record, $festival);
        } catch (\Exception $e) {
            return 'Erreur: ' . $e->getMessage();
        }
    }
    
    /**
     * Régénérer la nomenclature d'un film
     */
    private static function regenerateNomenclature($record, $set): void
    {
        try {
            $nomenclatureService = app(UnifiedNomenclatureService::class);
            $festival = Auth::user()?->festivals()?->first();
            
            if (!$festival) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Aucun festival associé à votre compte')
                    ->danger()
                    ->send();
                return;
            }
            
            $newNomenclature = $nomenclatureService->generateMovieNomenclature($record, $festival);
            
            Notification::make()
                ->title('Nomenclature régénérée')
                ->body("Nouvelle nomenclature: {$newNomenclature}")
                ->success()
                ->send();
                
            // Forcer le rafraîchissement de l'interface
            $set('computed_nomenclature', $newNomenclature);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors de la régénération')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Afficher la prévisualisation détaillée de la nomenclature
     */
    private static function showNomenclaturePreview($get, $record): void
    {
        try {
            $nomenclatureService = app(UnifiedNomenclatureService::class);
            $festival = Auth::user()?->festivals()?->first();
            
            if (!$festival || !$record) {
                Notification::make()
                    ->title('Impossible de prévisualiser')
                    ->body('Festival ou film manquant')
                    ->warning()
                    ->send();
                return;
            }
            
            $preview = $nomenclatureService->previewNomenclature($record, $festival);
            
            $previewHtml = '<div class="space-y-2">';
            foreach ($preview['preview_parts'] as $part) {
                $status = $part['is_required'] ? '(requis)' : '(optionnel)';
                $previewHtml .= "<div><strong>{$part['parameter']}</strong> {$status}: {$part['formatted_value']}</div>";
            }
            $previewHtml .= "<div class='mt-4 p-2 bg-blue-100 rounded'><strong>Résultat final:</strong> {$preview['final_nomenclature']}</div>";
            $previewHtml .= '</div>';
            
            Notification::make()
                ->title('Prévisualisation de la nomenclature')
                ->body(new HtmlString($previewHtml))
                ->info()
                ->persistent()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de prévisualisation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Gérer l'upload de fichier avec BackblazeService
     */
    private static function handleFileUpload($uploadedFile, $record, $set): void
    {
        try {
            $backblazeService = app(BackblazeService::class);
            $festival = Auth::user()?->festivals()?->first();
            
            if (!$festival) {
                Notification::make()
                    ->title('Erreur d\'upload')
                    ->body('Aucun festival associé')
                    ->danger()
                    ->send();
                return;
            }
            
            // L'upload réel avec progression sera géré en arrière-plan
            // Pour l'instant, on met à jour les informations du fichier
            $record->update([
                'status' => 'uploading',
                'original_filename' => $uploadedFile->getClientOriginalName(),
                'file_size' => $uploadedFile->getSize()
            ]);
            
            Notification::make()
                ->title('Upload démarré')
                ->body('Le fichier est en cours d\'upload vers le stockage cloud')
                ->info()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur d\'upload')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Formater la taille de fichier
     */
    private static function formatFileSize(?int $bytes): string
    {
        if (!$bytes) return 'N/A';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
