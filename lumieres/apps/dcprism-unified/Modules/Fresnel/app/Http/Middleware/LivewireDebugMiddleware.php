<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LivewireDebugMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $isLivewireRequest = $request->hasHeader('X-Livewire') ||
                           $request->is('livewire/*') ||
                           str_contains($request->getContentType() ?? '', 'livewire');

        if ($isLivewireRequest) {
            Log::channel('daily')->info('[LIVEWIRE DEBUG] Request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'livewire_headers' => [
                    'X-Livewire' => $request->header('X-Livewire'),
                    'X-Livewire-Request' => $request->header('X-Livewire-Request'),
                ],
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);
        }

        // Capturer les redirections potentiellement problématiques
        $response = $next($request);

        if ($response->isRedirection() && $isLivewireRequest) {
            Log::channel('daily')->warning('[LIVEWIRE DEBUG] Redirect in Livewire request', [
                'from_url' => $request->fullUrl(),
                'to_url' => $response->getTargetUrl(),
                'status_code' => $response->getStatusCode(),
                'timestamp' => now()->toISOString(),
            ]);
        }

        return $response;
    }
}
