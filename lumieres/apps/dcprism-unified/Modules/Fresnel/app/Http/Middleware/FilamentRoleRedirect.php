<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FilamentRoleRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('=== MIDDLEWARE DEBUG START ===', [
            'url' => $request->url(),
            'method' => $request->method(),
            'user_authenticated' => Auth::check(),
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'user_email' => Auth::check() ? Auth::user()->email : null,
        ]);

        if (!Auth::check()) {
            Log::info('User not authenticated - passing through');
            return $next($request);
        }

        $user = Auth::user();
        
        Log::info('User roles check:', [
            'user_roles' => $user->roles->pluck('name')->toArray(),
            'has_super_admin' => $user->hasRole('super_admin'),
        ]);

        // POUR LE DEBUG : autoriser tous les utilisateurs connectés
        Log::info('DEBUG: Allowing all authenticated users');
        return $next($request);
    }
}
