<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FilamentAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté avec le guard web
        if (!Auth::guard('web')->check()) {
            // Rediriger vers la page de login Fresnel
            return redirect('/fresnel/login');
        }

        // Utilisateur connecté = accès autorisé
        return $next($request);
    }
}
