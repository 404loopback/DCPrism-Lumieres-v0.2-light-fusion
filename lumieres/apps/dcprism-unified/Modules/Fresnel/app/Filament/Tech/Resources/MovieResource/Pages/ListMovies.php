<?php

namespace Modules\Fresnel\app\Filament\Tech\Resources\MovieResource\Pages;

use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Tech\Resources\MovieResource;
use Modules\Fresnel\app\Models\Dcp;
use Modules\Fresnel\app\Models\Movie;

class ListMovies extends ListRecords
{
    protected static string $resource = MovieResource::class;

    public function getTitle(): string
    {
        return 'Vue d\'Ensemble Films';
    }

    public function getSubheading(): ?string
    {
        $totalMovies = $this->getTableQuery()->count();
        $pendingMovies = $this->getTableQuery()
            ->whereHas('dcps', fn ($query) => $query->where('status', Dcp::STATUS_UPLOADED))
            ->count();

        if ($pendingMovies > 0) {
            return "⚠️ {$pendingMovies} films avec DCPs en attente sur {$totalMovies} total";
        }

        return "✅ Tous les films traités ({$totalMovies} total)";
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous les Films')
                ->badge(fn () => $this->getTableQuery()->count()),

            'with_pending_dcps' => Tab::make('Avec DCPs en Attente')
                ->badge(fn () => $this->getTableQuery()
                    ->whereHas('dcps', fn ($query) => $query->where('status', Dcp::STATUS_UPLOADED))
                    ->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn ($query) => $query->whereHas('dcps', fn ($q) => $q->where('status', Dcp::STATUS_UPLOADED))),

            'validated' => Tab::make('Entièrement Validés')
                ->badge(fn () => $this->getTableQuery()->where('status', Movie::STATUS_VALIDATED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn ($query) => $query->where('status', Movie::STATUS_VALIDATED)),

            'rejected' => Tab::make('Rejetés')
                ->badge(fn () => $this->getTableQuery()->where('status', Movie::STATUS_REJECTED)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn ($query) => $query->where('status', Movie::STATUS_REJECTED)),

            'recent' => Tab::make('Récents (7j)')
                ->badge(fn () => $this->getTableQuery()->where('created_at', '>=', now()->subDays(7))->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn ($query) => $query->where('created_at', '>=', now()->subDays(7))),
        ];
    }
}
