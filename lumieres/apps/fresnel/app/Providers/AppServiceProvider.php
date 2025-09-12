<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use App\Models\Dcp;
use App\Observers\DcpObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register context services
        $this->app->singleton(
            \App\Services\Context\FestivalContextService::class
        );
        
        // Register form builders
        $this->app->singleton(
            \App\Filament\Builders\FormFieldBuilder::class
        );
        
        // Register movie services
        $this->app->singleton(
            \App\Services\MovieForm\MovieFormService::class
        );
        
        // Register nomenclature services
        $this->app->singleton(
            \App\Services\Nomenclature\NomenclatureRepository::class
        );
        $this->app->singleton(
            \App\Services\Nomenclature\ParameterExtractor::class
        );
        $this->app->singleton(
            \App\Services\Nomenclature\NomenclatureBuilder::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrement de l'observer DCP - Temporarily disabled for debugging
        // Dcp::observe(DcpObserver::class);
    }
}
