<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Afficher la page de connexion
     */
    public function show(): View|RedirectResponse
    {
        // Si déjà connecté, rediriger vers le panel approprié
        if (Auth::check()) {
            return $this->redirectToPanel();
        }

        return view('fresnel::login');
    }

    /**
     * Traiter la connexion
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return $this->redirectToPanel();
        }

        throw ValidationException::withMessages([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Redirection vers le panel approprié selon le rôle Spatie
     */
    private function redirectToPanel(): RedirectResponse
    {
        $user = Auth::user();
        
        // Mappage des rôles vers leurs panels Filament
        $rolePanelMapping = [
            'admin' => '/fresnel/admin',
            'tech' => '/fresnel/tech', 
            'manager' => '/fresnel/manager',
            'supervisor' => '/fresnel/manager', // superviseurs → panel manager
            'source' => '/fresnel/source',
            'cinema' => '/fresnel/cinema',
        ];

        // Déterminer le panel de destination basé sur les rôles Spatie
        foreach ($rolePanelMapping as $role => $panelPath) {
            if ($user->hasRole($role)) {
                return redirect($panelPath);
            }
        }

        // Fallback vers admin si aucun rôle trouvé
        return redirect('/fresnel/admin');
    }
}
