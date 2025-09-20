<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Fresnel\app\Services\Context\FestivalContextService;
use Symfony\Component\HttpFoundation\Response;

class EnsureManagerFestivalSelected
{
    public function __construct(
        private FestivalContextService $festivalContext
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si on est dans le panel Manager
        if (! $request->is('panel/manager/*')) {
            return $next($request);
        }

        // Exclure certaines routes de la vérification
        $excludedRoutes = [
            'panel/manager',  // Dashboard principal
            'panel/manager/logout',
        ];

        foreach ($excludedRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Vérifier si l'utilisateur est connecté et a le rôle manager
        $user = $request->user();
        if (! $user || ! $user->hasRole('manager')) {
            return $next($request);
        }

        // Vérifier si un festival est sélectionné
        if (! $this->festivalContext->hasFestivalSelected()) {
            // Rediriger vers le dashboard pour sélectionner un festival
            return redirect()->to('/panel/manager')
                ->with('warning', 'Veuillez d\'abord sélectionner un festival à administrer.');
        }

        // Vérifier que l'utilisateur a toujours accès à ce festival
        $festival = $this->festivalContext->getCurrentFestival();

        if (! $festival) {
            $this->festivalContext->clearCurrentFestival();

            return redirect()->to('/panel/manager')
                ->with('error', 'Festival sélectionné non trouvé. Veuillez choisir un autre festival.');
        }

        $hasAccess = $user->festivals()
            ->where('festivals.id', $festival->id)
            ->where('is_active', true)
            ->exists();

        if (! $hasAccess) {
            // Festival plus accessible, nettoyer la session
            $this->festivalContext->clearCurrentFestival();

            return redirect()->to('/panel/manager')
                ->with('error', 'Accès au festival sélectionné révoqué. Veuillez choisir un autre festival.');
        }

        return $next($request);
    }
}
