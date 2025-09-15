<?php

namespace Modules\Fresnel\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm(): View|RedirectResponse
    {
        // Si déjà connecté, rediriger vers le dashboard approprié
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('fresnel::auth.standalone-login');
    }

    /**
     * Traite la tentative de connexion
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return $this->redirectToDashboard();
        }

        throw ValidationException::withMessages([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ]);
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/fresnel/login');
    }

    /**
     * Redirige vers le dashboard approprié selon le rôle
     */
    protected function redirectToDashboard(): RedirectResponse
    {
        $user = Auth::user();
        
        // Mappage des rôles vers leurs panels
        $rolePanelMapping = [
            'admin' => '/fresnel/admin',
            'tech' => '/fresnel/tech', 
            'manager' => '/fresnel/manager',
            'supervisor' => '/fresnel/manager',
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
}
