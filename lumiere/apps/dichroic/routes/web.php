<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Main application route (SPA)
Route::get('/', function () {
    return view('welcome');
});

// Performance test route (can be removed in production)
Route::get('/health', function () {
    $start = microtime(true);
    $userCount = \App\Models\User::count();
    $dbTime = round((microtime(true) - $start) * 1000, 2);
    
    return response()->json([
        'status' => 'healthy',
        'database' => 'connected',
        'users_count' => $userCount,
        'response_time_ms' => round((microtime(true) - $start) * 1000, 2)
    ]);
});
