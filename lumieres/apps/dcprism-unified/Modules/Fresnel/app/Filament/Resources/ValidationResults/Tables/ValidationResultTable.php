<?php

namespace Modules\Fresnel\app\Filament\Resources\ValidationResults\Tables;

use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Parameter;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ValidationResultTable
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
                Tables\Columns\BadgeColumn::make('validation_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'nomenclature',
                        'success' => 'technical',
                        'warning' => 'conformity',
                        'info' => 'metadata',
                        'danger' => 'structure',
                        'secondary' => ['audio', 'video', 'subtitles'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nomenclature' => 'Nomenclature',
                        'technical' => 'Technique',
                        'conformity' => 'Conformité',
                        'metadata' => 'Métadonnées',
                        'structure' => 'Structure',
                        'audio' => 'Audio',
                        'video' => 'Vidéo',
                        'subtitles' => 'Sous-titres',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('rule_name')
                    ->label('Règle')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'passed',
                        'danger' => 'failed',
                        'warning' => 'warning',
                        'secondary' => 'pending',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'passed' => 'Réussi',
                        'failed' => 'Échoué',
                        'warning' => 'Avertissement',
                        'pending' => 'En attente',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('severity')
                    ->label('Sévérité')
                    ->colors([
                        'danger' => 'error',
                        'warning' => 'warning',
                        'info' => 'info',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'error' => 'Erreur',
                        'warning' => 'Avertissement',
                        'info' => 'Info',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('parameter.name')
                    ->label('Paramètre')
                    ->searchable()
                    ->placeholder('Général')
                    ->limit(20),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->description),
                Tables\Columns\IconColumn::make('can_auto_fix')
                    ->label('Auto-fix')
                    ->boolean()
                    ->trueIcon('heroicon-o-wrench-screwdriver')
                    ->trueColor('success')
                    ->falseIcon('')
                    ->tooltip(fn ($record) => $record->can_auto_fix ? 'Correction automatique possible' : 'Correction manuelle nécessaire'),
                Tables\Columns\TextColumn::make('validated_at')
                    ->label('Validé le')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('validator_version')
                    ->label('Version')
                    ->placeholder('N/A')
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
                Tables\Filters\SelectFilter::make('validation_type')
                    ->label('Type de validation')
                    ->options([
                        'nomenclature' => 'Nomenclature',
                        'technical' => 'Technique',
                        'conformity' => 'Conformité',
                        'metadata' => 'Métadonnées',
                        'structure' => 'Structure DCP',
                        'audio' => 'Audio',
                        'video' => 'Vidéo',
                        'subtitles' => 'Sous-titres',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'passed' => 'Réussi',
                        'failed' => 'Échoué',
                        'warning' => 'Avertissement',
                        'pending' => 'En attente',
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->label('Sévérité')
                    ->options([
                        'error' => 'Erreur',
                        'warning' => 'Avertissement',
                        'info' => 'Information',
                    ]),
                Tables\Filters\SelectFilter::make('parameter_id')
                    ->label('Paramètre')
                    ->options(Parameter::orderBy('category')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn ($param) => [
                            $param->id => "{$param->category} - {$param->name}"
                        ]))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('can_auto_fix')
                    ->label('Correction automatique')
                    ->placeholder('Tous')
                    ->trueLabel('Possible')
                    ->falseLabel('Manuelle'),
                Tables\Filters\Filter::make('recent_validations')
                    ->label('Validations récentes')
                    ->query(fn ($query) => $query->where('validated_at', '>=', now()->subDay()))
                    ->toggle(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('auto_fix')
                    ->label('Corriger')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('success')
                    ->visible(fn ($record) => $record->can_auto_fix && $record->status === 'failed')
                    ->action(function ($record) {
                        // TODO: Implémenter la logique de correction automatique
                        // Cela dépendrait du type d'erreur et du paramètre
                    }),
                Action::make('view_details')
                    ->label('Détails')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(fn ($record) => view('filament.validation-result-details', ['record' => $record]))
                    ->modalHeading(fn ($record) => "Détails: {$record->rule_name}"),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_as_resolved')
                        ->label('Marquer comme résolu')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'passed'])),
                    BulkAction::make('revalidate')
                        ->label('Revalider')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function ($records) {
                            // TODO: Relancer les validations pour ces enregistrements
                            $records->each->update(['status' => 'pending', 'validated_at' => now()]);
                        }),
                ]),
            ])
            ->defaultSort('validated_at', 'desc')
            ->recordUrl(null); // Désactive les liens directs vers la vue d'édition
    }
}
