<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PanelAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('panel.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            // Régénérer complètement la session pour éviter les conflits
            $request->session()->regenerate();
            
            // Vider tous les caches de session potentiellement problématiques
            $request->session()->flush();
            $request->session()->regenerate(true);
            
            // Re-authentifier l'utilisateur après le flush
            Auth::attempt($request->only('email', 'password'), $request->boolean('remember'));

            // Rediriger tous les utilisateurs vers /home après connexion
            return redirect()->intended('/home');
        }

        throw ValidationException::withMessages([
            'email' => __('Ces identifiants ne correspondent pas à nos enregistrements.'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        // Nettoyer complètement la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush();
        
        // Forcer la création d'une nouvelle session
        $request->session()->regenerate(true);
        
        return redirect('/panel/login');
    }
}
