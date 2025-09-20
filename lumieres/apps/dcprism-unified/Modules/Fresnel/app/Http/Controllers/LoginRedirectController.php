<?php

namespace Modules\Fresnel\app\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LoginRedirectController extends Controller
{
    /**
     * Redirige vers le panel approprié selon le rôle utilisateur
     */
    public function redirectToDashboard(): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('fresnel.login');
        }

        $user = Auth::user();

        // Mappage des rôles vers leurs panels de destination
        $rolePanelMapping = [
            'admin' => '/fresnel/admin',
            'tech' => '/fresnel/tech',
            'manager' => '/fresnel/manager',
            'supervisor' => '/fresnel/manager', // Les supervisors utilisent le panel manager
            'source' => '/fresnel/source',
            'cinema' => '/fresnel/cinema',
        ];

        // Déterminer le panel de destination basé sur les rôles Spatie
        foreach ($rolePanelMapping as $role => $panelPath) {
            if ($user->hasRole($role)) {
                return redirect($panelPath);
            }
        }

        // Si aucun rôle correspondant, rediriger vers le panel admin par défaut
        return redirect('/fresnel/admin');
    }

    /**
     * Page d'accueil avec lien de connexion
     */
    public function welcome()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('fresnel::welcome');
    }
}
