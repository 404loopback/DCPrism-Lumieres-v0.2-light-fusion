<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowcaseController;

// Routes publiques du site vitrine DCPrism
Route::get('/', [ShowcaseController::class, 'home'])->name('showcase.home');
Route::get('/features', [ShowcaseController::class, 'features'])->name('showcase.features');
Route::get('/about', [ShowcaseController::class, 'about'])->name('showcase.about');
Route::get('/contact', [ShowcaseController::class, 'contact'])->name('showcase.contact');
Route::post('/contact', [ShowcaseController::class, 'submitContact'])->name('showcase.contact.submit');

// API pour les statistiques
Route::get('/api/stats', [ShowcaseController::class, 'apiStats'])->name('showcase.api.stats');
