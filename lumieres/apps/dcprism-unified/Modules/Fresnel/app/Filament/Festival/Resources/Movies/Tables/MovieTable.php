<?php

namespace Modules\Fresnel\app\Filament\Festival\Resources\Movies\Tables;

use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Services\{BackblazeService, UnifiedNomenclatureService};
use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction, Action};
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, BadgeColumn, IconColumn};
use Filament\Tables\Filters\{SelectFilter, Filter};
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class MovieTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyableState(fn ($record) => $record->title)
                    ->tooltip('Cliquer pour copier le titre'),
                    
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'gray' => 'pending',
                        'info' => 'created',
                        'success' => 'source_validated',
                        'success' => 'versions_validated',
                        'warning' => 'versions_rejected',
                        'blue' => 'uploading',
                        'success' => 'upload_ok',
                        'danger' => 'upload_error',
                        'warning' => 'in_review',
                        'success' => 'validated',
                        'danger' => 'validation_error',
                        'info' => 'ready',
                        'success' => 'distributed',
                        'danger' => 'distribution_error',
                        'danger' => 'rejected',
                        'danger' => 'error',
                    ])
                    ->formatStateUsing(fn ($state) => Movie::getStatuses()[$state] ?? $state),
                    
                TextColumn::make('format')
                    ->label('Format')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'FTR' => 'success',
                        'SHT' => 'info', 
                        'DOC' => 'warning',
                        'TRL' => 'gray',
                        default => 'gray'
                    }),
                    
                TextColumn::make('duration')
                    ->label('Durée')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} min" : 'N/A')
                    ->sortable(),
                    
                TextColumn::make('year')
                    ->label('Année')
                    ->sortable(),
                    
                TextColumn::make('nomenclature')
                    ->label('Nomenclature')
                    ->getStateUsing(function ($record) {
                        try {
                            $nomenclatureService = app(UnifiedNomenclatureService::class);
                            $festival = Auth::user()?->festivals()?->first();
                            return $festival ? $nomenclatureService->generateMovieNomenclature($record, $festival) : 'N/A';
                        } catch (\Exception $e) {
                            return 'Erreur';
                        }
                    })
                    ->fontFamily('mono')
                    ->copyable()
                    ->limit(30)
                    ->tooltip(fn ($state) => $state),
                    
                TextColumn::make('file_size')
                    ->label('Taille')
                    ->formatStateUsing(fn ($state) => self::formatFileSize($state))
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('source_email')
                    ->label('Source')
                    ->searchable()
                    ->toggleable()
                    ->limit(20),
                    
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                IconColumn::make('has_dcp')
                    ->label('DCP')
                    ->getStateUsing(fn ($record) => !empty($record->backblaze_file_id))
                    ->boolean()
                    ->tooltip(fn ($state) => $state ? 'DCP uploadé' : 'Aucun DCP'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Movie::getStatuses())
                    ->multiple(),
                    
                SelectFilter::make('format')
                    ->label('Format')
                    ->options([
                        'FTR' => 'Feature',
                        'SHR' => 'Short',
                        'EPS' => 'Episode',
                        'TST' => 'Test',
                        'TRL' => 'Trailer',
                        'RTG' => 'Rating',
                        'POL' => 'Policy',
                        'PSA' => 'PSA',
                        'ADV' => 'Advertisement'
                    ])
                    ->multiple(),
                    
                Filter::make('has_dcp')
                    ->label('Avec DCP')
                    ->query(fn (Builder $query) => $query->whereNotNull('backblaze_file_id'))
                    ->toggle(),
                    
                Filter::make('created_this_week')
                    ->label('Créés cette semaine')
                    ->query(fn (Builder $query) => $query->where('created_at', '>=', now()->startOfWeek()))
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make()
                    ->color(Color::Blue),
                    
                Action::make('generate_nomenclature')
                    ->label('Nomenclature')
                    ->icon('heroicon-o-tag')
                    ->color(Color::Green)
                    ->action(function ($record) {
                        self::generateNomenclatureAction($record);
                    })
                    ->tooltip('Générer/régénérer la nomenclature'),
                    
                Action::make('download_dcp')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color(Color::Gray)
                    ->action(function ($record) {
                        return self::downloadDcpAction($record);
                    })
                    ->visible(fn ($record) => !empty($record->backblaze_file_id))
                    ->tooltip('Télécharger le DCP'),
                    
                Action::make('extract_parameters')
                    ->label('Extraire')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color(Color::Yellow)
                    ->action(function ($record) {
                        self::extractParametersAction($record);
                    })
                    ->visible(fn ($record) => !empty($record->DCP_metadata))
                    ->tooltip('Extraire paramètres DCP'),
            ])
            ->toolbarActions([
                Action::make('bulk_generate_nomenclature')
                    ->label('Générer nomenclatures')
                    ->icon('heroicon-o-tag')
                    ->color(Color::Green)
                    ->action(function () {
                        self::bulkGenerateNomenclatureAction();
                    })
                    ->requiresConfirmation()
                    ->modalDescription('Générer les nomenclatures pour tous les films de ce festival ?'),
                    
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                        
                    Action::make('bulk_extract_parameters')
                        ->label('Extraire paramètres')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color(Color::Yellow)
                        ->action(function ($records) {
                            self::bulkExtractParametersAction($records);
                        })
                        ->requiresConfirmation()
                        ->modalDescription('Extraire les paramètres DCP pour tous les films sélectionnés ?'),
                ]),
            ])
            ->poll('30s') // Actualisation auto pour les statuts d'upload
            ->striped();
    }
    
    /**
     * Action pour générer la nomenclature d'un film
     */
    private static function generateNomenclatureAction($record): void
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
            
            $nomenclature = $nomenclatureService->generateMovieNomenclature($record, $festival);
            $validation = $nomenclatureService->validateNomenclature($nomenclature, $festival);
            
            Notification::make()
                ->title('Nomenclature générée')
                ->body(new HtmlString(
                    "<div class='font-mono bg-gray-100 p-2 rounded mb-2'>{$nomenclature}</div>" .
                    "<div>Score de qualité: {$validation['score']}/100</div>"
                ))
                ->success()
                ->duration(8000)
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de génération')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Action pour télécharger un DCP
     */
    private static function downloadDcpAction($record)
    {
        try {
            $backblazeService = app(BackblazeService::class);
            
            return $backblazeService->download($record);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de téléchargement')
                ->body($e->getMessage())
                ->danger()
                ->send();
                
            return null;
        }
    }
    
    /**
     * Action pour extraire les paramètres DCP
     */
    private static function extractParametersAction($record): void
    {
        try {
            $nomenclatureService = app(UnifiedNomenclatureService::class);
            
            $result = $nomenclatureService->extractParametersFromDcp($record);
            
            if ($result['success']) {
                $extractedCount = count($result['extracted_params']);
                $paramsList = implode(', ', array_keys($result['extracted_params']));
                
                Notification::make()
                    ->title('Paramètres extraits avec succès')
                    ->body("$extractedCount paramètres extraits: $paramsList")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Extraction échouée')
                    ->body($result['message'])
                    ->warning()
                    ->send();
            }
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur d\'extraction')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Action en lot pour générer les nomenclatures
     */
    private static function bulkGenerateNomenclatureAction(): void
    {
        try {
            $festival = Auth::user()?->festivals()?->first();
            if (!$festival) {
                throw new \Exception('Aucun festival associé');
            }
            
            $nomenclatureService = app(UnifiedNomenclatureService::class);
            $movies = $festival->movies;
            $generated = 0;
            
            foreach ($movies as $movie) {
                try {
                    $nomenclatureService->generateMovieNomenclature($movie, $festival);
                    $generated++;
                } catch (\Exception $e) {
                    // Continuer même si un film échoue
                    continue;
                }
            }
            
            Notification::make()
                ->title('Nomenclatures générées')
                ->body("$generated nomenclatures générées sur {$movies->count()} films")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de génération en lot')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Action en lot pour extraire les paramètres DCP
     */
    private static function bulkExtractParametersAction($records): void
    {
        try {
            $nomenclatureService = app(UnifiedNomenclatureService::class);
            $processed = 0;
            $extracted = 0;
            
            foreach ($records as $record) {
                if (empty($record->DCP_metadata)) {
                    continue;
                }
                
                $processed++;
                $result = $nomenclatureService->extractParametersFromDcp($record);
                
                if ($result['success']) {
                    $extracted += count($result['extracted_params']);
                }
            }
            
            Notification::make()
                ->title('Extraction en lot terminée')
                ->body("$processed films traités, $extracted paramètres extraits au total")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur d\'extraction en lot')
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
