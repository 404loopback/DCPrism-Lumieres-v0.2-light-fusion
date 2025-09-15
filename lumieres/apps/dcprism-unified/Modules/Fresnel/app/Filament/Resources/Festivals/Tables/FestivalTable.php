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

class FestivalTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subdomain')
                    ->label('Sous-domaine')
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('is_active')
                    ->label('Statut')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Actif' : 'Inactif'),
                TextColumn::make('accept_submissions')
                    ->label('Soumissions')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Ouvertes' : 'Fermées'),
                TextColumn::make('start_date')
                    ->label('Date début')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Date fin')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        1 => 'Actifs',
                        0 => 'Inactifs'
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label('Éditer')
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->url(fn (Festival $record) => route('filament.fresnel.resources.festivals.edit', ['record' => $record->id])),
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
