<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // API rate limiting for authenticated users
        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute(env('API_RATE_LIMIT', 1000))->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(env('API_RATE_LIMIT', 1000) * 10)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Public API rate limiting (no authentication required)
        RateLimiter::for('public', function (Request $request) {
            return [
                Limit::perMinute(env('PUBLIC_RATE_LIMIT', 60))->by($request->ip()),
                Limit::perHour(env('PUBLIC_RATE_LIMIT', 60) * 5)->by($request->ip()),
            ];
        });

        // Upload rate limiting (more restrictive)
        RateLimiter::for('upload', function (Request $request) {
            return [
                Limit::perMinute(env('UPLOAD_RATE_LIMIT', 10))->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(env('UPLOAD_RATE_LIMIT', 10) * 20)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Processing job rate limiting
        RateLimiter::for('processing', function (Request $request) {
            return [
                Limit::perMinute(20)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(100)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Webhook rate limiting
        RateLimiter::for('webhook', function (Request $request) {
            return [
                Limit::perMinute(100)->by($request->ip()),
                Limit::perHour(1000)->by($request->ip()),
            ];
        });

        // Auth endpoints (login, register) rate limiting
        RateLimiter::for('auth', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(20)->by($request->ip()),
            ];
        });

        // Search rate limiting
        RateLimiter::for('search', function (Request $request) {
            return [
                Limit::perMinute(100)->by($request->ip()),
                Limit::perHour(500)->by($request->ip()),
            ];
        });
        
        // Filament panels rate limiting (very generous)
        RateLimiter::for('filament', function (Request $request) {
            return [
                Limit::perMinute(300)->by($request->user()?->id ?: $request->ip()), // 5 requêtes par seconde max
                Limit::perHour(5000)->by($request->user()?->id ?: $request->ip()),
            ];
        });
        
        // Web interface rate limiting (for non-API routes)
        RateLimiter::for('web', function (Request $request) {
            return [
                Limit::perMinute(200)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(2000)->by($request->user()?->id ?: $request->ip()),
            ];
        });
    }
}
