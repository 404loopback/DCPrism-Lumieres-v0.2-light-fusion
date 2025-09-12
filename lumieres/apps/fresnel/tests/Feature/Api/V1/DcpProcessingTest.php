<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DcpProcessingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['api:access', 'dcp:read', 'dcp:write']);
        Storage::fake('local');
    }

    public function test_can_analyze_dcp()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);

        Storage::put('dcps/test_movie.zip', 'fake dcp content');

        $response = $this->postJson('/api/v1/dcp/analyze', [
            'movie_id' => $movie->id,
            'deep_analysis' => true
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'analysis_id',
                    'movie_id',
                    'status',
                    'job_id',
                    'estimated_duration'
                ]
            ]);

        $this->assertEquals('DCP analysis started successfully', $response->json('message'));
        $this->assertEquals('queued', $response->json('data.status'));
    }

    public function test_cannot_analyze_movie_without_dcp()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'dcp_path' => null
        ]);

        $response = $this->postJson('/api/v1/dcp/analyze', [
            'movie_id' => $movie->id
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Movie does not have a DCP file associated'
            ]);
    }

    public function test_can_validate_dcp()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);

        Storage::put('dcps/test_movie.zip', 'fake dcp content');

        $response = $this->postJson('/api/v1/dcp/validate', [
            'movie_id' => $movie->id,
            'validation_level' => 'strict'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'validation_id',
                    'movie_id',
                    'status',
                    'job_id',
                    'validation_level'
                ]
            ]);

        $this->assertEquals('DCP validation started successfully', $response->json('message'));
        $this->assertEquals('strict', $response->json('data.validation_level'));
    }

    public function test_can_extract_metadata()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);

        Storage::put('dcps/test_movie.zip', 'fake dcp content');

        $response = $this->postJson('/api/v1/dcp/metadata', [
            'movie_id' => $movie->id,
            'extract_technical' => true,
            'extract_assets' => true
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'extraction_id',
                    'movie_id',
                    'status',
                    'job_id',
                    'extraction_options'
                ]
            ]);

        $this->assertEquals('Metadata extraction started successfully', $response->json('message'));
        $this->assertTrue($response->json('data.extraction_options.extract_technical'));
        $this->assertTrue($response->json('data.extraction_options.extract_assets'));
    }

    public function test_can_generate_nomenclature()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'The Great Adventure',
            'resolution' => '2K',
            'aspect_ratio' => '1.85:1',
            'frame_rate' => 24.0,
            'genre' => 'Drama'
        ]);

        $response = $this->postJson('/api/v1/dcp/nomenclature', [
            'movie_id' => $movie->id,
            'template' => 'dcp',
            'custom_fields' => [
                'studio' => 'StudioA',
                'year' => '2024'
            ]
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'nomenclature',
                    'template_used',
                    'movie_data',
                    'custom_fields'
                ]
            ]);

        $nomenclature = $response->json('data.nomenclature');
        $this->assertStringContainsString('TheGreatAdventure', $nomenclature);
        $this->assertStringContainsString('2K', $nomenclature);
        $this->assertStringContainsString('2024', $nomenclature);
    }

    public function test_can_batch_process_dcps()
    {
        $movies = Movie::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);

        foreach ($movies as $movie) {
            Storage::put("dcps/movie_{$movie->id}.zip", 'fake dcp content');
        }

        $response = $this->postJson('/api/v1/dcp/batch-process', [
            'movie_ids' => $movies->pluck('id')->toArray(),
            'operations' => ['analyze', 'validate', 'extract_metadata'],
            'options' => [
                'validation_level' => 'standard',
                'deep_analysis' => false
            ]
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'batch_id',
                    'total_movies',
                    'operations',
                    'job_ids',
                    'estimated_duration'
                ]
            ]);

        $this->assertEquals(3, $response->json('data.total_movies'));
        $this->assertCount(3, $response->json('data.operations'));
        $this->assertEquals('Batch DCP processing started successfully', $response->json('message'));
    }

    public function test_can_initiate_chunked_upload()
    {
        $response = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 1073741824, // 1GB
            'chunk_size' => 5242880, // 5MB
            'movie_id' => null
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'upload_id',
                    'total_chunks',
                    'chunk_size',
                    'expires_at',
                    'upload_url_pattern'
                ]
            ]);

        $this->assertEquals('Chunked upload initialized successfully', $response->json('message'));
        $this->assertEquals(205, $response->json('data.total_chunks')); // 1GB / 5MB rounded up
    }

    public function test_can_upload_chunk()
    {
        // First initialize upload
        $initResponse = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 10485760, // 10MB
            'chunk_size' => 5242880   // 5MB
        ]);

        $uploadId = $initResponse->json('data.upload_id');
        
        // Create a fake chunk file
        $chunkFile = UploadedFile::fake()->create('chunk_1', 5120, 'application/octet-stream'); // 5MB

        $response = $this->postJson("/api/v1/dcp/upload/{$uploadId}/chunk", [
            'chunk_number' => 1,
            'chunk_data' => $chunkFile
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'upload_id',
                    'chunk_number',
                    'uploaded_size',
                    'total_size',
                    'progress_percentage',
                    'remaining_chunks'
                ]
            ]);

        $this->assertEquals('Chunk uploaded successfully', $response->json('message'));
        $this->assertEquals(1, $response->json('data.chunk_number'));
        $this->assertEquals(50.0, $response->json('data.progress_percentage'));
    }

    public function test_can_finalize_chunked_upload()
    {
        // Initialize upload
        $initResponse = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 10485760,
            'chunk_size' => 5242880,
            'movie_id' => null
        ]);

        $uploadId = $initResponse->json('data.upload_id');

        // Simulate uploading all chunks by updating the upload record directly
        // In a real scenario, this would be done by uploading actual chunks

        $response = $this->postJson("/api/v1/dcp/upload/{$uploadId}/finalize", [
            'callback_url' => 'https://example.com/webhook',
            'auto_process' => true
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'upload_id',
                    'file_path',
                    'final_size',
                    'movie_id',
                    'processing_job_id',
                    'callback_url'
                ]
            ]);

        $this->assertEquals('File upload completed and processing started', $response->json('message'));
    }

    public function test_can_get_upload_progress()
    {
        // Initialize upload
        $initResponse = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 10485760,
            'chunk_size' => 5242880
        ]);

        $uploadId = $initResponse->json('data.upload_id');

        $response = $this->getJson("/api/v1/dcp/upload/{$uploadId}/progress");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'upload_id',
                    'filename',
                    'total_size',
                    'uploaded_size',
                    'progress_percentage',
                    'chunks_uploaded',
                    'total_chunks',
                    'status',
                    'created_at',
                    'expires_at'
                ]
            ]);

        $this->assertEquals($uploadId, $response->json('data.upload_id'));
        $this->assertEquals('test_movie.zip', $response->json('data.filename'));
    }

    public function test_can_cancel_upload()
    {
        // Initialize upload
        $initResponse = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 10485760,
            'chunk_size' => 5242880
        ]);

        $uploadId = $initResponse->json('data.upload_id');

        $response = $this->deleteJson("/api/v1/dcp/upload/{$uploadId}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Upload cancelled and cleaned up successfully'
            ]);
    }

    public function test_can_resume_upload()
    {
        // Initialize upload
        $initResponse = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 10485760,
            'chunk_size' => 5242880
        ]);

        $uploadId = $initResponse->json('data.upload_id');

        $response = $this->postJson("/api/v1/dcp/upload/{$uploadId}/resume");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'upload_id',
                    'next_chunk_number',
                    'uploaded_chunks',
                    'remaining_chunks',
                    'progress_percentage'
                ]
            ]);

        $this->assertEquals('Upload resume information retrieved', $response->json('message'));
    }

    public function test_cannot_upload_chunk_with_invalid_upload_id()
    {
        $chunkFile = UploadedFile::fake()->create('chunk_1', 1024, 'application/octet-stream');

        $response = $this->postJson('/api/v1/dcp/upload/invalid-id/chunk', [
            'chunk_number' => 1,
            'chunk_data' => $chunkFile
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_upload_chunk_out_of_order()
    {
        // Initialize upload
        $initResponse = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 15728640, // 15MB
            'chunk_size' => 5242880   // 5MB - results in 3 chunks
        ]);

        $uploadId = $initResponse->json('data.upload_id');
        
        // Try to upload chunk 3 before chunk 1
        $chunkFile = UploadedFile::fake()->create('chunk_3', 5120, 'application/octet-stream');

        $response = $this->postJson("/api/v1/dcp/upload/{$uploadId}/chunk", [
            'chunk_number' => 3,
            'chunk_data' => $chunkFile
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Chunks must be uploaded sequentially'
            ]);
    }

    public function test_upload_expires_correctly()
    {
        // Initialize upload with very short expiry for testing
        $initResponse = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 10485760,
            'chunk_size' => 5242880,
            'expires_in' => 1 // 1 second
        ]);

        $uploadId = $initResponse->json('data.upload_id');

        // Wait for expiry
        sleep(2);

        $chunkFile = UploadedFile::fake()->create('chunk_1', 5120, 'application/octet-stream');

        $response = $this->postJson("/api/v1/dcp/upload/{$uploadId}/chunk", [
            'chunk_number' => 1,
            'chunk_data' => $chunkFile
        ]);

        $response
            ->assertStatus(410)
            ->assertJson([
                'message' => 'Upload session has expired'
            ]);
    }

    public function test_user_can_only_access_their_uploads()
    {
        $otherUser = User::factory()->create();
        
        // Initialize upload as current user
        $initResponse = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 10485760,
            'chunk_size' => 5242880
        ]);

        $uploadId = $initResponse->json('data.upload_id');

        // Switch to other user
        Sanctum::actingAs($otherUser, ['api:access', 'dcp:read', 'dcp:write']);

        $response = $this->getJson("/api/v1/dcp/upload/{$uploadId}/progress");
        $response->assertStatus(404);
    }

    public function test_can_process_dcp_with_callback()
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);

        Storage::put('dcps/test_movie.zip', 'fake dcp content');

        $response = $this->postJson('/api/v1/dcp/process', [
            'movie_id' => $movie->id,
            'operations' => ['analyze', 'validate'],
            'callback_url' => 'https://example.com/webhook',
            'priority' => 'high'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'processing_id',
                    'movie_id',
                    'operations',
                    'job_ids',
                    'callback_url',
                    'priority',
                    'estimated_completion'
                ]
            ]);

        $this->assertEquals('DCP processing started successfully', $response->json('message'));
        $this->assertEquals('high', $response->json('data.priority'));
        $this->assertEquals('https://example.com/webhook', $response->json('data.callback_url'));
    }

    public function test_unauthenticated_user_cannot_access_dcp_endpoints()
    {
        Sanctum::actingAs(null);

        $response = $this->postJson('/api/v1/dcp/analyze', [
            'movie_id' => 1
        ]);
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test.zip',
            'file_size' => 1000
        ]);
        $response->assertStatus(401);
    }

    public function test_user_without_proper_abilities_cannot_process_dcp()
    {
        // Create user with only read access
        Sanctum::actingAs($this->user, ['api:access', 'dcp:read']);

        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'dcp_path' => 'dcps/test_movie.zip'
        ]);

        $response = $this->postJson('/api/v1/dcp/analyze', [
            'movie_id' => $movie->id
        ]);

        $response->assertStatus(403);
    }

    public function test_upload_size_validation()
    {
        $response = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'huge_movie.zip',
            'file_size' => 10737418240000, // 10TB - exceeds typical limits
            'chunk_size' => 5242880
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file_size']);
    }

    public function test_chunk_size_validation()
    {
        $response = $this->postJson('/api/v1/dcp/upload/init', [
            'filename' => 'test_movie.zip',
            'file_size' => 10485760,
            'chunk_size' => 100 // Too small
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['chunk_size']);
    }
}
