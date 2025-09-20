<?php

namespace Modules\Fresnel\app\Filament\Manager\Pages;

use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use Modules\Fresnel\app\Filament\Manager\Widgets\FestivalOverviewWidget;
use Modules\Fresnel\app\Filament\Manager\Widgets\FestivalSelectorWidget;
use Modules\Fresnel\app\Models\Festival;

class ManagerDashboard extends Dashboard
{
    /**
     * Titre de la page basé sur le festival sélectionné
     */
    public function getTitle(): string
    {
        $festivalId = session('selected_festival_id');

        if ($festivalId) {
            $festival = Festival::find($festivalId);
            if ($festival) {
                return "Dashboard Manager - {$festival->name}";
            }
        }

        return 'Dashboard Manager';
    }

    /**
     * Widgets affichés dans la zone header
     */
    public function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
            FestivalSelectorWidget::class,
        ];
    }

    /**
     * Configuration des colonnes pour les widgets du header
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,  // 2 colonnes à partir de md (tablettes et plus)
        ];
    }

    /**
     * Widgets affichés dans le contenu principal du dashboard
     */
    public function getWidgets(): array
    {
        return [
            FestivalOverviewWidget::class,
        ];
    }

    /**
     * Configuration des colonnes pour les widgets du dashboard
     */
    public function getColumns(): int|array
    {
        return 2;
    }
}
