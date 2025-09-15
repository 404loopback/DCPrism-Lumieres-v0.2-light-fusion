<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Include app-specific API routes
Route::prefix('fresnel')->group(base_path('apps/fresnel/routes/api.php'));
Route::prefix('meniscus')->group(base_path('apps/meniscus/routes/api.php'));
