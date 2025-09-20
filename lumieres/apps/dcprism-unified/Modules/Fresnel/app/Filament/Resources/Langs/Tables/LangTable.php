<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LangTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('iso_639_1')
                    ->label('ISO 639-1')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('iso_639_3')
                    ->label('ISO 639-3')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label('Nom (EN)')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('french_name')
                    ->label('Nom (FR)')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('Non traduit'),

                TextColumn::make('local_name')
                    ->label('Nom local')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('Non renseigné'),

                TextColumn::make('display_name')
                    ->label('Affichage complet')
                    ->limit(60)
                    ->tooltip(fn ($record): string => $record->display_name)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Colonnes avec relations désactivées temporairement
                // BadgeColumn::make('audio_versions_count')
                //     ->label('Versions Audio')
                //     ->counts('audioVersions')
                //     ->color('success')
                //     ->alignCenter(),

                // BadgeColumn::make('subtitle_versions_count')
                //     ->label('Versions ST')
                //     ->counts('subtitleVersions')
                //     ->color('info')
                //     ->alignCenter(),

                // BadgeColumn::make('audio_dcps_count')
                //     ->label('DCPs Audio')
                //     ->counts('audioDcps')
                //     ->color('warning')
                //     ->alignCenter()
                //     ->toggleable(isToggledHiddenByDefault: true),

                // BadgeColumn::make('subtitle_dcps_count')
                //     ->label('DCPs ST')
                //     ->counts('subtitleDcps')
                //     ->color('gray')
                //     ->alignCenter()
                //     ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtre désactivé - affiche seulement 43 langues
                // Filter::make('has_local_name')
                //     ->label('Avec nom local')
                //     ->query(fn (Builder $query): Builder => $query->whereNotNull('local_name')),

                // Filtres désactivés temporairement à cause de problèmes de relations
                // Filter::make('used_in_versions')
                //     ->label('Utilisée dans les versions')
                //     ->query(fn (Builder $query): Builder => $query->whereHas('audioVersions')
                //         ->orWhereHas('subtitleVersions')),
                //
                // Filter::make('used_in_dcps')
                //     ->label('Utilisée dans les DCPs')
                //     ->query(fn (Builder $query): Builder => $query->whereHas('audioDcps')
                //         ->orWhereHas('subtitleDcps')),

                Filter::make('recent')
                    ->label('Récentes (7 jours)')
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
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->headerActions([
                Action::make('refresh_languages')
                    ->label('Mettre à jour les langues')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Mise à jour des langues')
                    ->modalDescription('Cette action va mettre à jour toutes les langues avec les derniers données ISO et traductions. Cela peut prendre quelques secondes.')
                    ->modalSubmitActionLabel('Mettre à jour')
                    ->action(function () {
                        // Exécuter le seeder
                        \Artisan::call('db:seed', [
                            '--class' => 'LanguageSeeder',
                        ]);

                        // Notification de succès
                        \Filament\Notifications\Notification::make()
                            ->title('Langues mises à jour')
                            ->success()
                            ->body('La base de données des langues a été mise à jour avec succès !')
                            ->send();
                    }),
            ])
            ->defaultSort('name')
            ->striped();
    }
}
