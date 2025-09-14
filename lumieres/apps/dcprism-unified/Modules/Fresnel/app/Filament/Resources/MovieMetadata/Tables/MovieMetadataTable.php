<?php

namespace Modules\Fresnel\app\Filament\Resources\MovieMetadata\Tables;

use Modules\Fresnel\app\Models\Movie;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class MovieMetadataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('movie.title')
                    ->label('Film')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->movie?->festival?->name),
                Tables\Columns\TextColumn::make('metadata_key')
                    ->label('Clé technique')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('data_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'string',
                        'success' => 'number',
                        'warning' => 'boolean',
                        'info' => 'date',
                        'danger' => 'file',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'string' => 'Texte',
                        'number' => 'Nombre',
                        'boolean' => 'Booléen',
                        'date' => 'Date',
                        'file' => 'Fichier',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('metadata_value')
                    ->label('Valeur')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(fn ($record) => $record->metadata_value),
                Tables\Columns\BadgeColumn::make('source')
                    ->label('Source')
                    ->colors([
                        'primary' => 'dcp_analyzer',
                        'success' => 'automatic',
                        'warning' => 'manual',
                        'info' => ['ffprobe', 'mediainfo'],
                        'secondary' => 'import',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'dcp_analyzer' => 'DCP Analyzer',
                        'manual' => 'Manuel',
                        'import' => 'Import',
                        'ffprobe' => 'FFProbe',
                        'mediainfo' => 'MediaInfo',
                        'automatic' => 'Auto',
                        default => $state ?? 'Inconnu',
                    }),
                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label('Vérifié'),
                Tables\Columns\IconColumn::make('is_critical')
                    ->label('Critique')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon(''),
                Tables\Columns\TextColumn::make('extracted_at')
                    ->label('Extrait le')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movie_id')
                    ->label('Film')
                    ->options(Movie::whereNotNull('title')
                        ->orderBy('title')
                        ->get()
                        ->mapWithKeys(fn ($movie) => [
                            $movie->id => "{$movie->title} ({$movie->festival?->name})"
                        ]))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('data_type')
                    ->label('Type de donnée')
                    ->options([
                        'string' => 'Texte',
                        'number' => 'Nombre',
                        'boolean' => 'Booléen',
                        'date' => 'Date',
                        'file' => 'Fichier',
                    ]),
                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'dcp_analyzer' => 'DCP Analyzer',
                        'manual' => 'Saisie manuelle',
                        'import' => 'Import',
                        'ffprobe' => 'FFProbe',
                        'mediainfo' => 'MediaInfo',
                        'automatic' => 'Détection automatique',
                    ]),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Statut de vérification')
                    ->placeholder('Tous')
                    ->trueLabel('Vérifiés')
                    ->falseLabel('Non vérifiés'),
                Tables\Filters\TernaryFilter::make('is_critical')
                    ->label('Criticité')
                    ->placeholder('Tous')
                    ->trueLabel('Critiques')
                    ->falseLabel('Non critiques'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('validate')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_verified)
                    ->action(fn ($record) => $record->update(['is_verified' => true])),
                Action::make('copy_value')
                    ->label('Copier')
                    ->icon('heroicon-o-clipboard')
                    ->color('secondary')
                    ->action(function ($record) {
                        // Implémentation de la copie dans le presse-papiers via notification
                        Notification::make()
                            ->title('Valeur copiée')
                            ->body("La valeur '{$record->metadata_value}' a été préparée pour la copie.")
                            ->success()
                            ->send();
                    })
                    ->extraAttributes([
                        'onclick' => 'navigator.clipboard.writeText(this.closest("tr").querySelector("[data-column=\"metadata_value\"]").textContent.trim())'
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verify')
                        ->label('Marquer comme vérifié')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_verified' => true])),
                    BulkAction::make('mark_critical')
                        ->label('Marquer comme critique')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_critical' => true])),
                ]),
            ])
            ->defaultSort('movie_id')
            ->defaultSort('metadata_key');
    }
}
