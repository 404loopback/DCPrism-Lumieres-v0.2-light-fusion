<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Movie;
use App\Models\Festival;
use App\Models\Version;
use App\Models\Dcp;
use App\Models\Upload;
use App\Jobs\ProcessDcpUploadJob;
use App\Services\BackblazeService;
use App\Services\UnifiedNomenclatureService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class DcpWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test storage
        Storage::fake('local');
        Storage::fake('backblaze');
    }

    /** @test */
    public function complete_dcp_workflow_from_upload_to_validation()
    {
        // Arrange - Create test data
        $admin = User::factory()->create(['role' => 'admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $source = User::factory()->create(['role' => 'source', 'email' => 'source@test.com']);
        $tech = User::factory()->create(['role' => 'tech']);

        $festival = Festival::factory()->create([
            'subdomain' => 'test-fest',
            'name' => 'Test Festival',
            'is_active' => true
        ]);

        // Manager creates movie
        $this->actingAs($manager);
        
        $movie = Movie::factory()->create([
            'title' => 'Test Movie',
            'source_email' => 'source@test.com',
            'status' => 'pending_upload',
            'expected_versions' => ['VF', 'VOST']
        ]);
        
        $movie->festivals()->attach($festival->id);

        // Create version
        $version = Version::factory()->create([
            'movie_id' => $movie->id,
            'type' => 'VF',
            'nomenclature' => 'Test_Movie_VF_2024'
        ]);

        // Act 1 - Source uploads DCP
        $this->actingAs($source);
        
        $uploadFile = UploadedFile::fake()->create('test-dcp.zip', 50000); // 50MB
        
        $response = $this->post(route('source.dcps.upload'), [
            'movie_id' => $movie->id,
            'version_id' => $version->id,
            'dcp_file' => $uploadFile,
            'dcp_type' => 'feature'
        ]);

        // Assert upload was initiated
        $response->assertStatus(200);
        $this->assertDatabaseHas('uploads', [
            'movie_id' => $movie->id,
            'status' => 'uploading'
        ]);

        // Act 2 - Process upload job
        Queue::fake();
        
        $upload = Upload::where('movie_id', $movie->id)->first();
        $job = new ProcessDcpUploadJob(
            Dcp::factory()->create([
                'movie_id' => $movie->id,
                'version_id' => $version->id,
                'status' => 'uploaded'
            ]),
            'temp/test-dcp.zip'
        );

        // Simulate job execution
        $backblazeService = $this->createMock(BackblazeService::class);
        $backblazeService->method('uploadWithProgress')
                        ->willReturn([
                            'success' => true,
                            'upload' => $upload,
                            'movie' => $movie,
                            'b2_response' => ['fileId' => 'test-b2-id']
                        ]);

        // Act 3 - Technical validation
        $this->actingAs($tech);
        
        $dcp = Dcp::where('movie_id', $movie->id)->first();
        
        $response = $this->patch(route('tech.dcps.validate', $dcp), [
            'status' => 'valid',
            'validation_notes' => 'DCP validated successfully',
            'technical_metadata' => [
                'resolution' => '2K',
                'frame_rate' => '24fps',
                'audio_channels' => '5.1'
            ]
        ]);

        // Assert validation was successful
        $response->assertStatus(200);
        $this->assertDatabaseHas('dcps', [
            'id' => $dcp->id,
            'status' => 'valid',
            'is_valid' => true
        ]);

        // Act 4 - Manager reviews validated DCPs
        $this->actingAs($manager);
        
        $response = $this->get(route('manager.movies.show', $movie));
        $response->assertStatus(200);
        $response->assertSee('valid'); // Should show DCP status

        // Act 5 - Download validated DCP (Cinema role)
        $cinema = User::factory()->create(['role' => 'cinema']);
        $this->actingAs($cinema);
        
        $response = $this->get(route('cinema.dcps.download', $dcp));
        $response->assertStatus(200);
    }

    /** @test */
    public function source_can_only_access_their_movies()
    {
        // Arrange
        $source1 = User::factory()->create(['role' => 'source', 'email' => 'source1@test.com']);
        $source2 = User::factory()->create(['role' => 'source', 'email' => 'source2@test.com']);

        $movie1 = Movie::factory()->create(['source_email' => 'source1@test.com']);
        $movie2 = Movie::factory()->create(['source_email' => 'source2@test.com']);

        // Act & Assert - Source 1 can access their movie
        $this->actingAs($source1);
        $response = $this->get(route('source.movies.show', $movie1));
        $response->assertStatus(200);

        // But not source 2's movie
        $response = $this->get(route('source.movies.show', $movie2));
        $response->assertStatus(403);
    }

    /** @test */
    public function tech_can_bulk_validate_dcps()
    {
        // Arrange
        $tech = User::factory()->create(['role' => 'tech']);
        $festival = Festival::factory()->create();

        $movies = Movie::factory()->count(3)->create();
        $dcps = [];

        foreach ($movies as $movie) {
            $movie->festivals()->attach($festival->id);
            $dcps[] = Dcp::factory()->create([
                'movie_id' => $movie->id,
                'status' => 'uploaded'
            ]);
        }

        // Act
        $this->actingAs($tech);
        
        $response = $this->post(route('tech.dcps.bulk-validate'), [
            'dcp_ids' => array_column($dcps, 'id'),
            'status' => 'valid',
            'validation_notes' => 'Bulk validation approved'
        ]);

        // Assert
        $response->assertStatus(200);
        
        foreach ($dcps as $dcp) {
            $this->assertDatabaseHas('dcps', [
                'id' => $dcp->id,
                'status' => 'valid',
                'is_valid' => true
            ]);
        }
    }

    /** @test */
    public function manager_festival_context_is_enforced()
    {
        // Arrange
        $manager = User::factory()->create(['role' => 'manager']);
        
        $festival1 = Festival::factory()->create(['subdomain' => 'fest1']);
        $festival2 = Festival::factory()->create(['subdomain' => 'fest2']);
        
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();
        
        $movie1->festivals()->attach($festival1->id);
        $movie2->festivals()->attach($festival2->id);

        // Act - Set festival context
        $this->actingAs($manager);
        
        $this->withSession(['selected_festival_id' => $festival1->id]);
        
        // Should see movies from selected festival
        $response = $this->get(route('manager.movies.index'));
        $response->assertStatus(200);
        
        // Should be able to access movie from selected festival
        $response = $this->get(route('manager.movies.show', $movie1));
        $response->assertStatus(200);
        
        // Should NOT be able to access movie from different festival
        $response = $this->get(route('manager.movies.show', $movie2));
        $response->assertStatus(403);
    }

    /** @test */
    public function nomenclature_is_generated_automatically()
    {
        // Arrange
        $manager = User::factory()->create(['role' => 'manager']);
        $festival = Festival::factory()->create();
        
        $nomenclatureService = app(UnifiedNomenclatureService::class);

        // Act
        $this->actingAs($manager);
        
        $response = $this->post(route('manager.movies.store'), [
            'title' => 'Avatar: The Way of Water',
            'source_email' => 'disney@test.com',
            'year' => 2022,
            'format' => 'DCP',
            'festival_id' => $festival->id,
            'expected_versions' => ['VF', 'VOST', 'VO']
        ]);

        // Assert
        $response->assertStatus(201);
        
        $movie = Movie::where('title', 'Avatar: The Way of Water')->first();
        $this->assertNotNull($movie);
        
        // Check that versions were created with nomenclature
        $versions = $movie->versions;
        $this->assertCount(3, $versions);
        
        foreach ($versions as $version) {
            $this->assertNotEmpty($version->nomenclature);
            $this->assertStringContainsString('Avatar', $version->nomenclature);
            $this->assertStringContainsString($version->type, $version->nomenclature);
        }
    }

    /** @test */
    public function upload_progress_is_tracked()
    {
        // Arrange
        $source = User::factory()->create(['role' => 'source', 'email' => 'source@test.com']);
        $movie = Movie::factory()->create(['source_email' => 'source@test.com']);
        $version = Version::factory()->create(['movie_id' => $movie->id]);

        // Act
        $this->actingAs($source);
        
        $uploadFile = UploadedFile::fake()->create('large-dcp.zip', 200000); // 200MB
        
        $response = $this->post(route('source.dcps.upload'), [
            'movie_id' => $movie->id,
            'version_id' => $version->id,
            'dcp_file' => $uploadFile
        ]);

        // Assert upload tracking was initiated
        $response->assertStatus(200);
        
        $upload = Upload::where('movie_id', $movie->id)->first();
        $this->assertNotNull($upload);
        $this->assertEquals('uploading', $upload->status);
        $this->assertNotNull($upload->metadata);
        $this->assertArrayHasKey('chunks_total', $upload->metadata);
        $this->assertArrayHasKey('chunk_size', $upload->metadata);
    }

    /** @test */
    public function failed_uploads_are_handled_gracefully()
    {
        // Arrange
        $source = User::factory()->create(['role' => 'source', 'email' => 'source@test.com']);
        $movie = Movie::factory()->create(['source_email' => 'source@test.com']);
        $version = Version::factory()->create(['movie_id' => $movie->id]);

        // Simulate upload failure
        Storage::shouldReceive('disk')->andThrow(new \Exception('Storage failure'));

        // Act
        $this->actingAs($source);
        
        $uploadFile = UploadedFile::fake()->create('test-dcp.zip', 5000);
        
        $response = $this->post(route('source.dcps.upload'), [
            'movie_id' => $movie->id,
            'version_id' => $version->id,
            'dcp_file' => $uploadFile
        ]);

        // Assert error handling
        $response->assertStatus(500);
        
        // Check upload was marked as failed
        $upload = Upload::where('movie_id', $movie->id)->first();
        if ($upload) {
            $this->assertEquals('failed', $upload->status);
            $this->assertNotNull($upload->error_message);
        }
    }

    /** @test */
    public function audit_trail_is_created_for_all_actions()
    {
        // Arrange
        $this->app['config']->set('activitylog.enabled', true);
        
        $manager = User::factory()->create(['role' => 'manager']);
        $tech = User::factory()->create(['role' => 'tech']);
        
        // Act - Manager creates movie
        $this->actingAs($manager);
        
        $movie = Movie::factory()->create([
            'title' => 'Audit Test Movie'
        ]);

        // Tech validates DCP
        $this->actingAs($tech);
        
        $dcp = Dcp::factory()->create([
            'movie_id' => $movie->id,
            'status' => 'uploaded'
        ]);
        
        $dcp->update([
            'status' => 'valid',
            'is_valid' => true,
            'validation_notes' => 'Approved by tech'
        ]);

        // Assert audit logs were created
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Movie::class,
            'subject_id' => $movie->id,
            'causer_id' => $manager->id
        ]);
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Dcp::class,
            'subject_id' => $dcp->id,
            'causer_id' => $tech->id
        ]);
    }
}
