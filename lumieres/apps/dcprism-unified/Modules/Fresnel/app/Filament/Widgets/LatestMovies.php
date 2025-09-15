<?php

namespace Modules\Fresnel\app\Filament\Widgets;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\Movie;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LatestMovies extends TableWidget
{
    protected static ?string $heading = 'Films Récents';
    
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Movie::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40)
                    ->weight('semibold')
                    ->url(fn (Movie $record): string => route('filament.fresnel.resources.movies.view', $record)),
                    
                TextColumn::make('source_email')
                    ->label('Source')
                    ->limit(25)
                    ->copyable(),
                    
                BadgeColumn::make('format')
                    ->label('Format')
                    ->colors([
                        'primary' => 'FTR',
                        'success' => 'SHR', 
                        'warning' => 'TRL',
                    ]),
                    
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'gray' => Movie::STATUS_PENDING,
                        'warning' => Movie::STATUS_UPLOADING,
                        'success' => Movie::STATUS_UPLOAD_OK,
                        'info' => Movie::STATUS_IN_REVIEW,
                        'primary' => Movie::STATUS_VALIDATED,
                        'danger' => Movie::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn (string $state): string => Movie::getStatuses()[$state] ?? $state),
                    
                TextColumn::make('created_at')
                    ->label('Ajouté')
                    ->since()
                    ->sortable(),
            ])
            ->recordUrl(fn (Movie $record): string => route('filament.fresnel.resources.movies.view', $record))
            ->paginated(false);
    }
}
