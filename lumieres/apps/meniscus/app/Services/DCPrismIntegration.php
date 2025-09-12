<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DCPrismIntegration
{
    private Client $apiClient;
    private array $config;
    private string $dcprismApiUrl;
    private string $dcprismApiKey;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'timeout' => 30,
            'retry_attempts' => 3,
            'cache_ttl' => 300, // 5 minutes
        ], $config);

        $this->dcprismApiUrl = config('services.dcprism.api_url');
        $this->dcprismApiKey = config('services.dcprism.api_key');

        $this->apiClient = new Client([
            'base_uri' => rtrim($this->dcprismApiUrl, '/') . '/',
            'timeout' => $this->config['timeout'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->dcprismApiKey,
                'Content-Type' => 'application/json',
                'User-Agent' => 'DCParty/1.0',
            ],
        ]);
    }

    /**
     * Notify DCPrism about job status changes
     */
    public function notifyJobStatusChange(string $jobId, string $status, array $metadata = []): bool
    {
        try {
            $payload = [
                'job_id' => $jobId,
                'status' => $status,
                'updated_by' => 'dcparty',
                'metadata' => $metadata,
                'timestamp' => now()->toISOString(),
            ];

            $response = $this->apiClient->post('api/jobs/status-update', [
                'json' => $payload,
                'timeout' => $this->config['timeout'],
            ]);

            if ($response->getStatusCode() === 200) {
                Log::info('DCPrism job status updated successfully', [
                    'job_id' => $jobId,
                    'status' => $status
                ]);
                return true;
            }

            return false;

        } catch (RequestException $e) {
            Log::error('Failed to update DCPrism job status', [
                'job_id' => $jobId,
                'status' => $status,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return false;
        }
    }

    /**
     * Retrieve job details from DCPrism
     */
    public function getJobDetails(string $jobId): ?array
    {
        $cacheKey = "dcprism:job:{$jobId}";
        
        return Cache::remember($cacheKey, $this->config['cache_ttl'], function () use ($jobId) {
            try {
                $response = $this->apiClient->get("api/jobs/{$jobId}");
                
                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getBody()->getContents(), true);
                    return $data['data'] ?? null;
                }
                
                return null;

            } catch (RequestException $e) {
                Log::error('Failed to retrieve job details from DCPrism', [
                    'job_id' => $jobId,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Get file metadata from DCPrism
     */
    public function getFileMetadata(string $fileId): ?array
    {
        $cacheKey = "dcprism:file:{$fileId}";
        
        return Cache::remember($cacheKey, $this->config['cache_ttl'], function () use ($fileId) {
            try {
                $response = $this->apiClient->get("api/files/{$fileId}");
                
                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getBody()->getContents(), true);
                    return $data['data'] ?? null;
                }
                
                return null;

            } catch (RequestException $e) {
                Log::error('Failed to retrieve file metadata from DCPrism', [
                    'file_id' => $fileId,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Request signed URL for file download from DCPrism
     */
    public function getSignedDownloadUrl(string $fileId, int $expirationHours = 2): ?string
    {
        try {
            $payload = [
                'file_id' => $fileId,
                'expiration_hours' => $expirationHours,
                'requester' => 'dcparty',
                'purpose' => 'processing',
            ];

            $response = $this->apiClient->post('api/files/signed-url', [
                'json' => $payload,
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                return $data['signed_url'] ?? null;
            }

            return null;

        } catch (RequestException $e) {
            Log::error('Failed to get signed download URL from DCPrism', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Sync job data with shared database
     */
    public function syncJobData(string $jobId, array $jobData): bool
    {
        try {
            DB::beginTransaction();

            // Update shared job_queue table
            DB::table('job_queue')
                ->updateOrInsert(
                    ['job_id' => $jobId],
                    [
                        'status' => $jobData['status'],
                        'progress' => $jobData['progress'] ?? 0,
                        'worker_id' => $jobData['worker_id'] ?? null,
                        'started_at' => $jobData['started_at'] ?? null,
                        'completed_at' => $jobData['completed_at'] ?? null,
                        'error_message' => $jobData['error_message'] ?? null,
                        'updated_by' => 'dcparty',
                        'updated_at' => now(),
                    ]
                );

            // Update processing metadata table
            if (isset($jobData['processing_metadata'])) {
                DB::table('job_processing_metadata')
                    ->updateOrInsert(
                        ['job_id' => $jobId],
                        array_merge($jobData['processing_metadata'], [
                            'updated_at' => now(),
                        ])
                    );
            }

            DB::commit();

            Log::info('Job data synced successfully', [
                'job_id' => $jobId,
                'status' => $jobData['status']
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to sync job data', [
                'job_id' => $jobId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get pending jobs from shared database
     */
    public function getPendingJobs(int $limit = 10): array
    {
        try {
            $jobs = DB::table('job_queue')
                ->select([
                    'job_id',
                    'file_id',
                    'user_id',
                    'priority',
                    'created_at',
                    'job_parameters'
                ])
                ->where('status', 'pending')
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'asc')
                ->limit($limit)
                ->get();

            return $jobs->map(function ($job) {
                return [
                    'job_id' => $job->job_id,
                    'file_id' => $job->file_id,
                    'user_id' => $job->user_id,
                    'priority' => $job->priority,
                    'created_at' => $job->created_at,
                    'parameters' => json_decode($job->job_parameters, true) ?? [],
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve pending jobs', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Update user session activity
     */
    public function updateUserActivity(int $userId, string $activity): bool
    {
        try {
            DB::table('user_sessions')
                ->updateOrInsert(
                    ['user_id' => $userId, 'platform' => 'dcparty'],
                    [
                        'last_activity' => now(),
                        'activity_type' => $activity,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'updated_at' => now(),
                    ]
                );

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update user activity', [
                'user_id' => $userId,
                'activity' => $activity,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get user session data from shared database
     */
    public function getUserSession(int $userId): ?array
    {
        try {
            $session = DB::table('user_sessions')
                ->where('user_id', $userId)
                ->where('platform', 'dcprism')
                ->where('expires_at', '>', now())
                ->first();

            if ($session) {
                return [
                    'user_id' => $session->user_id,
                    'session_id' => $session->session_id,
                    'last_activity' => $session->last_activity,
                    'ip_address' => $session->ip_address,
                    'expires_at' => $session->expires_at,
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user session', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Register webhook endpoint with DCPrism
     */
    public function registerWebhook(string $eventType, string $callbackUrl): bool
    {
        try {
            $payload = [
                'event_type' => $eventType,
                'callback_url' => $callbackUrl,
                'source' => 'dcparty',
                'active' => true,
            ];

            $response = $this->apiClient->post('api/webhooks', [
                'json' => $payload,
            ]);

            if ($response->getStatusCode() === 201) {
                Log::info('Webhook registered successfully with DCPrism', [
                    'event_type' => $eventType,
                    'callback_url' => $callbackUrl
                ]);
                return true;
            }

            return false;

        } catch (RequestException $e) {
            Log::error('Failed to register webhook with DCPrism', [
                'event_type' => $eventType,
                'callback_url' => $callbackUrl,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Test connection to DCPrism API
     */
    public function testConnection(): array
    {
        try {
            $response = $this->apiClient->get('api/health');
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                
                return [
                    'status' => 'connected',
                    'dcprism_version' => $data['version'] ?? 'unknown',
                    'response_time' => $data['response_time'] ?? null,
                    'timestamp' => now()->toISOString(),
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Unexpected response code: ' . $response->getStatusCode(),
                'timestamp' => now()->toISOString(),
            ];

        } catch (RequestException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ];
        }
    }

    /**
     * Get integration health metrics
     */
    public function getHealthMetrics(): array
    {
        $cacheKey = 'dcprism:health_metrics';
        
        return Cache::remember($cacheKey, 60, function () {
            try {
                // Test API connection
                $apiHealth = $this->testConnection();
                
                // Check database connectivity
                $dbHealth = $this->testDatabaseConnection();
                
                // Check recent sync activity
                $recentSyncs = $this->getRecentSyncActivity();
                
                return [
                    'api_connection' => $apiHealth,
                    'database_connection' => $dbHealth,
                    'recent_activity' => $recentSyncs,
                    'overall_status' => $this->calculateOverallHealth($apiHealth, $dbHealth),
                    'last_check' => now()->toISOString(),
                ];

            } catch (\Exception $e) {
                return [
                    'api_connection' => ['status' => 'error', 'message' => $e->getMessage()],
                    'database_connection' => ['status' => 'error', 'message' => $e->getMessage()],
                    'overall_status' => 'unhealthy',
                    'last_check' => now()->toISOString(),
                ];
            }
        });
    }

    /**
     * Test shared database connection
     */
    private function testDatabaseConnection(): array
    {
        try {
            $count = DB::table('job_queue')->count();
            
            return [
                'status' => 'connected',
                'job_count' => $count,
                'timestamp' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ];
        }
    }

    /**
     * Get recent sync activity for health monitoring
     */
    private function getRecentSyncActivity(): array
    {
        try {
            $recentJobs = DB::table('job_queue')
                ->where('updated_by', 'dcparty')
                ->where('updated_at', '>=', now()->subHours(24))
                ->count();

            return [
                'jobs_synced_24h' => $recentJobs,
                'last_activity' => DB::table('job_queue')
                    ->where('updated_by', 'dcparty')
                    ->max('updated_at'),
            ];

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate overall integration health
     */
    private function calculateOverallHealth(array $apiHealth, array $dbHealth): string
    {
        if ($apiHealth['status'] === 'connected' && $dbHealth['status'] === 'connected') {
            return 'healthy';
        } elseif ($apiHealth['status'] === 'connected' || $dbHealth['status'] === 'connected') {
            return 'degraded';
        }
        
        return 'unhealthy';
    }
}
