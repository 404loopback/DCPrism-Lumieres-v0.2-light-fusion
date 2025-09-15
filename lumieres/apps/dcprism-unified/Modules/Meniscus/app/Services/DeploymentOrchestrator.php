<?php

namespace Modules\Meniscus\app\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class DeploymentOrchestrator
{
    private OpenTofuManager $openTofuManager;
    private AnsibleManager $ansibleManager;
    private string $workingDir;
    private string $deploymentsPath;

    public function __construct(OpenTofuManager $openTofuManager, AnsibleManager $ansibleManager)
    {
        $this->openTofuManager = $openTofuManager;
        $this->ansibleManager = $ansibleManager;
        $this->workingDir = storage_path('app/deployments');
        $this->deploymentsPath = $this->workingDir . '/unified';
        
        // Ensure directories exist
        if (!File::exists($this->workingDir)) {
            File::makeDirectory($this->workingDir, 0755, true);
        }
        if (!File::exists($this->deploymentsPath)) {
            File::makeDirectory($this->deploymentsPath, 0755, true);
        }
    }

    /**
     * Create a complete deployment (OpenTofu + Ansible)
     */
    public function createDeployment(string $name, string $scenario, array $config): array
    {
        try {
            $deploymentId = uniqid('deployment_');
            $deploymentPath = $this->deploymentsPath . '/' . $deploymentId;
            
            // Create deployment directory
            File::makeDirectory($deploymentPath, 0755, true);
            
            // Step 1: Create OpenTofu configuration
            $openTofuConfig = $this->openTofuManager->createConfiguration(
                $name . ' - Infrastructure',
                $scenario,
                $config['provider'] ?? 'vultr',
                $config['infrastructure_variables'] ?? []
            );
            
            // Step 2: Create Ansible playbook based on scenario
            $ansibleTemplate = $this->getAnsibleTemplateForScenario($scenario);
            $ansibleConfig = $this->ansibleManager->createPlaybook(
                $name . ' - Configuration',
                $ansibleTemplate,
                [], // Hosts will be populated after OpenTofu deployment
                $config['configuration_variables'] ?? []
            );
            
            // Step 3: Create unified deployment metadata
            $metadata = [
                'id' => $deploymentId,
                'name' => $name,
                'scenario' => $scenario,
                'status' => 'draft',
                'created_at' => now()->toISOString(),
                'opentofu_config_id' => $openTofuConfig['id'],
                'ansible_playbook_id' => $ansibleConfig['id'],
                'config' => $config,
                'path' => $deploymentPath,
                'phases' => [
                    'infrastructure' => ['status' => 'pending', 'started_at' => null, 'completed_at' => null],
                    'configuration' => ['status' => 'pending', 'started_at' => null, 'completed_at' => null],
                    'validation' => ['status' => 'pending', 'started_at' => null, 'completed_at' => null]
                ]
            ];
            
            File::put($deploymentPath . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
            
            Log::info('Unified deployment created', [
                'deployment_id' => $deploymentId,
                'name' => $name,
                'scenario' => $scenario
            ]);
            
            return $metadata;
            
        } catch (\Exception $e) {
            Log::error('Failed to create unified deployment', [
                'name' => $name,
                'scenario' => $scenario,
                'error' => $e->getMessage()
            ]);
            
            throw new \RuntimeException('Failed to create deployment: ' . $e->getMessage());
        }
    }

    /**
     * Deploy complete infrastructure and configuration
     */
    public function deployComplete(string $deploymentId, array $options = []): array
    {
        try {
            $deployment = $this->getDeployment($deploymentId);
            if (!$deployment) {
                throw new \RuntimeException('Deployment not found');
            }
            
            $results = [];
            
            // Phase 1: Deploy infrastructure with OpenTofu
            Log::info('Starting infrastructure deployment', ['deployment_id' => $deploymentId]);
            $this->updateDeploymentPhase($deploymentId, 'infrastructure', 'running');
            
            $infraResult = $this->deployInfrastructure($deployment, $options);
            $results['infrastructure'] = $infraResult;
            
            if (!$infraResult['success']) {
                $this->updateDeploymentPhase($deploymentId, 'infrastructure', 'failed');
                $this->updateDeploymentStatus($deploymentId, 'failed');
                return $results;
            }
            
            $this->updateDeploymentPhase($deploymentId, 'infrastructure', 'completed');
            
            // Phase 2: Configure servers with Ansible
            Log::info('Starting configuration deployment', ['deployment_id' => $deploymentId]);
            $this->updateDeploymentPhase($deploymentId, 'configuration', 'running');
            
            $configResult = $this->deployConfiguration($deployment, $infraResult['outputs'], $options);
            $results['configuration'] = $configResult;
            
            if (!$configResult['success']) {
                $this->updateDeploymentPhase($deploymentId, 'configuration', 'failed');
                $this->updateDeploymentStatus($deploymentId, 'failed');
                return $results;
            }
            
            $this->updateDeploymentPhase($deploymentId, 'configuration', 'completed');
            
            // Phase 3: Validate deployment
            Log::info('Starting deployment validation', ['deployment_id' => $deploymentId]);
            $this->updateDeploymentPhase($deploymentId, 'validation', 'running');
            
            $validationResult = $this->validateDeployment($deployment, $infraResult['outputs']);
            $results['validation'] = $validationResult;
            
            $finalStatus = $validationResult['success'] ? 'deployed' : 'partially_deployed';
            $this->updateDeploymentPhase($deploymentId, 'validation', $validationResult['success'] ? 'completed' : 'failed');
            $this->updateDeploymentStatus($deploymentId, $finalStatus);
            
            Log::info('Unified deployment completed', [
                'deployment_id' => $deploymentId,
                'final_status' => $finalStatus
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('Failed to deploy unified deployment', [
                'deployment_id' => $deploymentId,
                'error' => $e->getMessage()
            ]);
            
            $this->updateDeploymentStatus($deploymentId, 'failed');
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get deployment status and details
     */
    public function getDeployment(string $deploymentId): ?array
    {
        $deploymentPath = $this->deploymentsPath . '/' . $deploymentId;
        $metadataFile = $deploymentPath . '/metadata.json';
        
        if (!File::exists($metadataFile)) {
            return null;
        }
        
        $metadata = json_decode(File::get($metadataFile), true);
        
        // Enrich with current status from OpenTofu and Ansible
        if (isset($metadata['opentofu_config_id'])) {
            $openTofuConfig = $this->openTofuManager->getConfiguration($metadata['opentofu_config_id']);
            $metadata['infrastructure_status'] = $openTofuConfig['status'] ?? 'unknown';
        }
        
        if (isset($metadata['ansible_playbook_id'])) {
            $ansiblePlaybook = $this->ansibleManager->getPlaybook($metadata['ansible_playbook_id']);
            $metadata['configuration_status'] = $ansiblePlaybook['status'] ?? 'unknown';
        }
        
        return $metadata;
    }

    /**
     * List all deployments
     */
    public function listDeployments(): array
    {
        $deployments = [];
        
        if (!File::exists($this->deploymentsPath)) {
            return $deployments;
        }
        
        $deploymentDirs = File::directories($this->deploymentsPath);
        
        foreach ($deploymentDirs as $deploymentDir) {
            $metadataFile = $deploymentDir . '/metadata.json';
            
            if (File::exists($metadataFile)) {
                $metadata = json_decode(File::get($metadataFile), true);
                $deployments[] = $metadata;
            }
        }
        
        // Sort by created_at desc
        usort($deployments, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $deployments;
    }

    /**
     * Destroy complete deployment
     */
    public function destroyDeployment(string $deploymentId): array
    {
        try {
            $deployment = $this->getDeployment($deploymentId);
            if (!$deployment) {
                throw new \RuntimeException('Deployment not found');
            }
            
            $results = [];
            
            // Step 1: Destroy infrastructure
            if (isset($deployment['opentofu_config_id'])) {
                $infraResult = $this->openTofuManager->destroyConfiguration($deployment['opentofu_config_id']);
                $results['infrastructure_destroy'] = $infraResult;
            }
            
            // Update status
            $this->updateDeploymentStatus($deploymentId, 'destroyed');
            
            Log::info('Unified deployment destroyed', ['deployment_id' => $deploymentId]);
            
            return [
                'success' => true,
                'results' => $results
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to destroy unified deployment', [
                'deployment_id' => $deploymentId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Deploy infrastructure phase
     */
    private function deployInfrastructure(array $deployment, array $options): array
    {
        try {
            $configId = $deployment['opentofu_config_id'];
            
            // Generate plan
            $planResult = $this->openTofuManager->generatePlan($configId);
            if (!$planResult['success']) {
                return $planResult;
            }
            
            // Deploy infrastructure
            $deployResult = $this->openTofuManager->deployConfiguration($configId);
            
            return $deployResult;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Deploy configuration phase
     */
    private function deployConfiguration(array $deployment, array $infraOutputs, array $options): array
    {
        try {
            $playbookId = $deployment['ansible_playbook_id'];
            
            // Update Ansible inventory with infrastructure outputs
            $this->updateAnsibleInventory($playbookId, $infraOutputs);
            
            // Execute playbook
            $execResult = $this->ansibleManager->executePlaybook($playbookId, $options);
            
            return $execResult;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate deployment
     */
    private function validateDeployment(array $deployment, array $infraOutputs): array
    {
        try {
            $checks = [];
            
            // Check infrastructure accessibility
            if (isset($infraOutputs['master_ip']['value'])) {
                $ip = $infraOutputs['master_ip']['value'];
                $checks['ssh_connectivity'] = $this->checkSSHConnectivity($ip);
            }
            
            // Check services based on scenario
            $scenario = $deployment['scenario'];
            $checks['services'] = $this->checkScenarioServices($scenario, $infraOutputs);
            
            // Overall success
            $allChecksPass = true;
            foreach ($checks as $check) {
                if (is_array($check) && isset($check['success'])) {
                    $allChecksPass = $allChecksPass && $check['success'];
                } elseif (!$check) {
                    $allChecksPass = false;
                }
            }
            
            return [
                'success' => $allChecksPass,
                'checks' => $checks
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'checks' => []
            ];
        }
    }

    /**
     * Update Ansible inventory with infrastructure outputs
     */
    private function updateAnsibleInventory(string $playbookId, array $infraOutputs): void
    {
        $playbook = $this->ansibleManager->getPlaybook($playbookId);
        if (!$playbook) {
            throw new \RuntimeException('Playbook not found');
        }
        
        // Extract IPs from infrastructure outputs
        $hosts = [];
        
        if (isset($infraOutputs['master_ip']['value'])) {
            $hosts[] = [
                'hostname' => 'master',
                'ip' => $infraOutputs['master_ip']['value'],
                'user' => 'root',
                'groups' => ['masters', 'dcparty']
            ];
        }
        
        if (isset($infraOutputs['worker_ips']['value']) && is_array($infraOutputs['worker_ips']['value'])) {
            foreach ($infraOutputs['worker_ips']['value'] as $i => $ip) {
                $hosts[] = [
                    'hostname' => 'worker' . ($i + 1),
                    'ip' => $ip,
                    'user' => 'root',
                    'groups' => ['workers', 'dcparty']
                ];
            }
        }
        
        // Generate new inventory
        $inventory = $this->generateUpdatedInventory($hosts);
        
        // Update the playbook
        $this->ansibleManager->updatePlaybook($playbookId, [
            'inventory.ini' => $inventory
        ]);
    }

    /**
     * Generate updated inventory content
     */
    private function generateUpdatedInventory(array $hosts): string
    {
        $inventory = "[dcparty]\n";
        
        foreach ($hosts as $host) {
            $line = $host['hostname'];
            $line .= " ansible_host=" . $host['ip'];
            $line .= " ansible_user=" . ($host['user'] ?? 'root');
            $line .= " ansible_ssh_common_args='-o StrictHostKeyChecking=no'";
            
            $inventory .= $line . "\n";
        }
        
        // Add groups
        $groups = ['masters' => [], 'workers' => []];
        foreach ($hosts as $host) {
            if (in_array('masters', $host['groups'])) {
                $groups['masters'][] = $host['hostname'];
            }
            if (in_array('workers', $host['groups'])) {
                $groups['workers'][] = $host['hostname'];
            }
        }
        
        foreach ($groups as $groupName => $groupHosts) {
            if (!empty($groupHosts)) {
                $inventory .= "\n[{$groupName}]\n";
                foreach ($groupHosts as $hostname) {
                    $inventory .= $hostname . "\n";
                }
            }
        }
        
        return $inventory;
    }

    /**
     * Check SSH connectivity
     */
    private function checkSSHConnectivity(string $ip): array
    {
        try {
            $result = Process::timeout(30)->run("ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no root@{$ip} 'echo connected'");
            
            return [
                'success' => $result->successful(),
                'message' => $result->successful() ? 'SSH connection successful' : 'SSH connection failed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SSH check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check scenario-specific services
     */
    private function checkScenarioServices(string $scenario, array $infraOutputs): array
    {
        $checks = [];
        
        switch ($scenario) {
            case 'backend-automation':
                // Check DCP-o-matic master service
                if (isset($infraOutputs['master_ip']['value'])) {
                    $ip = $infraOutputs['master_ip']['value'];
                    $checks['dcp_master'] = $this->checkHttpService($ip, 8080, '/health');
                }
                break;
                
            case 'manual-testing':
                // Check Guacamole service
                if (isset($infraOutputs['testing_ip']['value'])) {
                    $ip = $infraOutputs['testing_ip']['value'];
                    $checks['guacamole'] = $this->checkHttpService($ip, 8080, '/guacamole');
                    $checks['desktop'] = $this->checkVNCService($ip, 5901);
                }
                break;
                
            case 'high-performance-windows':
                // Check RDP service
                if (isset($infraOutputs['workstation_ip']['value'])) {
                    $ip = $infraOutputs['workstation_ip']['value'];
                    $checks['rdp'] = $this->checkRDPService($ip, 3389);
                }
                break;
        }
        
        return $checks;
    }

    /**
     * Check HTTP service availability
     */
    private function checkHttpService(string $ip, int $port, string $path): array
    {
        try {
            $url = "http://{$ip}:{$port}{$path}";
            $result = Process::timeout(30)->run("curl -f -s --connect-timeout 10 {$url}");
            
            return [
                'success' => $result->successful(),
                'message' => $result->successful() ? "HTTP service available at {$url}" : "HTTP service unavailable"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'HTTP check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check VNC service availability
     */
    private function checkVNCService(string $ip, int $port): array
    {
        try {
            $result = Process::timeout(10)->run("nc -z -v {$ip} {$port}");
            
            return [
                'success' => $result->successful(),
                'message' => $result->successful() ? "VNC service available on port {$port}" : "VNC service unavailable"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'VNC check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check RDP service availability
     */
    private function checkRDPService(string $ip, int $port): array
    {
        try {
            $result = Process::timeout(10)->run("nc -z -v {$ip} {$port}");
            
            return [
                'success' => $result->successful(),
                'message' => $result->successful() ? "RDP service available on port {$port}" : "RDP service unavailable"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'RDP check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Ansible template for scenario
     */
    private function getAnsibleTemplateForScenario(string $scenario): string
    {
        $mapping = [
            'backend-automation' => 'backend-automation-setup',
            'manual-testing' => 'manual-testing-setup',
            'high-performance-windows' => 'windows-workstation-setup'
        ];
        
        return $mapping[$scenario] ?? 'backend-automation-setup';
    }

    /**
     * Update deployment phase status
     */
    private function updateDeploymentPhase(string $deploymentId, string $phase, string $status): void
    {
        $deploymentPath = $this->deploymentsPath . '/' . $deploymentId;
        $metadataFile = $deploymentPath . '/metadata.json';
        
        if (File::exists($metadataFile)) {
            $metadata = json_decode(File::get($metadataFile), true);
            
            $metadata['phases'][$phase]['status'] = $status;
            
            if ($status === 'running') {
                $metadata['phases'][$phase]['started_at'] = now()->toISOString();
            } elseif (in_array($status, ['completed', 'failed'])) {
                $metadata['phases'][$phase]['completed_at'] = now()->toISOString();
            }
            
            File::put($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Update deployment overall status
     */
    private function updateDeploymentStatus(string $deploymentId, string $status): void
    {
        $deploymentPath = $this->deploymentsPath . '/' . $deploymentId;
        $metadataFile = $deploymentPath . '/metadata.json';
        
        if (File::exists($metadataFile)) {
            $metadata = json_decode(File::get($metadataFile), true);
            $metadata['status'] = $status;
            $metadata['updated_at'] = now()->toISOString();
            
            if ($status === 'deployed') {
                $metadata['deployed_at'] = now()->toISOString();
            } elseif ($status === 'destroyed') {
                $metadata['destroyed_at'] = now()->toISOString();
            }
            
            File::put($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
        }
    }
}
