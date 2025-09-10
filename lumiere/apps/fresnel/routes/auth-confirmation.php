<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Routes de confirmation et diagnostic d'authentification
Route::middleware('web')->group(function () {
    
    // Route de diagnostic auth (utile pour débugger)
    Route::get('/auth/debug', function (Request $request) {
        $user = auth()->user();
        
        return response()->json([
            'authenticated' => auth()->check(),
            'user_id' => $user?->id,
            'username' => $user?->name,
            'email' => $user?->email,
            'role' => $user?->role,
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
            'guards' => config('auth.guards'),
            'current_guard' => config('auth.defaults.guard'),
        ]);
    })->name('auth.debug');

    // Route de test de session
    Route::get('/auth/session', function (Request $request) {
        return response()->json([
            'session_data' => session()->all(),
            'has_auth_session' => session()->has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'),
            'remember_token' => $request->user()?->remember_token ?? null,
        ]);
    })->name('auth.session');

    // Route de confirmation utilisateur simple
    Route::get('/auth/whoami', function () {
        if (!auth()->check()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        $user = auth()->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'panel_url' => match($user->role) {
                'admin' => '/panel/admin',
                'tech' => '/panel/tech',
                'manager' => '/panel/manager',
                'supervisor' => '/supervisor',
                'source' => '/panel/source',
                'cinema' => '/panel/cinema',
                default => '/panel/admin'
            }
        ]);
    })->middleware('auth')->name('auth.whoami');
});
