<?php

namespace Modules\Fresnel\app\Filament\Resources\Parameters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables;
use Filament\Tables\Table;

class ParametersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Catégorie')
                    ->colors([
                        'primary' => 'video',
                        'success' => 'audio',
                        'warning' => 'accessibility',
                        'info' => 'format',
                        'secondary' => 'technical',
                        'gray' => 'metadata',
                        'purple' => 'management',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'video' => 'Vidéo',
                        'audio' => 'Audio',
                        'accessibility' => 'Accessibilité',
                        'format' => 'Format',
                        'technical' => 'Technique',
                        'metadata' => 'Métadonnées',
                        'management' => 'Gestion',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => ['string', 'select'],
                        'success' => ['integer', 'float'],
                        'warning' => ['boolean'],
                        'info' => ['date', 'datetime'],
                        'danger' => ['file', 'json'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'string' => 'Texte',
                        'integer' => 'Entier',
                        'float' => 'Décimal',
                        'boolean' => 'Booléen',
                        'select' => 'Sélection',
                        'date' => 'Date',
                        'datetime' => 'Date/Heure',
                        'file' => 'Fichier',
                        'json' => 'JSON',
                        default => $state,
                    }),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Disponible')
                    ->tooltip('Disponible pour sélection par les managers'),
                Tables\Columns\IconColumn::make('is_system')
                    ->label('Système')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('')
                    ->tooltip('Paramètre système, ne peut pas être supprimé'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'video' => 'Vidéo',
                        'audio' => 'Audio',
                        'accessibility' => 'Accessibilité',
                        'format' => 'Format',
                        'technical' => 'Technique',
                        'metadata' => 'Métadonnées',
                        'management' => 'Gestion',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'string' => 'Texte',
                        'integer' => 'Entier',
                        'float' => 'Décimal',
                        'boolean' => 'Booléen',
                        'select' => 'Sélection',
                        'date' => 'Date',
                        'datetime' => 'Date/Heure',
                        'file' => 'Fichier',
                        'json' => 'JSON',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('Type de paramètre')
                    ->placeholder('Tous')
                    ->trueLabel('Système')
                    ->falseLabel('Utilisateur'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => !$record->is_system),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $records->filter(fn ($record) => !$record->is_system)
                                   ->each(fn ($record) => $record->delete());
                        }),
                ]),
            ])
            ->defaultSort('id');
    }
}
