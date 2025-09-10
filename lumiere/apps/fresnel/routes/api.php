<?php

use App\Http\Controllers\Api\B2UploadController;
use App\Http\Controllers\Api\V1\MovieApiController;
use App\Http\Controllers\Api\V1\FestivalApiController;
use App\Http\Controllers\Api\V1\JobApiController;
use App\Http\Controllers\Api\V1\AuthApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Legacy user route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Legacy B2 Upload routes (keep for backward compatibility)
Route::middleware(['auth:sanctum'])->prefix('upload')->group(function () {
    Route::get('b2-credentials', [B2UploadController::class, 'getB2Credentials']);
    Route::post('generate-path', [B2UploadController::class, 'generateUploadPath']);
    Route::post('initialize-multipart', [B2UploadController::class, 'initializeMultipart']);
    Route::post('get-presigned-url', [B2UploadController::class, 'getPresignedUrl']);
    Route::post('complete-multipart', [B2UploadController::class, 'completeMultipart']);
    Route::post('abort-multipart', [B2UploadController::class, 'abortMultipart']);
    Route::post('update-progress', [B2UploadController::class, 'updateProgress']);
    Route::get('resumable-uploads', [B2UploadController::class, 'getResumableUploads']);
});

// ============================================================================
// API v1 Routes - DCPrism REST API
// ============================================================================

// Authentication routes
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthApiController::class, 'login'])->name('api.v1.auth.login');
    Route::post('/auth/register', [AuthApiController::class, 'register'])->name('api.v1.auth.register');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/auth/user', [AuthApiController::class, 'user'])->name('api.v1.auth.user');
        Route::post('/auth/refresh', [AuthApiController::class, 'refresh'])->name('api.v1.auth.refresh');
    });
});

// Protected API routes v1
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api', 'api.cors', 'api.logger'])->group(function () {
    
    // Movies API endpoints
    Route::apiResource('movies', MovieApiController::class)->names([
        'index' => 'api.v1.movies.index',
        'store' => 'api.v1.movies.store',
        'show' => 'api.v1.movies.show',
        'update' => 'api.v1.movies.update',
        'destroy' => 'api.v1.movies.destroy'
    ]);
    
    // Additional movie endpoints
    Route::prefix('movies')->group(function () {
        Route::get('/{movie}/dcp-status', [MovieApiController::class, 'dcpStatus'])->name('api.v1.movies.dcp-status');
        Route::post('/{movie}/upload-dcp', [MovieApiController::class, 'uploadDcp'])->name('api.v1.movies.upload-dcp');
        Route::get('/{movie}/download-dcp', [MovieApiController::class, 'downloadDcp'])->name('api.v1.movies.download-dcp');
        Route::post('/{movie}/validate', [MovieApiController::class, 'validate'])->name('api.v1.movies.validate');
        Route::get('/{movie}/metadata', [MovieApiController::class, 'metadata'])->name('api.v1.movies.metadata');
        Route::get('/{movie}/processing-history', [MovieApiController::class, 'processingHistory'])->name('api.v1.movies.processing-history');
    });
    
    // Festivals API endpoints  
    Route::apiResource('festivals', FestivalApiController::class)->names([
        'index' => 'api.v1.festivals.index',
        'store' => 'api.v1.festivals.store', 
        'show' => 'api.v1.festivals.show',
        'update' => 'api.v1.festivals.update',
        'destroy' => 'api.v1.festivals.destroy'
    ]);
    
    // Additional festival endpoints
    Route::prefix('festivals')->group(function () {
        Route::get('/{festival}/movies', [FestivalApiController::class, 'movies'])->name('api.v1.festivals.movies');
        Route::post('/{festival}/movies/{movie}', [FestivalApiController::class, 'attachMovie'])->name('api.v1.festivals.attach-movie');
        Route::delete('/{festival}/movies/{movie}', [FestivalApiController::class, 'detachMovie'])->name('api.v1.festivals.detach-movie');
        Route::get('/{festival}/statistics', [FestivalApiController::class, 'statistics'])->name('api.v1.festivals.statistics');
        Route::post('/{festival}/bulk-upload', [FestivalApiController::class, 'bulkUpload'])->name('api.v1.festivals.bulk-upload');
    });
    
    
    // DCP Processing endpoints supprimés - traitement externe
    // Les endpoints de traitement DCP ont été supprimés car le processing
    // se fait sur un système externe, pas sur cette application Laravel.
    
    // System information endpoints
    Route::prefix('system')->group(function () {
        Route::get('/status', function () {
            return response()->json([
                'status' => 'healthy',
                'version' => config('app.version', '1.0.0'),
                'timestamp' => now()->toISOString(),
                'octane_enabled' => extension_loaded('swoole') || extension_loaded('roadrunner'),
                'environment' => app()->environment()
            ]);
        })->name('api.v1.system.status');
        
        Route::get('/statistics', function () {
            return response()->json([
                'movies_total' => \App\Models\Movie::count(),
                'festivals_total' => \App\Models\Festival::count(), 
                'jobs_active' => 0,
                'jobs_completed_today' => 0,
                'storage_used' => \Illuminate\Support\Facades\Storage::disk('public')->size('dcp') ?? 0,
                'uptime' => now()->diffInMinutes(\Illuminate\Support\Facades\Cache::remember('app_start_time', 60, fn() => now()))
            ]);
        })->name('api.v1.system.statistics');
    });
});

// Public API endpoints (no authentication required)
Route::prefix('v1/public')->middleware(['throttle:public', 'api.cors'])->group(function () {
    Route::get('/festivals', [FestivalApiController::class, 'publicIndex'])->name('api.v1.public.festivals');
    Route::get('/festivals/{festival}', [FestivalApiController::class, 'publicShow'])->name('api.v1.public.festivals.show');
    Route::get('/movies/search', [MovieApiController::class, 'publicSearch'])->name('api.v1.public.movies.search');
});

// Webhook endpoints for external integrations
Route::prefix('v1/webhooks')->middleware(['api.webhook.signature'])->group(function () {
    Route::post('/festival-submission', [FestivalApiController::class, 'webhookSubmission'])->name('api.v1.webhooks.festival-submission');
    // Route::post('/processing-callback', ...) - supprimée avec DcpProcessingApiController
    Route::post('/upload-notification', [MovieApiController::class, 'webhookUploadNotification'])->name('api.v1.webhooks.upload-notification');
});
