<?php

namespace Modules\Meniscus\app\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            \Modules\Meniscus\app\Filament\Widgets\UserStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // TODO: Créer SystemOverviewWidget plus tard
            // \Modules\Meniscus\app\Filament\Widgets\SystemOverviewWidget::class,
        ];
    }
}
