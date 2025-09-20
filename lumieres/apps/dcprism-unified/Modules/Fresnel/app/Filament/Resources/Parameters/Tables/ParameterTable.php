<?php

namespace Modules\Fresnel\app\Filament\Resources\Parameters\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\Parameter;

class ParameterTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('icon')
                    ->label('')
                    ->icon(fn (Parameter $record): string => $record->icon ? "heroicon-o-{$record->icon}" : 'heroicon-o-cog')
                    ->color(fn (Parameter $record): string => $record->color ?? 'gray')
                    ->tooltip(fn (Parameter $record): string => $record->short_description ?? 'Paramètre')
                    ->width('40px'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Parameter $record): ?string => $record->short_description),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Catégorie')
                    ->color(function (Parameter $record): string {
                        return match($record->category) {
                            'technical' => 'primary',
                            'video' => 'success', 
                            'audio' => 'warning',
                            'content' => 'gray',
                            'accessibility' => 'danger',
                            'format' => 'info',
                            'metadata' => 'secondary',
                            'management' => 'purple',
                            default => 'gray'
                        };
                    })
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
                Action::make('edit')
                    ->label('Éditer')
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->url(fn (Parameter $record) => route('filament.fresnel.resources.parameters.edit', ['record' => $record->id])),
                DeleteAction::make()
                    ->visible(fn ($record) => ! $record->is_system),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $records->filter(fn ($record) => ! $record->is_system)
                                ->each(fn ($record) => $record->delete());
                        }),
                ]),
            ])
            ->defaultSort('id');
    }
}
