<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiCorsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Configure CORS headers for API
        $response->headers->set('Access-Control-Allow-Origin', $this->getAllowedOrigins($request));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 
            'Authorization, Content-Type, Accept, X-Requested-With, Origin, Cache-Control, Pragma, X-API-Version'
        );
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        // Handle preflight OPTIONS requests
        if ($request->getMethod() === 'OPTIONS') {
            $response->setContent('');
            $response->setStatusCode(200);
        }

        return $response;
    }

    /**
     * Get allowed origins based on environment
     */
    private function getAllowedOrigins(Request $request): string
    {
        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:8080',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:8080',
        ];

        // Add production origins
        if (app()->environment('production')) {
            $allowedOrigins = array_merge($allowedOrigins, [
                config('app.frontend_url'),
                'https://dcprism.app',
                'https://api.dcprism.app',
            ]);
        }

        $origin = $request->headers->get('Origin');
        
        if ($origin && in_array($origin, $allowedOrigins)) {
            return $origin;
        }

        // Allow same origin requests
        if (!$origin || $origin === $request->getSchemeAndHttpHost()) {
            return $request->getSchemeAndHttpHost();
        }

        // En production, ne pas utiliser * pour la sécurité
        if (app()->environment('production')) {
            return $allowedOrigins[0] ?? $request->getSchemeAndHttpHost();
        }

        return '*';
    }
}
