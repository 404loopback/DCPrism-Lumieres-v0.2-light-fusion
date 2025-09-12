<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tests\Commands\TestParameterSystemWorkflow;

class TestCommandServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer les commandes de test uniquement en développement
        if ($this->app->environment(['local', 'testing'])) {
            $this->commands([
                TestParameterSystemWorkflow::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
