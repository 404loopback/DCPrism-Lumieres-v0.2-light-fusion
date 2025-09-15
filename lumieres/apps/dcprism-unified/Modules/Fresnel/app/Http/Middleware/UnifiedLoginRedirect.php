<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnifiedLoginRedirect
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur accède à une page de login d'un panel spécifique
        // le rediriger vers le login unifié
        if ($request->is('fresnel/*/login')) {
            return redirect('/fresnel/login');
        }
        
        // Si c'est une tentative de logout qui cherche une route login inexistante
        if ($request->is('fresnel/*') && $request->query('logout') !== null) {
            return redirect('/fresnel/login?logout=1');
        }

        return $next($request);
    }
}
