<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Movie;
use App\Models\Festival;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FestivalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['api:access', 'festivals:read', 'festivals:write']);
    }

    public function test_can_list_festivals()
    {
        Festival::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/festivals');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'location', 'start_date', 'end_date',
                        'description', 'website', 'status', 'created_at'
                    ]
                ],
                'meta' => [
                    'current_page', 'per_page', 'total', 'last_page'
                ]
            ]);

        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_can_create_festival()
    {
        $festivalData = [
            'name' => 'Cannes Film Festival',
            'location' => 'Cannes, France',
            'start_date' => '2024-05-14',
            'end_date' => '2024-05-25',
            'description' => 'The most prestigious film festival in the world',
            'website' => 'https://www.festival-cannes.com',
            'contact_email' => 'info@festival-cannes.com',
            'status' => 'upcoming'
        ];

        $response = $this->postJson('/api/v1/festivals', $festivalData);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'name', 'location', 'start_date', 'end_date',
                    'description', 'website', 'status', 'created_at'
                ]
            ]);

        $this->assertDatabaseHas('festivals', [
            'name' => 'Cannes Film Festival',
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('Festival created successfully', $response->json('message'));
    }

    public function test_cannot_create_festival_without_required_fields()
    {
        $response = $this->postJson('/api/v1/festivals', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'location', 'start_date', 'end_date']);
    }

    public function test_cannot_create_festival_with_end_date_before_start_date()
    {
        $festivalData = [
            'name' => 'Test Festival',
            'location' => 'Test Location',
            'start_date' => '2024-05-25',
            'end_date' => '2024-05-20' // End date before start date
        ];

        $response = $this->postJson('/api/v1/festivals', $festivalData);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_can_show_festival()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/festivals/{$festival->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'location', 'start_date', 'end_date',
                    'description', 'website', 'status', 'movies_count',
                    'created_at', 'updated_at'
                ]
            ]);

        $this->assertEquals($festival->id, $response->json('data.id'));
        $this->assertEquals($festival->name, $response->json('data.name'));
    }

    public function test_can_update_festival()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);
        
        $updateData = [
            'name' => 'Updated Festival Name',
            'location' => 'Updated Location',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/v1/festivals/{$festival->id}", $updateData);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'name', 'location', 'description', 'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('festivals', [
            'id' => $festival->id,
            'name' => 'Updated Festival Name',
            'location' => 'Updated Location'
        ]);

        $this->assertEquals('Festival updated successfully', $response->json('message'));
    }

    public function test_can_delete_festival()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/festivals/{$festival->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Festival deleted successfully'
            ]);

        $this->assertSoftDeleted('festivals', ['id' => $festival->id]);
    }

    public function test_can_filter_festivals_by_status()
    {
        Festival::factory()->create(['user_id' => $this->user->id, 'status' => 'upcoming']);
        Festival::factory()->create(['user_id' => $this->user->id, 'status' => 'ongoing']);
        Festival::factory()->create(['user_id' => $this->user->id, 'status' => 'upcoming']);

        $response = $this->getJson('/api/v1/festivals?status=upcoming');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $festival) {
            $this->assertEquals('upcoming', $festival['status']);
        }
    }

    public function test_can_filter_festivals_by_date_range()
    {
        Festival::factory()->create([
            'user_id' => $this->user->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-25'
        ]);
        Festival::factory()->create([
            'user_id' => $this->user->id,
            'start_date' => '2024-06-15',
            'end_date' => '2024-06-25'
        ]);

        $response = $this->getJson('/api/v1/festivals?date_from=2024-01-01&date_to=2024-02-01');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $festival = $response->json('data')[0];
        $this->assertEquals('2024-01-15', $festival['start_date']);
    }

    public function test_can_search_festivals_by_name()
    {
        Festival::factory()->create(['user_id' => $this->user->id, 'name' => 'Cannes Film Festival']);
        Festival::factory()->create(['user_id' => $this->user->id, 'name' => 'Venice Film Festival']);
        Festival::factory()->create(['user_id' => $this->user->id, 'name' => 'Berlin International']);

        $response = $this->getJson('/api/v1/festivals?search=Film');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $festival) {
            $this->assertStringContainsString('Film', $festival['name']);
        }
    }

    public function test_can_attach_movies_to_festival()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);
        $movies = Movie::factory()->count(3)->create(['user_id' => $this->user->id]);
        $movieIds = $movies->pluck('id')->toArray();

        $response = $this->postJson("/api/v1/festivals/{$festival->id}/movies/attach", [
            'movie_ids' => $movieIds
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'attached_count',
                    'skipped_count',
                    'attached_movies' => [
                        '*' => ['id', 'title']
                    ]
                ]
            ]);

        $this->assertEquals(3, $response->json('data.attached_count'));
        $this->assertEquals(0, $response->json('data.skipped_count'));

        foreach ($movieIds as $movieId) {
            $this->assertDatabaseHas('festival_movie', [
                'festival_id' => $festival->id,
                'movie_id' => $movieId
            ]);
        }
    }

    public function test_can_detach_movies_from_festival()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);
        $movies = Movie::factory()->count(3)->create(['user_id' => $this->user->id]);
        $festival->movies()->attach($movies->pluck('id'));

        $response = $this->deleteJson("/api/v1/festivals/{$festival->id}/movies/detach", [
            'movie_ids' => [$movies->first()->id]
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'detached_count'
                ]
            ]);

        $this->assertEquals(1, $response->json('data.detached_count'));
        $this->assertDatabaseMissing('festival_movie', [
            'festival_id' => $festival->id,
            'movie_id' => $movies->first()->id
        ]);
    }

    public function test_can_get_festival_movies()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);
        $movies = Movie::factory()->count(5)->create(['user_id' => $this->user->id]);
        $festival->movies()->attach($movies->pluck('id'));

        $response = $this->getJson("/api/v1/festivals/{$festival->id}/movies");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'title', 'description', 'duration',
                        'pivot' => ['festival_id', 'movie_id', 'created_at']
                    ]
                ],
                'meta' => [
                    'current_page', 'per_page', 'total'
                ]
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_can_get_festival_statistics()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);
        $movies = Movie::factory()->count(10)->create(['user_id' => $this->user->id]);
        $festival->movies()->attach($movies->pluck('id'));

        $response = $this->getJson("/api/v1/festivals/{$festival->id}/statistics");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'movies_count',
                    'total_duration',
                    'average_duration',
                    'genres_distribution',
                    'resolutions_distribution',
                    'submissions_by_month',
                    'top_countries'
                ]
            ]);

        $this->assertEquals(10, $response->json('data.movies_count'));
        $this->assertIsArray($response->json('data.genres_distribution'));
    }

    public function test_can_bulk_process_festival_movies()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);
        $movies = Movie::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);
        $festival->movies()->attach($movies->pluck('id'));

        $response = $this->postJson("/api/v1/festivals/{$festival->id}/process", [
            'operations' => ['validate', 'extract_metadata'],
            'movie_ids' => $movies->pluck('id')->toArray()
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'batch_id',
                    'total_movies',
                    'operations',
                    'estimated_duration',
                    'job_ids'
                ]
            ]);

        $this->assertEquals(3, $response->json('data.total_movies'));
        $this->assertContains('validate', $response->json('data.operations'));
        $this->assertContains('extract_metadata', $response->json('data.operations'));
    }

    public function test_can_validate_festival_movies()
    {
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);
        $movies = Movie::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);
        $festival->movies()->attach($movies->pluck('id'));

        $response = $this->postJson("/api/v1/festivals/{$festival->id}/validate");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'validation_batch_id',
                    'total_movies',
                    'job_ids'
                ]
            ]);

        $this->assertEquals(2, $response->json('data.total_movies'));
    }

    public function test_can_get_public_festivals()
    {
        Festival::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_public' => true,
            'status' => 'upcoming'
        ]);
        Festival::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_public' => false
        ]);

        // Test without authentication
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/v1/public/festivals');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');

        foreach ($response->json('data') as $festival) {
            $this->assertTrue($festival['is_public']);
        }
    }

    public function test_can_get_public_festival_details()
    {
        $festival = Festival::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => true
        ]);

        // Test without authentication
        Sanctum::actingAs(null);

        $response = $this->getJson("/api/v1/public/festivals/{$festival->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'location', 'start_date', 'end_date',
                    'description', 'website', 'status'
                ]
            ]);

        // Should not include sensitive data
        $this->assertArrayNotHasKey('user_id', $response->json('data'));
        $this->assertArrayNotHasKey('contact_email', $response->json('data'));
    }

    public function test_cannot_access_private_festival_publicly()
    {
        $festival = Festival::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => false
        ]);

        Sanctum::actingAs(null);

        $response = $this->getJson("/api/v1/public/festivals/{$festival->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_only_access_their_festivals()
    {
        $otherUser = User::factory()->create();
        $userFestival = Festival::factory()->create(['user_id' => $this->user->id]);
        $otherUserFestival = Festival::factory()->create(['user_id' => $otherUser->id]);

        // List festivals - should only see own festivals
        $response = $this->getJson('/api/v1/festivals');
        $festivals = $response->json('data');
        $festivalIds = array_column($festivals, 'id');
        
        $this->assertContains($userFestival->id, $festivalIds);
        $this->assertNotContains($otherUserFestival->id, $festivalIds);

        // Try to access other user's festival
        $response = $this->getJson("/api/v1/festivals/{$otherUserFestival->id}");
        $response->assertStatus(404);
    }

    public function test_pagination_works_correctly()
    {
        Festival::factory()->count(25)->create(['user_id' => $this->user->id]);

        // Test first page
        $response = $this->getJson('/api/v1/festivals?page=1&per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.current_page'));
        $this->assertEquals(25, $response->json('meta.total'));

        // Test second page
        $response = $this->getJson('/api/v1/festivals?page=2&per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(2, $response->json('meta.current_page'));
    }

    public function test_cannot_attach_movies_that_dont_belong_to_user()
    {
        $otherUser = User::factory()->create();
        $festival = Festival::factory()->create(['user_id' => $this->user->id]);
        $otherUserMovie = Movie::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->postJson("/api/v1/festivals/{$festival->id}/movies/attach", [
            'movie_ids' => [$otherUserMovie->id]
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Some movies do not exist or do not belong to you', 
            $response->json('message'));
    }

    public function test_unauthenticated_user_cannot_access_festivals()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/v1/festivals');
        $response->assertStatus(401);
    }

    public function test_user_without_proper_abilities_cannot_create_festivals()
    {
        // Create user with only read access
        Sanctum::actingAs($this->user, ['api:access', 'festivals:read']);

        $response = $this->postJson('/api/v1/festivals', [
            'name' => 'Test Festival',
            'location' => 'Test Location',
            'start_date' => '2024-05-01',
            'end_date' => '2024-05-10'
        ]);

        $response->assertStatus(403);
    }
}
