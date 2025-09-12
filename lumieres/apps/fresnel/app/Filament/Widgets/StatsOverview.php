<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Movie;
use App\Models\Festival;
use App\Models\User;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Films', Movie::count())
                ->description('Films dans la base')
                ->descriptionIcon('heroicon-m-film')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
                
            Stat::make('Films Validés', Movie::where('status', Movie::STATUS_VALIDATED)->count())
                ->description('Films validés techniquement')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([1, 3, 5, 10, 15, 8, 12]),
                
            Stat::make('DCPs En Attente', \App\Models\Dcp::whereNull('is_valid')->count())
                ->description('DCPs en attente de validation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([5, 10, 7, 15, 12, 8, 6]),
                
            Stat::make('Festivals Actifs', Festival::active()->count())
                ->description('Festivals en cours')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart([2, 3, 3, 4, 5, 4, 5]),
                
            Stat::make('Utilisateurs', User::count())
                ->description('Comptes utilisateurs')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray')
                ->chart([10, 12, 15, 18, 20, 22, 25]),
                
            Stat::make('Upload Aujourd\'hui', Movie::whereDate('created_at', today())->count())
                ->description('Nouveaux films aujourd\'hui')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('purple')
                ->chart([0, 1, 2, 0, 3, 1, 2]),
        ];
    }
    
    protected function getColumns(): int
    {
        return 3; // 3 colonnes pour un affichage optimal
    }
}
