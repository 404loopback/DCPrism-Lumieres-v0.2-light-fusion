<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Include app-specific routes
Route::prefix('fresnel')->group(base_path('apps/fresnel/routes/web.php'));
Route::prefix('meniscus')->group(base_path('apps/meniscus/routes/web.php'));
