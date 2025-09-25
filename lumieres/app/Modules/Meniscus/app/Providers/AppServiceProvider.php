<?php

namespace Modules\Meniscus\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Meniscus\app\Filament\Widgets\UserStatsWidget;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrement manuel du widget Livewire pour éviter les problèmes de namespace
        Livewire::component('meniscus.widgets.user-stats-widget', UserStatsWidget::class);
    }
}
