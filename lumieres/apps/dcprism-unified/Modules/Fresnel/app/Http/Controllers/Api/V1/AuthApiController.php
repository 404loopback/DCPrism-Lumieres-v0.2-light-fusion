<?php

namespace Modules\Fresnel\app\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Modules\Fresnel\app\Http\Controllers\Controller;
use Modules\Fresnel\app\Http\Resources\V1\UserResource;
use Modules\Fresnel\app\Models\User;

class AuthApiController extends Controller
{
    /**
     * Login user and create token
     *
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="User login",
     *     description="Authenticate user with email and password, returns access token",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="device_name", type="string", example="iPhone 12", description="Optional device identifier")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="1|abc123token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time"),
     *                 @OA\Property(property="abilities", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Invalid credentials",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *
     *     @OA\Response(
     *         response=429,
     *         description="Too many attempts",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Too many login attempts")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $key = 'api-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many login attempts. Please try again in '.$seconds.' seconds.',
                'errors' => [
                    'email' => ['Too many failed attempts'],
                ],
            ], 429);
        }

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-_\.]+$/'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key);

            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 422);
        }

        // Check if user is active
        if (isset($user->is_active) && ! $user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated.',
                'errors' => [
                    'email' => ['Account deactivated'],
                ],
            ], 403);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // Update last seen
        $user->update(['last_seen_at' => now()]);

        // Create token with abilities based on user role
        $abilities = $this->getUserAbilities($user);
        $deviceName = $request->device_name ?? $request->userAgent() ?? 'Unknown Device';

        $token = $user->createToken($deviceName, $abilities, now()->addDays(30));

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->accessToken->expires_at?->toISOString(),
                'abilities' => $abilities,
            ],
        ]);
    }

    /**
     * Register new user
     */
    public function register(Request $request): JsonResponse
    {
        $key = 'api-register:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many registration attempts. Please try again in '.$seconds.' seconds.',
            ], 429);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-_\.]+$/'],
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(), // Auto-verify for API registration
            ]);

            // Assign default role if using Spatie roles
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('user');
            }

            $abilities = $this->getUserAbilities($user);
            $deviceName = $request->device_name ?? $request->userAgent() ?? 'Unknown Device';

            $token = $user->createToken($deviceName, $abilities, now()->addDays(30));

            RateLimiter::clear($key);

            return response()->json([
                'message' => 'Registration successful',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $token->accessToken->expires_at?->toISOString(),
                    'abilities' => $abilities,
                ],
            ], 201);

        } catch (\Exception $e) {
            RateLimiter::hit($key);

            return response()->json([
                'message' => 'Registration failed',
                'errors' => [
                    'general' => ['Unable to create account. Please try again.'],
                ],
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()),
                'abilities' => $request->user()->currentAccessToken()->abilities ?? [],
                'token_name' => $request->user()->currentAccessToken()->name ?? null,
                'last_used_at' => $request->user()->currentAccessToken()->last_used_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Refresh user token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        // Create new token with same abilities and name
        $abilities = $currentToken->abilities ?? $this->getUserAbilities($user);
        $deviceName = $currentToken->name ?? 'Unknown Device';

        $newToken = $user->createToken($deviceName, $abilities, now()->addDays(30));

        // Delete old token
        $currentToken->delete();

        return response()->json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'user' => new UserResource($user),
                'token' => $newToken->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $newToken->accessToken->expires_at?->toISOString(),
                'abilities' => $abilities,
            ],
        ]);
    }

    /**
     * Get user abilities based on role
     */
    private function getUserAbilities(User $user): array
    {
        $baseAbilities = [
            'api:access',
            'movies:read',
            'festivals:read',
            'jobs:read',
        ];

        // Add abilities based on user role
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin')) {
                return [
                    '*', // All abilities for admin
                ];
            }

            if ($user->hasRole('festival-manager')) {
                return array_merge($baseAbilities, [
                    'movies:create',
                    'movies:update',
                    'festivals:create',
                    'festivals:update',
                    'festivals:manage-submissions',
                    'processing:start',
                    'jobs:retry',
                ]);
            }

            if ($user->hasRole('dcp-operator')) {
                return array_merge($baseAbilities, [
                    'movies:create',
                    'movies:update',
                    'processing:start',
                    'processing:validate',
                    'jobs:retry',
                    'jobs:cancel',
                ]);
            }
        }

        // Default user abilities
        return array_merge($baseAbilities, [
            'movies:create',
            'processing:basic',
        ]);
    }
}
