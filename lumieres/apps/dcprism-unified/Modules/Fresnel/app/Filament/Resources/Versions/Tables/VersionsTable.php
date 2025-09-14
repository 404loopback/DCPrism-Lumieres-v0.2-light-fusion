<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Modules\Fresnel\app\Models\Version;

class VersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('movie.title')
                    ->label('Film')
                    ->searchable()
                    ->sortable(),
                    
                BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'VO',
                        'success' => 'VF', 
                        'info' => 'VOST',
                        'warning' => 'VOSTF',
                        'gray' => 'DUB'
                    ]),
                    
                TextColumn::make('audio_lang')
                    ->label('Audio')
                    ->badge()
                    ->color('success'),
                    
                TextColumn::make('sub_lang')
                    ->label('Sous-titres')
                    ->badge()
                    ->color('info')
                    ->placeholder('Aucun'),
                    
                TextColumn::make('generated_nomenclature')
                    ->label('Nomenclature')
                    ->limit(40)
                    ->copyable(),
                    
                BadgeColumn::make('dcps_count')
                    ->label('DCPs')
                    ->counts('dcps')
                    ->color('warning'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type de version')
                    ->options(Version::TYPES),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    \Filament\Actions\Action::make('generate_nomenclature')
                        ->label('Générer Nomenclature')
                        ->icon('heroicon-o-sparkles')
                        ->color('success')
                        ->action(function (\App\Models\Version $record) {
                            $nomenclatureService = app(\App\Services\UnifiedNomenclatureService::class);
                            
                            try {
                                // Générer la nomenclature pour chaque festival du film
                                $movie = $record->movie;
                                $festivals = $movie->festivals;
                                
                                if ($festivals->isEmpty()) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Aucun festival associé')
                                        ->body('Ce film n\'est associé à aucun festival')
                                        ->warning()
                                        ->send();
                                    return;
                                }
                                
                                $nomenclatures = [];
                                foreach ($festivals as $festival) {
                                    $nomenclature = $nomenclatureService->generateMovieNomenclature($movie, $festival);
                                    $nomenclatures[] = "{$festival->name}: {$nomenclature}";
                                }
                                
                                // Mettre à jour la version avec la nomenclature du premier festival
                                $mainNomenclature = $nomenclatureService->generateMovieNomenclature($movie, $festivals->first());
                                $record->update([
                                    'generated_nomenclature' => $mainNomenclature
                                ]);
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Nomenclature générée')
                                    ->body('Nomenclature: ' . $mainNomenclature)
                                    ->success()
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erreur de génération')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                        
                    \Filament\Actions\Action::make('preview_nomenclature')
                        ->label('Aperçu')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalContent(function (\App\Models\Version $record) {
                            $nomenclatureService = app(\App\Services\UnifiedNomenclatureService::class);
                            $movie = $record->movie;
                            $festivals = $movie->festivals;
                            
                            if ($festivals->isEmpty()) {
                                return view('filament.modals.version-nomenclature-preview', [
                                    'message' => 'Aucun festival associé à ce film'
                                ]);
                            }
                            
                            $previews = [];
                            foreach ($festivals as $festival) {
                                $preview = $nomenclatureService->previewNomenclature($movie, $festival);
                                $previews[] = [
                                    'festival' => $festival->name,
                                    'preview' => $preview
                                ];
                            }
                            
                            return view('filament.modals.version-nomenclature-preview', [
                                'previews' => $previews
                            ]);
                        })
                        ->modalHeading('Aperçu des nomenclatures')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fermer'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
