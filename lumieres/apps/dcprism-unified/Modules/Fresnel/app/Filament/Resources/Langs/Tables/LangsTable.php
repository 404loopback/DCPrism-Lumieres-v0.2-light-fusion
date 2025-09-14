<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\Lang;

class LangsTable
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
                    
                TextColumn::make('local_name')
                    ->label('Nom local')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('Non renseigné'),
                    
                TextColumn::make('display_name')
                    ->label('Affichage complet')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
                    
                BadgeColumn::make('audio_versions_count')
                    ->label('Versions Audio')
                    ->counts('audioVersions')
                    ->color('success')
                    ->alignCenter(),
                    
                BadgeColumn::make('subtitle_versions_count')
                    ->label('Versions ST')
                    ->counts('subtitleVersions')
                    ->color('info')
                    ->alignCenter(),
                    
                BadgeColumn::make('audio_dcps_count')
                    ->label('DCPs Audio')
                    ->counts('audioDcps')
                    ->color('warning')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                BadgeColumn::make('subtitle_dcps_count')
                    ->label('DCPs ST')
                    ->counts('subtitleDcps')
                    ->color('gray')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
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
                Filter::make('has_local_name')
                    ->label('Avec nom local')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('local_name')),
                    
                Filter::make('used_in_versions')
                    ->label('Utilisée dans les versions')
                    ->query(fn (Builder $query): Builder => $query->whereHas('audioVersions')
                        ->orWhereHas('subtitleVersions')),
                        
                Filter::make('used_in_dcps')
                    ->label('Utilisée dans les DCPs')
                    ->query(fn (Builder $query): Builder => $query->whereHas('audioDcps')
                        ->orWhereHas('subtitleDcps')),
                        
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
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name')
            ->striped();
    }
}
