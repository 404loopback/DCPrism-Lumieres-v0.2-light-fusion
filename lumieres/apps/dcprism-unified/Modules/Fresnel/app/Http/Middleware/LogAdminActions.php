<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\Fresnel\app\Services\AuditService;
use Illuminate\Support\Facades\Auth;

class LogAdminActions
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ne logger que les requêtes POST, PUT, PATCH, DELETE (actions de modification)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $response;
        }

        // Ne logger que si l'utilisateur est authentifié
        if (!Auth::check()) {
            return $response;
        }

        // Construire la description de l'action
        $action = $this->buildActionDescription($request);
        
        if ($action) {
            $this->auditService->logAdminAction($action, null, [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'parameters' => $this->filterSensitiveData($request->all()),
                'response_status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    /**
     * Construire la description de l'action basée sur la route
     */
    protected function buildActionDescription(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        $routeName = $route->getName();
        $method = $request->method();

        return match ($method) {
            'POST' => $this->getCreateActionDescription($routeName),
            'PUT', 'PATCH' => $this->getUpdateActionDescription($routeName),
            'DELETE' => $this->getDeleteActionDescription($routeName),
            default => null,
        };
    }

    /**
     * Description pour les actions de création
     */
    protected function getCreateActionDescription(?string $routeName): ?string
    {
        if (!$routeName) {
            return 'Création d\'élément';
        }

        return match (true) {
            str_contains($routeName, 'movie') => 'Création de film',
            str_contains($routeName, 'festival') => 'Création de festival',
            str_contains($routeName, 'user') => 'Création d\'utilisateur',
            str_contains($routeName, 'role') => 'Création de rôle',
            str_contains($routeName, 'parameter') => 'Création de paramètre',
            default => 'Création d\'élément',
        };
    }

    /**
     * Description pour les actions de mise à jour
     */
    protected function getUpdateActionDescription(?string $routeName): ?string
    {
        if (!$routeName) {
            return 'Modification d\'élément';
        }

        return match (true) {
            str_contains($routeName, 'movie') => 'Modification de film',
            str_contains($routeName, 'festival') => 'Modification de festival',
            str_contains($routeName, 'user') => 'Modification d\'utilisateur',
            str_contains($routeName, 'role') => 'Modification de rôle',
            str_contains($routeName, 'parameter') => 'Modification de paramètre',
            default => 'Modification d\'élément',
        };
    }

    /**
     * Description pour les actions de suppression
     */
    protected function getDeleteActionDescription(?string $routeName): ?string
    {
        if (!$routeName) {
            return 'Suppression d\'élément';
        }

        return match (true) {
            str_contains($routeName, 'movie') => 'Suppression de film',
            str_contains($routeName, 'festival') => 'Suppression de festival',
            str_contains($routeName, 'user') => 'Suppression d\'utilisateur',
            str_contains($routeName, 'role') => 'Suppression de rôle',
            str_contains($routeName, 'parameter') => 'Suppression de paramètre',
            default => 'Suppression d\'élément',
        };
    }

    /**
     * Filtrer les données sensibles des paramètres de la requête
     */
    protected function filterSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}
