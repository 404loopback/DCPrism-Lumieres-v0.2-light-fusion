<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowcaseController;

// ========================================
// ROUTES AVEC GESTION PAR DOMAINE
// ========================================

// Routes vitrine (pour localhost uniquement)
Route::group(['domain' => 'localhost'], function () {
    Route::get('/', [ShowcaseController::class, 'home'])->name('showcase.home');
    Route::get('/features', [ShowcaseController::class, 'features'])->name('showcase.features');
    Route::get('/about', [ShowcaseController::class, 'about'])->name('showcase.about');
    Route::get('/contact', [ShowcaseController::class, 'contact'])->name('showcase.contact');
    Route::post('/contact', [ShowcaseController::class, 'submitContact'])->name('showcase.contact.submit');
    Route::get('/api/stats', [ShowcaseController::class, 'apiStats'])->name('showcase.api.stats');
    
    // Rediriger toute autre route vers fresnel.localhost pour l'app
    Route::fallback(function () {
        return redirect('http://fresnel.localhost' . request()->getRequestUri());
    });
});

// Routes app (pour fresnel.localhost uniquement)
Route::group(['domain' => 'fresnel.localhost'], function () {
    // Routes de l'application gérées par Filament
    // (L'authentification est maintenant gérée par Filament)

    // Redirection de /login vers /panel/admin/login pour compatibilité
    Route::get('/login', function () {
        return redirect('/panel/admin/login');
    })->name('login');

    // Route home qui redirige selon le rôle
    Route::get('/home', function () {
        $user = auth()->user();
        
        if (!$user) {
            return redirect('/panel/admin/login');
        }
        
        switch ($user->role) {
            case 'admin':
                return redirect('/panel/admin');
            case 'tech':
                return redirect('/panel/tech');
            case 'manager':
                return redirect('/panel/manager');
            case 'supervisor':
                return redirect('/supervisor');
            case 'source':
                return redirect('/panel/source');
            case 'cinema':
                return redirect('/panel/cinema');
            default:
                return redirect('/panel/admin');
        }
    })->name('home')->middleware('auth');
    
    // Rediriger fresnel.localhost vers l'app (login Filament)
    Route::get('/', function () { return redirect('/panel/admin/login'); });
    Route::get('/features', function () { return redirect('http://localhost/features'); });
    Route::get('/about', function () { return redirect('http://localhost/about'); });
    Route::get('/contact', function () { return redirect('http://localhost/contact'); });
    Route::get('/api/stats', function () { return redirect('http://localhost/api/stats'); });

    // Routes de confirmation et diagnostic d'authentification
    require __DIR__.'/auth-confirmation.php';
});

