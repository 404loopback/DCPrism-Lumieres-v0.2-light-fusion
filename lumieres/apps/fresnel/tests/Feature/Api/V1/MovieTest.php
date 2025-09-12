<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Movie;
use App\Models\Festival;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MovieTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['api:access', 'movies:read', 'movies:write']);
        Storage::fake('local');
    }

    public function test_can_list_movies()
    {
        Movie::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/movies');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'title', 'description', 'duration',
                        'resolution', 'aspect_ratio', 'frame_rate',
                        'created_at', 'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page', 'per_page', 'total', 'last_page'
                ]
            ]);

        $this->assertCount(10, $response->json('data')); // Default pagination
        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_can_create_movie()
    {
        $movieData = [
            'title' => 'Test Movie',
            'description' => 'This is a test movie',
            'duration' => 120,
            'resolution' => '2K',
            'aspect_ratio' => '1.85:1',
            'frame_rate' => 24.0,
            'genre' => 'Drama',
            'release_date' => '2024-01-15'
        ];

        $response = $this->postJson('/api/v1/movies', $movieData);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'title', 'description', 'duration',
                    'resolution', 'aspect_ratio', 'frame_rate',
                    'genre', 'release_date', 'created_at'
                ]
            ]);

        $this->assertDatabaseHas('movies', [
            'title' => 'Test Movie',
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('Movie created successfully', $response->json('message'));
    }

    public function test_cannot_create_movie_without_required_fields()
    {
        $response = $this->postJson('/api/v1/movies', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'duration']);
    }

    public function test_can_show_movie()
    {
        $movie = Movie::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/movies/{$movie->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'description', 'duration',
                    'resolution', 'aspect_ratio', 'frame_rate',
                    'created_at', 'updated_at'
                ]
            ]);

        $this->assertEquals($movie->id, $response->json('data.id'));
        $this->assertEquals($movie->title, $response->json('data.title'));
    }

    public function test_cannot_show_movie_that_does_not_exist()
    {
        $response = $this->getJson('/api/v1/movies/999999');

        $response->assertStatus(404);
    }

    public function test_can_update_movie()
    {
        $movie = Movie::factory()->create(['user_id' => $this->user->id]);
        
        $updateData = [
            'title' => 'Updated Movie Title',
            'description' => 'Updated description',
            'duration' => 135
        ];

        $response = $this->putJson("/api/v1/movies/{$movie->id}", $updateData);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'title', 'description', 'duration',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'title' => 'Updated Movie Title',
            'description' => 'Updated description',
            'duration' => 135
        ]);

        $this->assertEquals('Movie updated successfully', $response->json('message'));
    }

    public function test_can_delete_movie()
    {
        $movie = Movie::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/movies/{$movie->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Movie deleted successfully'
            ]);

        $this->assertSoftDeleted('movies', ['id' => $movie->id]);
    }

    public function test_can_filter_movies_by_genre()
    {
        Movie::factory()->create(['user_id' => $this->user->id, 'genre' => 'Drama']);
        Movie::factory()->create(['user_id' => $this->user->id, 'genre' => 'Comedy']);
        Movie::factory()->create(['user_id' => $this->user->id, 'genre' => 'Drama']);

        $response = $this->getJson('/api/v1/movies?genre=Drama');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $movie) {
            $this->assertEquals('Drama', $movie['genre']);
        }
    }

    public function test_can_search_movies_by_title()
    {
        Movie::factory()->create(['user_id' => $this->user->id, 'title' => 'The Great Adventure']);
        Movie::factory()->create(['user_id' => $this->user->id, 'title' => 'Adventure Time']);
        Movie::factory()->create(['user_id' => $this->user->id, 'title' => 'Comedy Show']);

        $response = $this->getJson('/api/v1/movies?search=Adventure');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $movie) {
            $this->assertStringContainsString('Adventure', $movie['title']);
        }
    }

    public function test_can_sort_movies()
    {
        Movie::factory()->create(['user_id' => $this->user->id, 'title' => 'Z Movie']);
        Movie::factory()->create(['user_id' => $this->user->id, 'title' => 'A Movie']);
        Movie::factory()->create(['user_id' => $this->user->id, 'title' => 'M Movie']);

        $response = $this->getJson('/api/v1/movies?sort=title&direction=asc');

        $movies = $response->json('data');
        $this->assertEquals('A Movie', $movies[0]['title']);
        $this->assertEquals('M Movie', $movies[1]['title']);
        $this->assertEquals('Z Movie', $movies[2]['title']);
    }

    public function test_can_upload_dcp_file()
    {
        $movie = Movie::factory()->create(['user_id' => $this->user->id]);
        $dcpFile = UploadedFile::fake()->create('test_dcp.zip', 1000, 'application/zip');

        $response = $this->postJson("/api/v1/movies/{$movie->id}/upload", [
            'dcp_file' => $dcpFile
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'file_path',
                    'file_size',
                    'upload_status',
                    'processing_job_id'
                ]
            ]);

        $this->assertEquals('DCP file uploaded successfully', $response->json('message'));
        Storage::disk('local')->assertExists($response->json('data.file_path'));
    }

    public function test_cannot_upload_invalid_dcp_file()
    {
        $movie = Movie::factory()->create(['user_id' => $this->user->id]);
        $invalidFile = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson("/api/v1/movies/{$movie->id}/upload", [
            'dcp_file' => $invalidFile
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['dcp_file']);
    }

    public function test_can_validate_dcp()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);

        $response = $this->postJson("/api/v1/movies/{$movie->id}/validate");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'validation_job_id',
                    'status'
                ]
            ]);

        $this->assertEquals('DCP validation started', $response->json('message'));
        $this->assertEquals('queued', $response->json('data.status'));
    }

    public function test_cannot_validate_movie_without_dcp()
    {
        $movie = Movie::factory()->create(['user_id' => $this->user->id, 'dcp_path' => null]);

        $response = $this->postJson("/api/v1/movies/{$movie->id}/validate");

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'No DCP file found for this movie'
            ]);
    }

    public function test_can_extract_metadata()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);

        $response = $this->postJson("/api/v1/movies/{$movie->id}/metadata");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'extraction_job_id',
                    'status'
                ]
            ]);

        $this->assertEquals('Metadata extraction started', $response->json('message'));
    }

    public function test_can_generate_nomenclature()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'The Great Adventure',
            'resolution' => '2K',
            'aspect_ratio' => '1.85:1',
            'frame_rate' => 24.0
        ]);

        $response = $this->postJson("/api/v1/movies/{$movie->id}/nomenclature", [
            'template' => 'dcp'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'nomenclature',
                    'template_used'
                ]
            ]);

        $nomenclature = $response->json('data.nomenclature');
        $this->assertStringContainsString('TheGreatAdventure', $nomenclature);
        $this->assertStringContainsString('2K', $nomenclature);
    }

    public function test_pagination_works_correctly()
    {
        Movie::factory()->count(25)->create(['user_id' => $this->user->id]);

        // Test first page
        $response = $this->getJson('/api/v1/movies?page=1&per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.current_page'));
        $this->assertEquals(25, $response->json('meta.total'));

        // Test second page
        $response = $this->getJson('/api/v1/movies?page=2&per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(2, $response->json('meta.current_page'));
    }

    public function test_user_can_only_access_their_movies()
    {
        $otherUser = User::factory()->create();
        $userMovie = Movie::factory()->create(['user_id' => $this->user->id]);
        $otherUserMovie = Movie::factory()->create(['user_id' => $otherUser->id]);

        // List movies - should only see own movies
        $response = $this->getJson('/api/v1/movies');
        $movies = $response->json('data');
        $movieIds = array_column($movies, 'id');
        
        $this->assertContains($userMovie->id, $movieIds);
        $this->assertNotContains($otherUserMovie->id, $movieIds);

        // Try to access other user's movie
        $response = $this->getJson("/api/v1/movies/{$otherUserMovie->id}");
        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_movies()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/v1/movies');
        $response->assertStatus(401);
    }

    public function test_user_without_proper_abilities_cannot_create_movies()
    {
        // Create user with only read access
        Sanctum::actingAs($this->user, ['api:access', 'movies:read']);

        $response = $this->postJson('/api/v1/movies', [
            'title' => 'Test Movie',
            'duration' => 120
        ]);

        $response->assertStatus(403);
    }
}
