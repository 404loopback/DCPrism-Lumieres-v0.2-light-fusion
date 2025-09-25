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
    // Routes B2 Upload
    Route::prefix('b2')->group(function () {
        // Test de connexion B2 (ping)
        Route::get('ping', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'pingB2'])
            ->name('api.b2.ping');
        
        // Credentials et configuration
        Route::get('credentials', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'getB2Credentials'])
            ->name('api.b2.credentials');
        
        // Upload workflow
        Route::post('upload-path', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'generateUploadPath'])
            ->name('api.b2.upload-path');
        Route::post('multipart/init', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'initializeMultipart'])
            ->name('api.b2.multipart.init');
        Route::post('multipart/url', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'getPresignedUrl'])
            ->name('api.b2.multipart.url');
        Route::post('multipart/complete', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'completeMultipart'])
            ->name('api.b2.multipart.complete');
        Route::post('multipart/abort', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'abortMultipart'])
            ->name('api.b2.multipart.abort');
        
        // Upload management
        Route::post('upload/progress', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'updateProgress'])
            ->name('api.b2.upload.progress');
        Route::get('uploads/resumable', [\Modules\Fresnel\app\Http\Controllers\Api\B2UploadController::class, 'getResumableUploads'])
            ->name('api.b2.uploads.resumable');
    });
    
    // Autres routes API du module Fresnel
    // Exemple :
    // Route::apiResource('movies', MovieApiController::class);
});

// Routes spécifiques au Manager Panel
Route::middleware(['auth:sanctum', 'panel:manager'])->prefix('manager')->group(function () {
    // Version preview pour le wizard de création de films
    Route::post('movies/preview-version', [\Modules\Fresnel\app\Http\Controllers\Api\VersionPreviewController::class, 'previewVersion'])
        ->name('manager.movies.preview-version');
});
