<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ShowcaseController;
use Illuminate\Support\Facades\Route;
use Modules\Fresnel\app\Http\Controllers\LoginRedirectController;

// Routes publiques du site vitrine DCPrism
Route::get('/', [ShowcaseController::class, 'home'])->name('showcase.home');
Route::get('/features', [ShowcaseController::class, 'features'])->name('showcase.features');
Route::get('/about', [ShowcaseController::class, 'about'])->name('showcase.about');
Route::get('/contact', [ShowcaseController::class, 'contact'])->name('showcase.contact');
Route::post('/contact', [ShowcaseController::class, 'submitContact'])->name('showcase.contact.submit');

// Routes d'authentification - LoginController dédié
Route::get('/login', function () {
    return redirect('/fresnel/login');
})->name('login')->middleware('guest');
Route::get('/fresnel/login', [LoginController::class, 'show'])->name('fresnel.login')->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get')->middleware('auth');

// API pour les statistiques
Route::get('/api/stats', [ShowcaseController::class, 'apiStats'])->name('showcase.api.stats');

// Route pour redirection après connexion
Route::get('/dashboard', [LoginRedirectController::class, 'redirectToDashboard'])
    ->middleware('auth')
    ->name('dashboard');
