<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['api:access', 'jobs:read', 'jobs:write']);
    }

    public function test_can_list_jobs()
    {
        Job::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/jobs');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'type', 'status', 'payload', 'progress',
                        'created_at', 'started_at', 'finished_at',
                        'estimated_duration', 'actual_duration'
                    ]
                ],
                'meta' => [
                    'current_page', 'per_page', 'total', 'last_page'
                ]
            ]);

        $this->assertCount(10, $response->json('data')); // Default pagination
        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_can_show_job()
    {
        $job = Job::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'type', 'status', 'payload', 'progress',
                    'result', 'error_message', 'metadata',
                    'created_at', 'started_at', 'finished_at',
                    'estimated_duration', 'actual_duration'
                ]
            ]);

        $this->assertEquals($job->id, $response->json('data.id'));
        $this->assertEquals($job->type, $response->json('data.type'));
    }

    public function test_cannot_show_job_that_does_not_exist()
    {
        $response = $this->getJson('/api/v1/jobs/999999');

        $response->assertStatus(404);
    }

    public function test_can_filter_jobs_by_status()
    {
        Job::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Job::factory()->create(['user_id' => $this->user->id, 'status' => 'running']);
        Job::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->getJson('/api/v1/jobs?status=pending');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $job) {
            $this->assertEquals('pending', $job['status']);
        }
    }

    public function test_can_filter_jobs_by_type()
    {
        Job::factory()->create(['user_id' => $this->user->id, 'type' => 'dcp_validation']);
        Job::factory()->create(['user_id' => $this->user->id, 'type' => 'metadata_extraction']);
        Job::factory()->create(['user_id' => $this->user->id, 'type' => 'dcp_validation']);

        $response = $this->getJson('/api/v1/jobs?type=dcp_validation');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $job) {
            $this->assertEquals('dcp_validation', $job['type']);
        }
    }

    public function test_can_filter_jobs_by_date_range()
    {
        Job::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => '2024-01-15 10:00:00'
        ]);
        Job::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => '2024-06-15 10:00:00'
        ]);

        $response = $this->getJson('/api/v1/jobs?created_from=2024-01-01&created_to=2024-02-01');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $job = $response->json('data')[0];
        $this->assertStringContainsString('2024-01-15', $job['created_at']);
    }

    public function test_can_sort_jobs()
    {
        Job::factory()->create(['user_id' => $this->user->id, 'created_at' => '2024-01-01']);
        Job::factory()->create(['user_id' => $this->user->id, 'created_at' => '2024-03-01']);
        Job::factory()->create(['user_id' => $this->user->id, 'created_at' => '2024-02-01']);

        $response = $this->getJson('/api/v1/jobs?sort=created_at&direction=asc');

        $jobs = $response->json('data');
        $this->assertStringContainsString('2024-01-01', $jobs[0]['created_at']);
        $this->assertStringContainsString('2024-02-01', $jobs[1]['created_at']);
        $this->assertStringContainsString('2024-03-01', $jobs[2]['created_at']);
    }

    public function test_can_retry_failed_job()
    {
        $job = Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'failed',
            'error_message' => 'Some error occurred'
        ]);

        $response = $this->postJson("/api/v1/jobs/{$job->id}/retry");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'new_job_id',
                    'original_job_id',
                    'status'
                ]
            ]);

        $this->assertEquals('Job retry initiated successfully', $response->json('message'));
        $this->assertEquals('pending', $response->json('data.status'));
    }

    public function test_cannot_retry_job_that_is_not_failed()
    {
        $job = Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $response = $this->postJson("/api/v1/jobs/{$job->id}/retry");

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Only failed jobs can be retried'
            ]);
    }

    public function test_can_cancel_pending_job()
    {
        $job = Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->postJson("/api/v1/jobs/{$job->id}/cancel");

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Job cancelled successfully'
            ]);

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_cannot_cancel_completed_job()
    {
        $job = Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $response = $this->postJson("/api/v1/jobs/{$job->id}/cancel");

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot cancel a job that is already completed or failed'
            ]);
    }

    public function test_can_get_job_logs()
    {
        $job = Job::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}/logs");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'job_id',
                    'logs' => [
                        '*' => [
                            'level', 'message', 'timestamp', 'context'
                        ]
                    ],
                    'total_entries',
                    'last_updated'
                ]
            ]);

        $this->assertEquals($job->id, $response->json('data.job_id'));
        $this->assertIsArray($response->json('data.logs'));
    }

    public function test_can_get_job_logs_with_level_filter()
    {
        $job = Job::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}/logs?level=error");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'job_id',
                    'logs',
                    'filter_applied' => [
                        'level'
                    ]
                ]
            ]);

        // All logs should be error level if filter is applied
        foreach ($response->json('data.logs') as $log) {
            $this->assertEquals('error', $log['level']);
        }
    }

    public function test_can_get_job_statistics()
    {
        // Create various jobs with different statuses and types
        Job::factory()->count(5)->create(['user_id' => $this->user->id, 'status' => 'completed']);
        Job::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => 'failed']);
        Job::factory()->count(2)->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->getJson('/api/v1/jobs/statistics');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_jobs',
                    'status_distribution',
                    'type_distribution',
                    'success_rate',
                    'average_duration',
                    'jobs_by_date',
                    'performance_metrics'
                ]
            ]);

        $this->assertEquals(10, $response->json('data.total_jobs'));
        $this->assertEquals(5, $response->json('data.status_distribution.completed'));
        $this->assertEquals(3, $response->json('data.status_distribution.failed'));
        $this->assertEquals(2, $response->json('data.status_distribution.pending'));
    }

    public function test_can_get_performance_metrics()
    {
        // Create completed jobs with different durations
        Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'started_at' => '2024-01-01 10:00:00',
            'finished_at' => '2024-01-01 10:05:00',
            'type' => 'dcp_validation'
        ]);
        Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'started_at' => '2024-01-01 11:00:00',
            'finished_at' => '2024-01-01 11:10:00',
            'type' => 'dcp_validation'
        ]);

        $response = $this->getJson('/api/v1/jobs/performance');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'average_execution_time',
                    'median_execution_time',
                    'fastest_job',
                    'slowest_job',
                    'throughput_per_hour',
                    'performance_by_type',
                    'queue_efficiency'
                ]
            ]);

        $this->assertIsNumeric($response->json('data.average_execution_time'));
        $this->assertIsArray($response->json('data.performance_by_type'));
    }

    public function test_pagination_works_correctly()
    {
        Job::factory()->count(25)->create(['user_id' => $this->user->id]);

        // Test first page
        $response = $this->getJson('/api/v1/jobs?page=1&per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.current_page'));
        $this->assertEquals(25, $response->json('meta.total'));

        // Test second page
        $response = $this->getJson('/api/v1/jobs?page=2&per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(2, $response->json('meta.current_page'));
    }

    public function test_user_can_only_access_their_jobs()
    {
        $otherUser = User::factory()->create();
        $userJob = Job::factory()->create(['user_id' => $this->user->id]);
        $otherUserJob = Job::factory()->create(['user_id' => $otherUser->id]);

        // List jobs - should only see own jobs
        $response = $this->getJson('/api/v1/jobs');
        $jobs = $response->json('data');
        $jobIds = array_column($jobs, 'id');
        
        $this->assertContains($userJob->id, $jobIds);
        $this->assertNotContains($otherUserJob->id, $jobIds);

        // Try to access other user's job
        $response = $this->getJson("/api/v1/jobs/{$otherUserJob->id}");
        $response->assertStatus(404);
    }

    public function test_can_bulk_cancel_jobs()
    {
        $pendingJobs = Job::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);
        
        $runningJob = Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'running'
        ]);

        $jobIds = $pendingJobs->pluck('id')->toArray();
        $jobIds[] = $runningJob->id;

        $response = $this->postJson('/api/v1/jobs/bulk-cancel', [
            'job_ids' => $jobIds
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'cancelled_count',
                    'skipped_count',
                    'cancelled_jobs',
                    'skipped_jobs'
                ]
            ]);

        $this->assertEquals(4, $response->json('data.cancelled_count'));
        $this->assertEquals(0, $response->json('data.skipped_count'));

        // Verify jobs are cancelled in database
        foreach ($jobIds as $jobId) {
            $this->assertDatabaseHas('jobs', [
                'id' => $jobId,
                'status' => 'cancelled'
            ]);
        }
    }

    public function test_can_bulk_retry_failed_jobs()
    {
        $failedJobs = Job::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'failed'
        ]);
        
        $completedJob = Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $jobIds = $failedJobs->pluck('id')->toArray();
        $jobIds[] = $completedJob->id;

        $response = $this->postJson('/api/v1/jobs/bulk-retry', [
            'job_ids' => $jobIds
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'retried_count',
                    'skipped_count',
                    'new_job_ids',
                    'skipped_jobs'
                ]
            ]);

        $this->assertEquals(3, $response->json('data.retried_count'));
        $this->assertEquals(1, $response->json('data.skipped_count'));
        $this->assertCount(3, $response->json('data.new_job_ids'));
    }

    public function test_unauthenticated_user_cannot_access_jobs()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/v1/jobs');
        $response->assertStatus(401);
    }

    public function test_user_without_proper_abilities_cannot_cancel_jobs()
    {
        // Create user with only read access
        Sanctum::actingAs($this->user, ['api:access', 'jobs:read']);

        $job = Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->postJson("/api/v1/jobs/{$job->id}/cancel");
        $response->assertStatus(403);
    }

    public function test_can_search_jobs_by_payload()
    {
        Job::factory()->create([
            'user_id' => $this->user->id,
            'payload' => json_encode(['movie_title' => 'The Great Adventure'])
        ]);
        Job::factory()->create([
            'user_id' => $this->user->id,
            'payload' => json_encode(['movie_title' => 'Comedy Show'])
        ]);

        $response = $this->getJson('/api/v1/jobs?search=Adventure');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $job = $response->json('data')[0];
        $payload = json_decode($job['payload'], true);
        $this->assertStringContainsString('Adventure', $payload['movie_title']);
    }

    public function test_job_progress_updates_correctly()
    {
        $job = Job::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'running',
            'progress' => 0
        ]);

        // Simulate progress update (this would typically be done by a job processor)
        $job->update(['progress' => 50]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $job->id,
                    'progress' => 50
                ]
            ]);
    }
}
