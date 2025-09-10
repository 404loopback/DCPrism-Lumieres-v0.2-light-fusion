<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Festival;
use App\Models\Movie;
use App\Models\Dcp;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FestivalOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Récupérer les données du festival de l'utilisateur connecté (à adapter selon votre logique)
        $totalMovies = Movie::count();
        $totalDcps = Dcp::count();
        $activeFestivals = Festival::where('is_active', true)->count();
        
        return [
            Stat::make('Films Total', $totalMovies)
                ->description('Films dans le catalogue')
                ->descriptionIcon('heroicon-m-film')
                ->color('success'),
                
            Stat::make('DCP Traités', $totalDcps)
                ->description('DCP prêts pour diffusion')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('primary'),
                
            Stat::make('Festivals Actifs', $activeFestivals)
                ->description('Événements en cours')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
                
            Stat::make('Taux de Réussite', '94.2%')
                ->description('Validation technique')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }
}
