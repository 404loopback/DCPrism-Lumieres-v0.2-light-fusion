<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fresnel API Routes
|--------------------------------------------------------------------------
|
| Ici vous pouvez enregistrer les routes API pour le module Fresnel.
| Ces routes sont automatiquement préfixées avec "api" et chargées avec
| le middleware "api" par le RouteServiceProvider.
|
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Les routes API du module Fresnel seront définies ici
    // Exemple :
    // Route::apiResource('movies', MovieApiController::class);
});
