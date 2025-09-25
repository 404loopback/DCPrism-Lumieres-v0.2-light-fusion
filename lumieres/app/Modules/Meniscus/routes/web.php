<?php

use Illuminate\Support\Facades\Route;
use Modules\Fresnel\app\Models\User;

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

// Redirection vers le panel admin Filament
Route::get('/', function () {
    return redirect('/admin');
});

// Performance test route (can be removed in production)
Route::get('/health', function () {
    $start = microtime(true);
    $userCount = User::count();
    $dbTime = round((microtime(true) - $start) * 1000, 2);

    return response()->json([
        'status' => 'healthy',
        'database' => 'connected',
        'users_count' => $userCount,
        'response_time_ms' => round((microtime(true) - $start) * 1000, 2),
    ]);
});
