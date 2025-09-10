<?php

namespace App\Filament\Resources\Movies\Tables;

use App\Filament\Shared\Tables\Columns;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Movie;
use App\Models\Festival;

class MoviesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('festivals'))
            ->columns([
                Columns::title(),
                Columns::email('source_email', 'Source', 30),
                Columns::countBadge('versions', 'Versions'),
                    
                Columns::statusBadge(
                    'status',
                    'Statut',
                    [
                        'gray' => Movie::STATUS_FILM_CREATED,
                        'info' => Movie::STATUS_SOURCE_VALIDATED,
                        'success' => [Movie::STATUS_VERSIONS_VALIDATED, Movie::STATUS_UPLOADS_OK, Movie::STATUS_VALIDATION_OK, Movie::STATUS_DISTRIBUTION_OK],
                        'danger' => [Movie::STATUS_VERSIONS_REJECTED, Movie::STATUS_UPLOAD_ERROR, Movie::STATUS_VALIDATION_ERROR, Movie::STATUS_DISTRIBUTION_ERROR],
                    ],
                    Movie::getStatuses()
                ),
                    
                TextColumn::make('festivals_info')
                    ->label('Festivals')
                    ->getStateUsing(function (Movie $record): string {
                        $festivals = $record->festivals->sortBy('id');
                        
                        if ($festivals->isEmpty()) {
                            return 'Aucun festival';
                        }
                        
                        $firstFestival = $festivals->first();
                        $totalCount = $festivals->count();
                        
                        // Status icon for the first festival
                        $status = $firstFestival->pivot->submission_status ?? 'pending';
                        $priority = $firstFestival->pivot->priority ?? 0;
                        $statusLabel = match($status) {
                            'pending' => '⏳',
                            'submitted' => '📤', 
                            'in_review' => '👀',
                            'accepted' => '✅',
                            'rejected' => '❌',
                            'withdrawn' => '🚫',
                            default => '⚪'
                        };
                        $priorityLabel = $priority > 2 ? ' 🔥' : '';
                        
                        $result = "{$statusLabel} {$firstFestival->name}{$priorityLabel}";
                        
                        // Add badge if more than one festival
                        if ($totalCount > 1) {
                            $additionalCount = $totalCount - 1;
                            $result .= " <span class='inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10'>+{$additionalCount}</span>";
                        }
                        
                        return $result;
                    })
                    ->html()
                    ->limit(60)
                    ->tooltip(function (Movie $record): string {
                        $festivals = $record->festivals;
                        
                        if ($festivals->isEmpty()) {
                            return 'Ce film n\'est lié à aucun festival';
                        }
                        
                        return $festivals->map(function ($festival) {
                            $status = $festival->pivot->submission_status ?? 'pending';
                            $priority = $festival->pivot->priority ?? 0;
                            $statusText = match($status) {
                                'pending' => 'En attente',
                                'submitted' => 'Soumis',
                                'in_review' => 'En cours d\'examen',
                                'accepted' => 'Accepté',
                                'rejected' => 'Rejeté', 
                                'withdrawn' => 'Retiré',
                                default => 'Statut inconnu'
                            };
                            $priorityText = match($priority) {
                                0 => 'Normale',
                                1 => 'Faible',
                                2 => 'Moyenne',
                                3 => 'Haute',
                                4 => 'Critique',
                                5 => 'Urgente',
                                default => 'Inconnue'
                            };
                            return "• {$festival->name}: {$statusText} (Priorité: {$priorityText})";
                        })->join('\n');
                    }),
                    
                // Colonnes de dates partagées
                Columns::createdAt(),
                Columns::updatedAt(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Movie::getStatuses())
                    ->multiple(),
                    
                Filter::make('validated')
                    ->label('Validés uniquement')
                    ->query(fn (Builder $query): Builder => $query->validated()),
                    
                Filter::make('with_errors')
                    ->label('Avec erreurs')
                    ->query(fn (Builder $query): Builder => $query->withErrors()),
                    
                Filter::make('distributed')
                    ->label('Distribués')
                    ->query(fn (Builder $query): Builder => $query->distributed()),
                    
                Filter::make('recent')
                    ->label('Récents (7 jours)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Voir'),
                    EditAction::make()
                        ->label('Éditer'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
