<?php

namespace Modules\Fresnel\app\Filament\Resources\Festivals\Tables;

use Modules\Fresnel\app\Filament\Shared\Tables\Columns;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\Festival;

class FestivalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Utilisation des colonnes partagées
                Columns::name('name', 'Nom'),
                Columns::subdomain(),
                Columns::activeBadge(),
                Columns::booleanIcon(
                    'accept_submissions',
                    'Soumissions',
                    trueColor: 'success',
                    falseColor: 'danger'
                ),
                Columns::date('start_date', 'Date début'),
                Columns::date('end_date', 'Date fin'),
                Columns::countBadge('movies', 'Films'),
                Columns::createdAt('Créé le'),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        1 => 'Actifs',
                        0 => 'Inactifs'
                    ]),
                    
                Filter::make('upcoming')
                    ->label('À venir')
                    ->query(fn (Builder $query): Builder => $query->where('start_date', '>', now())),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Éditer'),
                    Action::make('toggle_active')
                        ->label(fn (Festival $record) => $record->is_active ? 'Désactiver' : 'Activer')
                        ->icon(fn (Festival $record) => $record->is_active ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                        ->color(fn (Festival $record) => $record->is_active ? 'warning' : 'success')
                        ->action(function (Festival $record) {
                            $record->update(['is_active' => !$record->is_active]);
                        })
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
