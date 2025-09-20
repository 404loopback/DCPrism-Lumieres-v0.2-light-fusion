<?php

namespace Modules\Fresnel\app\Filament\Widgets;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\Festival;

class FestivalPerformanceWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Performance par festival';

    protected static ?string $description = 'Statistiques et métriques de traitement par festival';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Festival::query()
                    ->withCount([
                        'movies',
                        'movies as validated_movies_count' => function (Builder $query) {
                            $query->where('status', 'validated');
                        },
                        'movies as failed_movies_count' => function (Builder $query) {
                            $query->where('status', 'failed');
                        },
                        'movies as processing_movies_count' => function (Builder $query) {
                            $query->where('status', 'processing');
                        },
                    ])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Festival')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('movies_count')
                    ->label('Films total')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                BadgeColumn::make('validated_movies_count')
                    ->label('Validés')
                    ->color('success')
                    ->sortable(),

                BadgeColumn::make('processing_movies_count')
                    ->label('En cours')
                    ->color('warning')
                    ->sortable(),

                BadgeColumn::make('failed_movies_count')
                    ->label('Échoués')
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('success_rate')
                    ->label('Taux de succès')
                    ->getStateUsing(function (Festival $record): string {
                        if ($record->movies_count === 0) {
                            return '0%';
                        }
                        $rate = ($record->validated_movies_count / $record->movies_count) * 100;

                        return round($rate, 1).'%';
                    })
                    ->badge()
                    ->color(function (Festival $record): string {
                        if ($record->movies_count === 0) {
                            return 'gray';
                        }
                        $rate = ($record->validated_movies_count / $record->movies_count) * 100;

                        return $rate >= 80 ? 'success' : ($rate >= 60 ? 'warning' : 'danger');
                    })
                    ->sortable(),

                TextColumn::make('delivery_deadline')
                    ->label('Date limite')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Non définie'),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'in_progress',
                        'danger' => 'delayed',
                        'secondary' => 'draft',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'completed' => 'Terminé',
                        'in_progress' => 'En cours',
                        'delayed' => 'En retard',
                        'draft' => 'Brouillon',
                        default => 'Non défini',
                    }),
            ])
            ->defaultSort('movies_count', 'desc')
            ->paginated(false)
            ->striped();
    }
}
