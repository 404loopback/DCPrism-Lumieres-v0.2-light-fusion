<?php

namespace Modules\Fresnel\app\Filament\Resources\Movies\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Filament\Shared\Tables\Columns;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Movie;

class MovieTable
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
                        $festivals = $record->festivals->sortBy('name');

                        if ($festivals->isEmpty()) {
                            return 'Aucun festival';
                        }

                        $totalCount = $festivals->count();
                        $firstFestival = $festivals->first();

                        if ($totalCount === 1) {
                            return $firstFestival->name;
                        }

                        // Afficher le premier festival + badge du nombre restant
                        $remaining = $totalCount - 1;
                        $badge = "<span class='inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10'>+{$remaining}</span>";
                        return "{$firstFestival->name} {$badge}";
                    })
                    ->html()
                    ->limit(60)
                    ->tooltip(function (Movie $record): ?string {
                        $festivals = $record->festivals->sortBy('name');

                        if ($festivals->isEmpty() || $festivals->count() <= 1) {
                            return null; // Pas de tooltip s'il n'y a qu'un seul festival ou aucun
                        }

                        // Afficher seulement les festivals non affichés (tous sauf le premier)
                        $otherFestivals = $festivals->skip(1);
                        
                        return $otherFestivals->map(function ($festival) {
                            return $festival->name;
                        })->join(' • ');
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
                ]),
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
