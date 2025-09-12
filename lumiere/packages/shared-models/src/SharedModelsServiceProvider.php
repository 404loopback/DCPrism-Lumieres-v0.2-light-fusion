<?php

namespace Lumieres\Shared\Models;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for Shared Models Package
 */
class SharedModelsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register any bindings or singletons here
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish migrations if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'shared-models-migrations');
        }
    }
}
