<?php

namespace Modules\Meniscus\app\Services\Providers;

use App\Contracts\CloudProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class VultrProvider implements CloudProviderInterface
{
    private Client $client;
    private string $apiKey;
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? config('services.vultr.api_key');
        
        $this->client = new Client([
            'base_uri' => 'https://api.vultr.com/v2/',
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function deployWorkers(array $config): array
    {
        $deployedWorkers = [];
        $workerCount = $config['worker_count'] ?? 1;
        $plan = $config['plan'] ?? 'vc2-4c-8gb';
        $region = $config['region'] ?? 'fra';
        $osId = $config['os_id'] ?? $this->config['dcp_matic_linux_image_id'];

        try {
            for ($i = 0; $i < $workerCount; $i++) {
                $instanceData = [
                    'region' => $region,
                    'plan' => $plan,
                    'os_id' => $osId,
                    'label' => "dcparty-worker-{$config['environment']}-" . time() . "-{$i}",
                    'hostname' => "worker-{$i}.{$config['domain_name']}",
                    'enable_ipv6' => false,
                    'ddos_protection' => false,
                    'user_data' => base64_encode($this->generateWorkerUserData($config, $i)),
                    'tags' => [
                        'project:dcparty',
                        'type:worker',
                        'environment:' . $config['environment'],
                        'deployment:' . ($config['deployment_id'] ?? 'unknown')
                    ]
                ];

                if (isset($config['ssh_key_id'])) {
                    $instanceData['sshkey_id'] = [$config['ssh_key_id']];
                }

                $response = $this->client->post('instances', [
                    'json' => $instanceData
                ]);

                $instance = json_decode($response->getBody(), true);
                $deployedWorkers[] = [
                    'provider' => 'vultr',
                    'id' => $instance['instance']['id'],
                    'label' => $instance['instance']['label'],
                    'main_ip' => $instance['instance']['main_ip'] ?? null,
                    'status' => 'deploying',
                    'plan' => $plan,
                    'region' => $region,
                ];
            }

            Log::info('Vultr workers deployed successfully', [
                'count' => $workerCount,
                'deployment_id' => $config['deployment_id'] ?? null
            ]);

        } catch (RequestException $e) {
            Log::error('Failed to deploy Vultr workers', [
                'error' => $e->getMessage(),
                'config' => $config
            ]);
            throw new \RuntimeException('Failed to deploy workers: ' . $e->getMessage());
        }

        return $deployedWorkers;
    }

    public function getWorkerStatus(): array
    {
        try {
            $response = $this->client->get('instances');
            $instances = json_decode($response->getBody(), true);
            
            $workers = [];
            foreach ($instances['instances'] as $instance) {
                // Filter only DCParty workers
                if (str_contains($instance['label'], 'dcparty-worker')) {
                    $workers[] = [
                        'id' => $instance['id'],
                        'label' => $instance['label'],
                        'main_ip' => $instance['main_ip'],
                        'status' => $this->mapVultrStatus($instance['status']),
                        'plan' => $instance['plan'],
                        'region' => $instance['region'],
                        'date_created' => $instance['date_created'],
                        'ram' => $instance['ram'] ?? 0,
                        'vcpu_count' => $instance['vcpu_count'] ?? 0,
                    ];
                }
            }

            return $workers;

        } catch (RequestException $e) {
            Log::error('Failed to get Vultr worker status', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function terminateWorkers(array $workerIds): bool
    {
        try {
            foreach ($workerIds as $workerId) {
                $this->client->delete("instances/{$workerId}");
                Log::info('Vultr worker terminated', ['worker_id' => $workerId]);
            }
            return true;

        } catch (RequestException $e) {
            Log::error('Failed to terminate Vultr workers', [
                'error' => $e->getMessage(),
                'worker_ids' => $workerIds
            ]);
            return false;
        }
    }

    public function getPricing(): array
    {
        try {
            $response = $this->client->get('plans');
            $plans = json_decode($response->getBody(), true);
            
            $pricing = [];
            foreach ($plans['plans'] as $plan) {
                if ($plan['type'] === 'vc2') { // Regular performance plans
                    $pricing[$plan['id']] = [
                        'vcpu_count' => $plan['vcpu_count'],
                        'ram' => $plan['ram'],
                        'disk' => $plan['disk'],
                        'monthly_cost' => $plan['monthly_cost'],
                        'hourly_cost' => round($plan['monthly_cost'] / 730, 4), // Approximate hourly
                    ];
                }
            }

            return $pricing;

        } catch (RequestException $e) {
            Log::error('Failed to get Vultr pricing', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function checkAvailability(string $region): bool
    {
        try {
            $response = $this->client->get('regions');
            $regions = json_decode($response->getBody(), true);
            
            foreach ($regions['regions'] as $availableRegion) {
                if ($availableRegion['id'] === $region) {
                    return true;
                }
            }

            return false;

        } catch (RequestException $e) {
            Log::error('Failed to check Vultr availability', [
                'error' => $e->getMessage(),
                'region' => $region
            ]);
            return false;
        }
    }

    public function getAvailableInstanceTypes(): array
    {
        try {
            $response = $this->client->get('plans');
            $plans = json_decode($response->getBody(), true);
            
            $instanceTypes = [];
            foreach ($plans['plans'] as $plan) {
                if ($plan['type'] === 'vc2') {
                    $instanceTypes[] = [
                        'id' => $plan['id'],
                        'vcpu_count' => $plan['vcpu_count'],
                        'ram' => $plan['ram'],
                        'disk' => $plan['disk'],
                        'monthly_cost' => $plan['monthly_cost'],
                    ];
                }
            }

            return $instanceTypes;

        } catch (RequestException $e) {
            Log::error('Failed to get Vultr instance types', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function getAvailableRegions(): array
    {
        try {
            $response = $this->client->get('regions');
            $regions = json_decode($response->getBody(), true);
            
            return array_map(function ($region) {
                return [
                    'id' => $region['id'],
                    'city' => $region['city'],
                    'country' => $region['country'],
                ];
            }, $regions['regions']);

        } catch (RequestException $e) {
            Log::error('Failed to get Vultr regions', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function calculateCost(array $config): float
    {
        $workerCount = $config['worker_count'] ?? 1;
        $plan = $config['plan'] ?? 'vc2-4c-8gb';
        $hoursEstimated = $config['estimated_hours'] ?? 2; // Default 2 hours per job
        
        $pricing = $this->getPricing();
        
        if (!isset($pricing[$plan])) {
            return 0.0; // Unknown plan
        }
        
        $hourlyCost = $pricing[$plan]['hourly_cost'];
        
        return $workerCount * $hourlyCost * $hoursEstimated;
    }

    public function validateConfig(array $config): array
    {
        $errors = [];
        
        if (empty($config['region'])) {
            $errors[] = 'Region is required';
        }
        
        if (empty($config['plan'])) {
            $errors[] = 'Instance plan is required';
        }
        
        if (empty($config['dcp_matic_linux_image_id'])) {
            $errors[] = 'DCP-O-MATIC Linux image ID is required';
        }
        
        return $errors;
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->client->get('account');
            $account = json_decode($response->getBody(), true);
            
            return isset($account['account']);

        } catch (RequestException $e) {
            Log::error('Vultr connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate user data script for worker initialization
     */
    private function generateWorkerUserData(array $config, int $workerIndex): string
    {
        return "#!/bin/bash\n" .
               "# DCParty Worker Initialization\n" .
               "export WORKER_INDEX={$workerIndex}\n" .
               "export MASTER_IPS=\"" . implode(',', $config['master_ips'] ?? []) . "\"\n" .
               "export PROJECT_NAME=\"{$config['project_name']}\"\n" .
               "export ENVIRONMENT=\"{$config['environment']}\"\n" .
               "export DEPLOYMENT_ID=\"{$config['deployment_id']}\"\n" .
               "\n" .
               "# Download and execute worker setup script\n" .
               "curl -fsSL https://raw.githubusercontent.com/dcparty/scripts/main/worker-init.sh | bash\n";
    }

    /**
     * Map Vultr status to standardized status
     */
    private function mapVultrStatus(string $vultrStatus): string
    {
        return match ($vultrStatus) {
            'pending' => 'deploying',
            'installing' => 'deploying',
            'active' => 'running',
            'stopped' => 'stopped',
            'resizing' => 'updating',
            default => 'unknown'
        };
    }
}
