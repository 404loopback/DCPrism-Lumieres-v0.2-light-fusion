<?php

namespace Modules\Fresnel\app\Filament\Source\Pages;

use Modules\Fresnel\app\Filament\Source\Widgets\FestivalSelectorWidget;
use Modules\Fresnel\app\Filament\Source\Widgets\UploadOverviewWidget;
use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;

class SourceDashboard extends Dashboard
{
    
    /**
     * Widgets affichés dans la zone header (carré rouge)
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
    public function getHeaderWidgetsColumns(): int | array
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
            UploadOverviewWidget::class,
        ];
    }
    
    /**
     * Configuration des colonnes pour les widgets du dashboard
     */
    public function getColumns(): int | array
    {
        return 2;
    }
}
