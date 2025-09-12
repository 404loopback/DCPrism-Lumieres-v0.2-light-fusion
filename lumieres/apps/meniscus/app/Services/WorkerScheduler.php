<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class WorkerScheduler
{
    private CloudProviderFactory $providerFactory;
    private array $config;

    // Default deployment time (20:00 UTC)
    private const DEFAULT_DEPLOYMENT_HOUR = 20;
    private const DEFAULT_DEPLOYMENT_MINUTE = 0;

    public function __construct(CloudProviderFactory $providerFactory, array $config = [])
    {
        $this->providerFactory = $providerFactory;
        $this->config = array_merge([
            'deployment_hour' => self::DEFAULT_DEPLOYMENT_HOUR,
            'deployment_minute' => self::DEFAULT_DEPLOYMENT_MINUTE,
            'max_workers_per_deployment' => 10,
            'jobs_per_worker' => 3,
            'default_instance_type' => 'vc2-4c-8gb',
            'preferred_providers' => ['vultr', 'aws', 'gcp', 'azure'],
        ], $config);
    }

    /**
     * Check if it's time to deploy workers
     */
    public function shouldDeployWorkers(): bool
    {
        $now = Carbon::now('UTC');
        $deploymentTime = Carbon::today('UTC')
            ->setHour($this->config['deployment_hour'])
            ->setMinute($this->config['deployment_minute']);

        // Check if we're within 5 minutes of deployment time
        return $now->between(
            $deploymentTime->copy()->subMinutes(2),
            $deploymentTime->copy()->addMinutes(3)
        );
    }

    /**
     * Analyze the job queue and determine worker requirements
     */
    public function analyzeQueue(): array
    {
        try {
            // Get queue size from Redis
            $queueSize = Redis::llen('queues:dcp-processing');
            $queueData = Redis::lrange('queues:dcp-processing', 0, -1);
            
            $totalJobs = $queueSize;
            $estimatedProcessingTime = 0;
            $complexityScores = [];
            
            // Analyze individual jobs
            foreach ($queueData as $jobJson) {
                $job = json_decode($jobJson, true);
                
                if (!$job) continue;
                
                // Calculate complexity based on job parameters
                $complexity = $this->calculateJobComplexity($job);
                $complexityScores[] = $complexity;
                
                // Estimate processing time (in hours)
                $estimatedProcessingTime += $this->estimateJobTime($job, $complexity);
            }

            $averageComplexity = !empty($complexityScores) ? 
                array_sum($complexityScores) / count($complexityScores) : 1.0;

            return [
                'total_jobs' => $totalJobs,
                'estimated_total_time' => $estimatedProcessingTime,
                'average_complexity' => $averageComplexity,
                'recommended_workers' => $this->calculateOptimalWorkerCount($totalJobs, $estimatedProcessingTime),
                'recommended_instance_type' => $this->selectInstanceType($averageComplexity),
                'analysis_timestamp' => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to analyze queue', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_jobs' => 0,
                'estimated_total_time' => 0,
                'average_complexity' => 1.0,
                'recommended_workers' => 0,
                'recommended_instance_type' => $this->config['default_instance_type'],
                'analysis_timestamp' => time(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Deploy workers based on queue analysis
     */
    public function deployScheduledWorkers(): array
    {
        $analysis = $this->analyzeQueue();
        
        if ($analysis['recommended_workers'] <= 0) {
            Log::info('No workers needed based on queue analysis', $analysis);
            return [
                'deployed' => false,
                'reason' => 'No jobs in queue',
                'analysis' => $analysis
            ];
        }

        $deploymentResults = [];
        $totalDeployed = 0;
        $maxWorkers = min($analysis['recommended_workers'], $this->config['max_workers_per_deployment']);

        // Try each provider in order of preference
        foreach ($this->config['preferred_providers'] as $providerName) {
            if ($totalDeployed >= $maxWorkers) {
                break;
            }

            try {
                $provider = $this->providerFactory->create($providerName);
                
                // Test provider connectivity
                if (!$provider->testConnection()) {
                    Log::warning("Provider {$providerName} not available, trying next");
                    continue;
                }

                $remainingWorkers = $maxWorkers - $totalDeployed;
                $workersToDeployOnProvider = min($remainingWorkers, 5); // Max 5 per provider

                $deploymentConfig = [
                    'worker_count' => $workersToDeployOnProvider,
                    'plan' => $analysis['recommended_instance_type'],
                    'environment' => config('app.env'),
                    'deployment_id' => 'scheduled-' . time(),
                    'project_name' => 'dcparty',
                    'domain_name' => config('dcparty.domain_name', 'dcparty.local'),
                    'estimated_hours' => ceil($analysis['estimated_total_time'] / $maxWorkers),
                    'master_ips' => $this->getMasterIPs(),
                ];

                $deployed = $provider->deployWorkers($deploymentConfig);
                
                if (!empty($deployed)) {
                    $deploymentResults[] = [
                        'provider' => $providerName,
                        'workers' => $deployed,
                        'count' => count($deployed),
                    ];
                    $totalDeployed += count($deployed);
                    
                    Log::info("Deployed {count} workers on {provider}", [
                        'count' => count($deployed),
                        'provider' => $providerName,
                        'analysis' => $analysis
                    ]);
                }

            } catch (\Exception $e) {
                Log::error("Failed to deploy workers on {$providerName}", [
                    'error' => $e->getMessage(),
                    'provider' => $providerName
                ]);
                continue;
            }
        }

        // Store deployment record
        $this->recordDeployment($deploymentResults, $analysis);

        return [
            'deployed' => $totalDeployed > 0,
            'total_deployed' => $totalDeployed,
            'deployments' => $deploymentResults,
            'analysis' => $analysis,
            'timestamp' => time(),
        ];
    }

    /**
     * Calculate job complexity score (1-5)
     */
    private function calculateJobComplexity(array $job): float
    {
        $complexity = 1.0;
        
        // Resolution factor
        if (isset($job['resolution'])) {
            if (str_contains(strtoupper($job['resolution']), '4K')) {
                $complexity += 2.0;
            } elseif (str_contains(strtoupper($job['resolution']), '2K')) {
                $complexity += 1.0;
            }
        }
        
        // Duration factor
        if (isset($job['duration_minutes'])) {
            if ($job['duration_minutes'] > 120) {
                $complexity += 1.5;
            } elseif ($job['duration_minutes'] > 60) {
                $complexity += 0.5;
            }
        }
        
        // File size factor
        if (isset($job['file_size_gb'])) {
            if ($job['file_size_gb'] > 100) {
                $complexity += 1.0;
            } elseif ($job['file_size_gb'] > 50) {
                $complexity += 0.5;
            }
        }

        return min($complexity, 5.0); // Cap at 5.0
    }

    /**
     * Estimate job processing time in hours
     */
    private function estimateJobTime(array $job, float $complexity): float
    {
        // Base processing time (in hours)
        $baseTime = 0.5;
        
        // Apply complexity multiplier
        $estimatedTime = $baseTime * $complexity;
        
        // Additional factors
        if (isset($job['duration_minutes'])) {
            // Rough estimate: 1 minute of source = 2 minutes of processing
            $estimatedTime += ($job['duration_minutes'] / 60) * 2;
        }

        return $estimatedTime;
    }

    /**
     * Calculate optimal worker count based on queue analysis
     */
    private function calculateOptimalWorkerCount(int $totalJobs, float $totalTime): int
    {
        if ($totalJobs <= 0) {
            return 0;
        }

        // Target: complete all jobs within 4 hours
        $targetCompletionTime = 4.0;
        
        $workersForTime = ceil($totalTime / $targetCompletionTime);
        $workersForJobs = ceil($totalJobs / $this->config['jobs_per_worker']);
        
        // Take the higher of the two estimates
        $recommendedWorkers = max($workersForTime, $workersForJobs);
        
        // Apply limits
        return min($recommendedWorkers, $this->config['max_workers_per_deployment']);
    }

    /**
     * Select appropriate instance type based on complexity
     */
    private function selectInstanceType(float $averageComplexity): string
    {
        if ($averageComplexity >= 4.0) {
            return 'vc2-8c-16gb'; // High performance for 4K
        } elseif ($averageComplexity >= 2.5) {
            return 'vc2-6c-12gb'; // Medium-high performance
        } elseif ($averageComplexity >= 1.5) {
            return 'vc2-4c-8gb';  // Standard performance
        }
        
        return 'vc2-2c-4gb'; // Basic performance for simple jobs
    }

    /**
     * Get master server IPs for worker connection
     */
    private function getMasterIPs(): array
    {
        // This would typically fetch from database or configuration
        // For now, return a placeholder
        return ['10.0.1.10']; // Master private IP
    }

    /**
     * Record deployment for tracking and analytics
     */
    private function recordDeployment(array $deployments, array $analysis): void
    {
        try {
            $record = [
                'timestamp' => time(),
                'deployments' => $deployments,
                'queue_analysis' => $analysis,
                'deployment_type' => 'scheduled',
            ];
            
            // Store in Redis for quick access
            Redis::lpush('dcparty:deployment_history', json_encode($record));
            
            // Keep only last 100 deployment records
            Redis::ltrim('dcparty:deployment_history', 0, 99);
            
            Log::info('Deployment recorded successfully', [
                'total_workers' => array_sum(array_column($deployments, 'count')),
                'providers_used' => array_column($deployments, 'provider')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to record deployment', [
                'error' => $e->getMessage(),
                'deployments' => $deployments
            ]);
        }
    }
}
