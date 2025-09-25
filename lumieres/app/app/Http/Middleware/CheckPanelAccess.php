<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPanelAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $panelPermission  The panel permission to check (e.g., 'panel.manager')
     */
    public function handle(Request $request, Closure $next, string $panelPermission): Response
    {
        $user = Auth::user();

        // If no user, redirect to login
        if (! $user) {
            return redirect()->route('fresnel.login');
        }

        // If super admin, allow all panels
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Check if user has permission for this panel
        if ($user->can($panelPermission)) {
            return $next($request);
        }

        // User doesn't have permission, redirect to appropriate panel
        // But avoid infinite loop - if we're already trying to access the panel they'd be redirected to, just deny access
        $redirectUrl = $this->getRedirectUrlForUser($user);
        if ($request->url() === url($redirectUrl)) {
            abort(403, 'Accès refusé à ce panel.');
        }
        
        return redirect($redirectUrl);
    }

    /**
     * Get redirect URL for user based on their permissions
     */
    private function getRedirectUrlForUser($user): string
    {
        $panelMappings = [
            'panel.admin' => '/fresnel/admin',
            'panel.manager' => '/fresnel/manager',
            'panel.tech' => '/fresnel/tech',
            'panel.source' => '/fresnel/source',
            'panel.infrastructure' => '/infrastructure',
        ];

        // Find first panel user has access to
        foreach ($panelMappings as $permission => $url) {
            if ($user->can($permission)) {
                return $url;
            }
        }

        // If no panel access, redirect to login
        return route('fresnel.login');
    }
}
