<?php

namespace Tests\Unit\Services;

use App\Services\BackblazeService;
use App\Services\B2NativeService;
use App\Models\Movie;
use App\Models\Festival;
use App\Models\Upload;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;

class BackblazeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BackblazeService $backblazeService;
    private $mockB2Native;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockB2Native = Mockery::mock(B2NativeService::class);
        $this->backblazeService = new BackblazeService($this->mockB2Native);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_upload_small_file_successfully()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create();
        
        $file = UploadedFile::fake()->create('test-movie.zip', 5000); // 5MB
        
        // Mock B2 responses for simple upload
        $this->mockB2Native
            ->shouldReceive('getAuthData')
            ->andReturn([
                'authToken' => 'test-token',
                'apiUrl' => 'https://api.backblazeb2.com',
                'downloadUrl' => 'https://f001.backblazeb2.com'
            ]);

        // Act
        $result = $this->backblazeService->uploadWithProgress($file, $festival, $movie);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Upload::class, $result['upload']);
        $this->assertEquals('completed', $result['upload']->status);
        
        // Check movie was updated
        $movie->refresh();
        $this->assertEquals('upload_ok', $movie->status);
        $this->assertNotNull($movie->backblaze_file_id);
    }

    /** @test */
    public function it_can_upload_large_file_with_chunks()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create();
        
        $file = UploadedFile::fake()->create('large-movie.zip', 150000); // 150MB
        
        // Mock B2 responses for large file upload
        $this->mockB2Native
            ->shouldReceive('startLargeFile')
            ->once()
            ->with($festival->subdomain, Mockery::any(), 'application/zip')
            ->andReturn(['fileId' => 'test-large-file-id']);

        $this->mockB2Native
            ->shouldReceive('getUploadPartUrl')
            ->times(15) // ~15 chunks for 150MB at 10MB each
            ->with('test-large-file-id')
            ->andReturn([
                'uploadUrl' => 'https://pod-001-1234-56.backblaze.com/b2api/v1/b2_upload_part/test',
                'authorizationToken' => 'test-upload-token'
            ]);

        $this->mockB2Native
            ->shouldReceive('finishLargeFile')
            ->once()
            ->with('test-large-file-id', Mockery::any())
            ->andReturn([
                'fileId' => 'test-final-file-id',
                'fileName' => 'large-movie.zip',
                'contentLength' => 150000000
            ]);

        // Act
        $progressCallbacks = [];
        $progressCallback = function($progress, $chunk, $total) use (&$progressCallbacks) {
            $progressCallbacks[] = ['progress' => $progress, 'chunk' => $chunk, 'total' => $total];
        };

        $result = $this->backblazeService->uploadWithProgress($file, $festival, $movie, $progressCallback);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($progressCallbacks);
        $this->assertEquals('completed', $result['upload']->status);
        $this->assertEquals('test-final-file-id', $result['upload']->b2_file_id);
    }

    /** @test */
    public function it_handles_upload_failure_gracefully()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create();
        
        $file = UploadedFile::fake()->create('test-movie.zip', 5000);
        
        // Mock B2 failure
        $this->mockB2Native
            ->shouldReceive('getAuthData')
            ->andThrow(new \Exception('B2 connection failed'));

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('B2 connection failed');
        
        $this->backblazeService->uploadWithProgress($file, $festival, $movie);

        // Verify upload record was marked as failed
        $failedUpload = Upload::where('movie_id', $movie->id)->first();
        $this->assertNotNull($failedUpload);
        $this->assertEquals('failed', $failedUpload->status);
    }

    /** @test */
    public function it_can_download_file_from_b2()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create([
            'backblaze_file_id' => 'test-file-id',
            'file_path' => $festival->subdomain . '/test-movie.zip'
        ]);

        $this->mockB2Native
            ->shouldReceive('getAuthData')
            ->once()
            ->andReturn([
                'authToken' => 'test-token',
                'downloadUrl' => 'https://f001.backblazeb2.com'
            ]);

        // Act
        $response = $this->backblazeService->download($movie);

        // Assert
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
    }

    /** @test */
    public function it_can_delete_file_from_b2()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create([
            'backblaze_file_id' => 'test-file-id',
            'file_path' => $festival->subdomain . '/test-movie.zip'
        ]);

        $this->mockB2Native
            ->shouldReceive('getAuthData')
            ->once()
            ->andReturn([
                'authToken' => 'test-token',
                'apiUrl' => 'https://api.backblazeb2.com'
            ]);

        // Mock successful deletion
        // Note: This would need actual HTTP client mocking in real implementation

        // Act
        $result = $this->backblazeService->delete($movie);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_festival_storage_stats()
    {
        // Arrange
        $festival = Festival::factory()->create();
        
        // Create some movies with file data
        Movie::factory()->count(3)->create([
            'file_size' => 1000000 // 1MB each
        ])->each(function ($movie) use ($festival) {
            $movie->festivals()->attach($festival);
            $movie->update(['backblaze_file_id' => 'test-id-' . $movie->id]);
        });

        // Create some uploads in last 24h
        Upload::factory()->count(2)->create([
            'festival_id' => $festival->id,
            'created_at' => now()->subHours(12)
        ]);

        // Act
        $stats = $this->backblazeService->getFestivalStorageStats($festival);

        // Assert
        $this->assertEquals(3, $stats['total_files']);
        $this->assertEquals(3000000, $stats['total_size_bytes']);
        $this->assertEquals('2.86 MB', $stats['total_size_formatted']);
        $this->assertEquals(2, $stats['uploads_last_24h']);
        $this->assertEquals($festival->subdomain, $stats['bucket_name']);
        $this->assertIsFloat($stats['storage_efficiency']);
    }

    /** @test */
    public function it_can_cleanup_failed_uploads()
    {
        // Arrange
        $oldFailedUpload = Upload::factory()->create([
            'status' => 'failed',
            'created_at' => now()->subDays(10),
            'metadata' => ['b2_file_id' => 'test-cleanup-id']
        ]);

        $recentFailedUpload = Upload::factory()->create([
            'status' => 'failed',
            'created_at' => now()->subHours(12)
        ]);

        $this->mockB2Native
            ->shouldReceive('cancelLargeFile')
            ->once()
            ->with('test-cleanup-id');

        // Act
        $cleanedCount = $this->backblazeService->cleanupFailedUploads(7);

        // Assert
        $this->assertEquals(1, $cleanedCount);
        $this->assertDatabaseMissing('uploads', ['id' => $oldFailedUpload->id]);
        $this->assertDatabaseHas('uploads', ['id' => $recentFailedUpload->id]);
    }

    /** @test */
    public function it_generates_proper_file_names()
    {
        // Arrange
        $festival = Festival::factory()->create(['subdomain' => 'cannes']);
        $movie = Movie::factory()->create(['title' => 'Parasite', 'id' => 123]);
        $file = UploadedFile::fake()->create('original.zip');

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->backblazeService);
        $method = $reflection->getMethod('generateFileName');
        $method->setAccessible(true);

        // Act
        $fileName = $method->invokeArgs($this->backblazeService, [$festival, $movie, $file]);

        // Assert
        $this->assertStringContainsString('parasite', strtolower($fileName));
        $this->assertStringContainsString('123', $fileName);
        $this->assertStringEndsWith('.zip', $fileName);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}/', $fileName);
    }
}
