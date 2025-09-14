<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FilamentRoleRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Ne pas interférer avec les actions de déconnexion et autres routes spéciales
        $excludedRoutes = [
            'panel/*/logout',
            '*/logout', 
            'panel/*/login',
            'panel/admin/login',
            'panel/*/livewire/*',
            '*/livewire/*',
            'filament/exports/*',
            'filament/imports/*',
        ];
        
        foreach ($excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }
        
        // Mappage des rôles vers leurs panels de destination (chemins Filament corrects)
        $rolePanelMapping = [
            'admin' => '/panel/admin',
            'tech' => '/panel/tech', 
            'manager' => '/panel/manager',
            'supervisor' => '/panel/supervisor',
            'source' => '/panel/source',
            'cinema' => '/panel/cinema',
        ];

        // Si l'utilisateur accède à un panel Filament qui ne correspond pas à son rôle
        if ($request->is('panel/*')) {
            $currentPath = '/' . $request->path();
            $expectedPanel = $rolePanelMapping[$user->role] ?? null;
            
            // Si l'utilisateur n'a pas de rôle défini, rediriger vers l'accueil
            if (!$user->role || !$expectedPanel) {
                return redirect('/');
            }
            
            // Si l'utilisateur n'est pas sur son panel correct, le rediriger
            if (!str_starts_with($currentPath, $expectedPanel)) {
                return redirect($expectedPanel);
            }
        }

        return $next($request);
    }
}
