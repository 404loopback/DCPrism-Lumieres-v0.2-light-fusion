<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API Status endpoint
Route::get('/status', function () {
    return response()->json([
        'name' => 'DCParty API',
        'version' => '1.0.0',
        'description' => 'Digital Cinema Package encoding orchestration API',
        'status' => 'operational',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});

// Debug routes (should be removed in production)
Route::group(['prefix' => 'debug'], function () {
    // Test des utilisateurs
    Route::get('/users', function () {
        $users = \App\Models\User::all(['id', 'name', 'email', 'created_at']);

        return response()->json([
            'users' => $users,
            'count' => $users->count(),
        ]);
    });

    // Debug hash de mot de passe
    Route::get('/password/{email}', function ($email) {
        $user = \App\Models\User::where('email', $email)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found']);
        }

        return response()->json([
            'user' => $user->email,
            'password_hash' => $user->password,
            'check_admin123' => \Illuminate\Support\Facades\Hash::check('admin123', $user->password),
            'check_password' => \Illuminate\Support\Facades\Hash::check('password', $user->password),
            'check_password123' => \Illuminate\Support\Facades\Hash::check('password123', $user->password),
        ]);
    });

    // Test hash simple
    Route::get('/test-hash', function () {
        $password = 'simple123';
        $hash1 = \Illuminate\Support\Facades\Hash::make($password);
        $hash2 = bcrypt($password);

        return response()->json([
            'password' => $password,
            'hash1' => $hash1,
            'hash2' => $hash2,
            'check1' => \Illuminate\Support\Facades\Hash::check($password, $hash1),
            'check2' => \Illuminate\Support\Facades\Hash::check($password, $hash2),
            'check_cross' => \Illuminate\Support\Facades\Hash::check($password, $hash2),
        ]);
    });
});

// Auth routes
Route::group(['prefix' => 'auth'], function () {
    // Test de login simple
    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->only('email', 'password');

        if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            return response()->json([
                'success' => true,
                'user' => \Illuminate\Support\Facades\Auth::user(),
                'message' => 'Login successful',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ], 401);
    });
});

// Admin routes (should be protected in production)
Route::group(['prefix' => 'admin'], function () {
    // Créer un utilisateur admin proprement
    Route::post('/create-admin', function () {
        $password = 'admin123';

        $user = \App\Models\User::updateOrCreate(
            ['email' => 'admin@dcparty.local'],
            [
                'name' => 'Admin DCParty',
                'email' => 'admin@dcparty.local',
                'password' => $password, // Plain text - le mutateur va hasher
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'Admin user created successfully',
            'email' => $user->email,
            'password_test' => 'admin123',
            'hash_check' => \Illuminate\Support\Facades\Hash::check($password, $user->password),
        ]);
    });
});
