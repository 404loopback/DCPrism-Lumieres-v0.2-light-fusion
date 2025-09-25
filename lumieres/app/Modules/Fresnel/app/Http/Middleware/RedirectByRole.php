<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectByRole
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentPanel = $request->route()->getPrefix();

            // Mappage des rôles vers les panels
            $rolePanelMapping = [
                'admin' => '/admin',
                'tech' => '/tech',
                'manager' => '/manager',
                'supervisor' => '/supervisor',
                'source' => '/source',
                'cinema' => '/cinema',
            ];

            // Récupérer le rôle Shield de l'utilisateur
            $userRole = $user->roles->first()?->name;
            // Récupérer le panel autorisé pour ce rôle
            $authorizedPanel = $rolePanelMapping[$userRole] ?? null;

            if ($authorizedPanel && ! str_starts_with($request->getPathInfo(), $authorizedPanel)) {
                // Rediriger vers le panel autorisé si l'utilisateur tente d'accéder à un autre panel
                return redirect($authorizedPanel);
            }

            // Vérifier si l'utilisateur est sur le bon panel
            $expectedPrefix = str_replace('/', '', $authorizedPanel ?? '');
            if ($authorizedPanel && $currentPanel !== $expectedPrefix) {
                return redirect($authorizedPanel);
            }
        }

        return $next($request);
    }
}
