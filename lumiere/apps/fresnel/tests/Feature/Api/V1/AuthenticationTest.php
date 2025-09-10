<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure sanctum routes are loaded
        $this->artisan('migrate', ['--database' => 'testing']);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'Test Device'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id', 'name', 'email', 'created_at'
                    ],
                    'token',
                    'token_type',
                    'expires_at',
                    'abilities'
                ]
            ]);

        $this->assertEquals('Login successful', $response->json('message'));
        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertIsArray($response->json('data.abilities'));
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);

        $this->assertEquals('The provided credentials are incorrect.', $response->json('message'));
    }

    public function test_login_requires_email_and_password()
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_rate_limiting()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Make 6 failed login attempts (rate limit is 5)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        $response->assertStatus(429);
        $this->assertStringContainsString('Too many login attempts', $response->json('message'));
    }

    public function test_user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'Test Device'
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id', 'name', 'email', 'created_at'
                    ],
                    'token',
                    'token_type',
                    'expires_at',
                    'abilities'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ]);
    }

    public function test_user_cannot_register_with_duplicate_email()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_requires_valid_data()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456'
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_authenticated_user_can_get_user_info()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/user');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id', 'name', 'email', 'created_at'
                    ],
                    'abilities',
                    'token_name',
                    'last_used_at'
                ]
            ]);

        $this->assertEquals($user->id, $response->json('data.user.id'));
    }

    public function test_unauthenticated_user_cannot_get_user_info()
    {
        $response = $this->getJson('/api/v1/auth/user');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully'
            ]);

        // Verify token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class
        ]);
    }

    public function test_authenticated_user_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/refresh');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type',
                    'expires_at',
                    'abilities'
                ]
            ]);

        $this->assertNotEquals($token, $response->json('data.token'));
    }

    public function test_invalid_token_returns_unauthenticated()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/v1/auth/user');

        $response->assertStatus(401);
    }

    public function test_token_includes_correct_abilities()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password' // Default factory password
        ]);

        $abilities = $response->json('data.abilities');
        
        $this->assertContains('api:access', $abilities);
        $this->assertContains('movies:read', $abilities);
        $this->assertContains('festivals:read', $abilities);
        $this->assertContains('jobs:read', $abilities);
    }
}
