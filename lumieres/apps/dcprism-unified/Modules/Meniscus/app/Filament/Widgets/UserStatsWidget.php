<?php

namespace Modules\Meniscus\app\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Fresnel\app\Models\User;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Utilisateurs Total', User::count())
                ->description('Nombre total d\'utilisateurs')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Utilisateurs Actifs', User::where('is_active', true)->count())
                ->description('Utilisateurs activés')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('Administrateurs', User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'super_admin']);
                })->count())
                ->description('Comptes administrateurs')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),
        ];
    }
}
