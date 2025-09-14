<?php

namespace Modules\Fresnel\app\Filament\Widgets;

use Modules\Fresnel\app\Models\ValidationResult;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget;

class ProcessingActivityWidget extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Activité de traitement récente';
    protected static ?string $description = 'Dernières validations, analyses et traitements DCP';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                ValidationResult::query()
                    ->with(['movie', 'movie.festivals'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('movie.title')
                    ->label('Film')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (ValidationResult $record): ?string {
                        return $record->movie?->title;
                    }),
                
                TextColumn::make('festival')
                    ->label('Festival')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(function ($state, ValidationResult $record): string {
                        return $record->movie?->festivals?->first()?->name ?? 'Non assigné';
                    }),
                
                BadgeColumn::make('validation_type')
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
                
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'passed',
                        'danger' => 'failed',
                        'warning' => 'warning',
                        'secondary' => 'pending',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'passed' => 'Validé',
                        'failed' => 'Échoué',
                        'warning' => 'Attention',
                        'pending' => 'En attente',
                        default => $state,
                    }),
                
                TextColumn::make('description')
                    ->label('Message')
                    ->limit(40)
                    ->tooltip(function (ValidationResult $record): ?string {
                        return $record->description;
                    })
                    ->default('Aucun message'),
                
                TextColumn::make('created_at')
                    ->label('Traité le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->striped();
    }
}
