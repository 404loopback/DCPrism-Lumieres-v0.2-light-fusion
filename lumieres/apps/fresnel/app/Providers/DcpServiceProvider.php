<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider pour les services DCP
 * Note: Les services DCP ont été supprimés car le traitement DCP
 * se fait sur un système externe, pas sur cette application Laravel.
 */
class DcpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Services DCP supprimés - traitement externe
        // Garde ce provider pour d'éventuels futurs services liés aux DCP
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
