<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowcaseController;

// Routes du site vitrine
Route::get('/', [ShowcaseController::class, 'home'])->name('showcase.home');
Route::get('/features', [ShowcaseController::class, 'features'])->name('showcase.features');
Route::get('/about', [ShowcaseController::class, 'about'])->name('showcase.about');
Route::get('/contact', [ShowcaseController::class, 'contact'])->name('showcase.contact');
Route::post('/contact', [ShowcaseController::class, 'submitContact'])->name('showcase.contact.submit');
Route::get('/api/stats', [ShowcaseController::class, 'apiStats'])->name('showcase.api.stats');

// Routes de connexion commune pour les panels
Route::get('/panel/login', [App\Http\Controllers\PanelAuthController::class, 'showLoginForm'])
    ->name('panel.login')
    ->middleware('guest');

Route::post('/panel/login', [App\Http\Controllers\PanelAuthController::class, 'login'])
    ->name('panel.login.submit')
    ->middleware('guest');

Route::post('/panel/logout', [App\Http\Controllers\PanelAuthController::class, 'logout'])
    ->name('panel.logout')
    ->middleware('auth');

// Route GET logout supprimée - utiliser POST /panel/logout

// Redirection de /login vers /panel/login pour compatibilité
Route::get('/login', function () {
    return redirect('/panel/login');
})->name('login');

// Route home qui redirige selon le rôle
Route::get('/home', function () {
    $user = auth()->user();
    
    if (!$user) {
        return redirect('/panel/login');
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

// Routes de confirmation et diagnostic d'authentification
require __DIR__.'/auth-confirmation.php';

