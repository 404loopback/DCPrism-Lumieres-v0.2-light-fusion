<?php

namespace App\Filament\Resources\Dcps\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Dcp;

class DcpsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('movie.title')
                    ->label('Film')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                    
                TextColumn::make('version.type')
                    ->label('Version')
                    ->badge()
                    ->color('info'),
                    
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'gray' => Dcp::STATUS_UPLOADED,
                        'warning' => Dcp::STATUS_PROCESSING,
                        'success' => Dcp::STATUS_VALID,
                        'danger' => Dcp::STATUS_INVALID,
                        'gray' => Dcp::STATUS_ERROR,
                    ])
                    ->formatStateUsing(fn (string $state): string => Dcp::STATUSES[$state] ?? $state),
                    
                IconColumn::make('is_valid')
                    ->label('Validé')
                    ->boolean()
                    ->alignCenter(),
                    
                TextColumn::make('audio_lang')
                    ->label('Audio')
                    ->badge()
                    ->color('success'),
                    
                TextColumn::make('subtitle_lang')
                    ->label('ST')
                    ->badge()
                    ->color('info')
                    ->placeholder('Aucun'),
                    
                TextColumn::make('formatted_file_size')
                    ->label('Taille')
                    ->alignRight(),
                    
                TextColumn::make('uploader.name')
                    ->label('Uploadé par')
                    ->limit(20),
                    
                TextColumn::make('uploaded_at')
                    ->label('Uploadé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
                    
                TextColumn::make('validated_at')
                    ->label('Validé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Non validé'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Dcp::STATUSES),
                    
                Filter::make('is_valid')
                    ->label('Validés seulement')
                    ->query(fn (Builder $query): Builder => $query->where('is_valid', true)),
                    
                Filter::make('pending_validation')
                    ->label('En attente de validation')
                    ->query(fn (Builder $query): Builder => $query->where('status', Dcp::STATUS_UPLOADED)),
                    
                Filter::make('recent')
                    ->label('Récents (7 jours)')
                    ->query(fn (Builder $query): Builder => $query->where('uploaded_at', '>=', now()->subDays(7))),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Voir'),
                    EditAction::make()
                        ->label('Éditer'),
                    Action::make('validate')
                        ->label('Valider')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Dcp $record): bool => !$record->is_valid)
                        ->requiresConfirmation()
                        ->action(fn (Dcp $record) => $record->markAsValid('Validé depuis le panel admin'))
                        ->successNotificationTitle('DCP validé avec succès'),
                        
                    Action::make('reject')
                        ->label('Rejeter')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Dcp $record): bool => $record->status === Dcp::STATUS_UPLOADED)
                        ->requiresConfirmation()
                        ->form([
                            \Filament\Forms\Components\Textarea::make('notes')
                                ->label('Raison du rejet')
                                ->required()
                                ->rows(3)
                        ])
                        ->action(function (Dcp $record, array $data) {
                            $record->markAsInvalid($data['notes']);
                        })
                        ->successNotificationTitle('DCP rejeté'),
                        
                    Action::make('download')
                        ->label('Télécharger')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->visible(fn (Dcp $record): bool => $record->is_valid && !empty($record->backblaze_file_id))
                        ->action(function (Dcp $record) {
                            $backblazeService = app(\App\Services\BackblazeService::class);
                            return $backblazeService->download($record->movie);
                        })
                        ->successNotificationTitle('Téléchargement initié'),
                        
                    Action::make('analyze')
                        ->label('Analyser DCP')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('warning')
                        ->visible(fn (Dcp $record): bool => !empty($record->file_path))
                        ->action(function (Dcp $record) {
                            $dcpAnalysisService = app(\App\Services\DCP\DcpAnalysisService::class);
                            
                            try {
                                $analysis = $dcpAnalysisService->analyze($record->movie, [
                                    'include_recommendations' => true,
                                    'deep_analysis' => false
                                ]);
                                
                                // Mettre à jour les métadonnées techniques
                                $record->update([
                                    'technical_metadata' => $analysis->technicalSpecs,
                                    'status' => $analysis->success ? 'valid' : 'invalid',
                                    'validation_notes' => $analysis->message
                                ]);
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Analyse DCP terminée')
                                    ->body($analysis->message)
                                    ->success()
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erreur d\'analyse')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->striped();
    }
}
