<?php

namespace Modules\Meniscus\app\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OpenTofuManager
{
    private string $workingDir;

    private string $configsPath;

    private array $availableProviders;

    private array $availableScenarios;

    public function __construct()
    {
        $this->workingDir = storage_path('app/opentofu');
        $this->configsPath = $this->workingDir.'/configs';

        // Ensure directories exist
        if (! File::exists($this->workingDir)) {
            File::makeDirectory($this->workingDir, 0755, true);
        }
        if (! File::exists($this->configsPath)) {
            File::makeDirectory($this->configsPath, 0755, true);
        }

        $this->availableProviders = [
            'vultr' => [
                'name' => 'Vultr',
                'resources' => ['vultr_instance', 'vultr_ssh_key', 'vultr_vpc', 'vultr_firewall_group'],
                'required_vars' => ['vultr_api_key', 'vultr_region'],
            ],
            'aws' => [
                'name' => 'Amazon Web Services',
                'resources' => ['aws_instance', 'aws_key_pair', 'aws_vpc', 'aws_security_group'],
                'required_vars' => ['aws_access_key', 'aws_secret_key', 'aws_region'],
            ],
            'azure' => [
                'name' => 'Microsoft Azure',
                'resources' => ['azurerm_linux_virtual_machine', 'azurerm_resource_group'],
                'required_vars' => ['azure_subscription_id', 'azure_client_id'],
            ],
            'gcp' => [
                'name' => 'Google Cloud Platform',
                'resources' => ['google_compute_instance', 'google_compute_network'],
                'required_vars' => ['gcp_project', 'gcp_region'],
            ],
        ];

        $this->availableScenarios = [
            'backend-automation' => [
                'name' => 'Backend Automation',
                'description' => 'Master permanent + Workers dynamiques pour DCP processing',
                'template' => 'backend-automation.tf.tpl',
            ],
            'manual-testing' => [
                'name' => 'Manual Testing',
                'description' => 'Machine puissante avec GUI pour tests manuels',
                'template' => 'manual-testing.tf.tpl',
            ],
            'high-performance-windows' => [
                'name' => 'High-Performance Windows',
                'description' => 'Machine Windows avec maximum CPU cores',
                'template' => 'windows-workstation.tf.tpl',
            ],
        ];
    }

    /**
     * Get available providers
     */
    public function getAvailableProviders(): array
    {
        return $this->availableProviders;
    }

    /**
     * Get available scenarios
     */
    public function getAvailableScenarios(): array
    {
        return $this->availableScenarios;
    }

    /**
     * Create a new configuration from template
     */
    public function createConfiguration(string $name, string $scenario, string $provider, array $variables = []): array
    {
        try {
            $configId = uniqid('config_');
            $configPath = $this->configsPath.'/'.$configId;

            // Create configuration directory
            File::makeDirectory($configPath, 0755, true);

            // Generate main.tf from template
            $mainTf = $this->generateMainTf($scenario, $provider, $variables);
            File::put($configPath.'/main.tf', $mainTf);

            // Generate variables.tf
            $variablesTf = $this->generateVariablesTf($provider, $variables);
            File::put($configPath.'/variables.tf', $variablesTf);

            // Generate terraform.tfvars
            $tfvars = $this->generateTfVars($variables);
            File::put($configPath.'/terraform.tfvars', $tfvars);

            // Store configuration metadata
            $metadata = [
                'id' => $configId,
                'name' => $name,
                'scenario' => $scenario,
                'provider' => $provider,
                'variables' => $variables,
                'created_at' => now()->toISOString(),
                'status' => 'draft',
                'path' => $configPath,
            ];

            File::put($configPath.'/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));

            Log::info('OpenTofu configuration created', [
                'config_id' => $configId,
                'name' => $name,
                'scenario' => $scenario,
                'provider' => $provider,
            ]);

            return $metadata;

        } catch (\Exception $e) {
            Log::error('Failed to create OpenTofu configuration', [
                'name' => $name,
                'scenario' => $scenario,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to create configuration: '.$e->getMessage());
        }
    }

    /**
     * List all configurations
     */
    public function listConfigurations(): array
    {
        $configurations = [];

        if (! File::exists($this->configsPath)) {
            return $configurations;
        }

        $configDirs = File::directories($this->configsPath);

        foreach ($configDirs as $configDir) {
            $metadataFile = $configDir.'/metadata.json';

            if (File::exists($metadataFile)) {
                $metadata = json_decode(File::get($metadataFile), true);
                $configurations[] = $metadata;
            }
        }

        // Sort by created_at desc
        usort($configurations, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $configurations;
    }

    /**
     * Get configuration details
     */
    public function getConfiguration(string $configId): ?array
    {
        $configPath = $this->configsPath.'/'.$configId;
        $metadataFile = $configPath.'/metadata.json';

        if (! File::exists($metadataFile)) {
            return null;
        }

        $metadata = json_decode(File::get($metadataFile), true);

        // Add file contents
        $metadata['files'] = [];

        $files = ['main.tf', 'variables.tf', 'terraform.tfvars'];
        foreach ($files as $file) {
            $filePath = $configPath.'/'.$file;
            if (File::exists($filePath)) {
                $metadata['files'][$file] = File::get($filePath);
            }
        }

        return $metadata;
    }

    /**
     * Update configuration files
     */
    public function updateConfiguration(string $configId, array $files): bool
    {
        try {
            $configPath = $this->configsPath.'/'.$configId;

            if (! File::exists($configPath)) {
                throw new \RuntimeException('Configuration not found');
            }

            // Update files
            foreach ($files as $filename => $content) {
                if (in_array($filename, ['main.tf', 'variables.tf', 'terraform.tfvars'])) {
                    File::put($configPath.'/'.$filename, $content);
                }
            }

            // Update metadata
            $metadataFile = $configPath.'/metadata.json';
            if (File::exists($metadataFile)) {
                $metadata = json_decode(File::get($metadataFile), true);
                $metadata['updated_at'] = now()->toISOString();
                File::put($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
            }

            Log::info('OpenTofu configuration updated', ['config_id' => $configId]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update OpenTofu configuration', [
                'config_id' => $configId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate configuration syntax
     */
    public function validateConfiguration(string $configId): array
    {
        $configPath = $this->configsPath.'/'.$configId;

        try {
            // Run tofu validate
            $result = Process::path($configPath)
                ->timeout(60)
                ->run('tofu init -backend=false');

            if (! $result->successful()) {
                return [
                    'valid' => false,
                    'errors' => ['Init failed: '.$result->errorOutput()],
                    'output' => $result->output(),
                ];
            }

            $result = Process::path($configPath)
                ->timeout(60)
                ->run('tofu validate');

            if ($result->successful()) {
                return [
                    'valid' => true,
                    'errors' => [],
                    'output' => $result->output(),
                ];
            } else {
                return [
                    'valid' => false,
                    'errors' => ['Validation failed: '.$result->errorOutput()],
                    'output' => $result->output(),
                ];
            }

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Exception: '.$e->getMessage()],
                'output' => '',
            ];
        }
    }

    /**
     * Generate deployment plan
     */
    public function generatePlan(string $configId): array
    {
        $configPath = $this->configsPath.'/'.$configId;

        try {
            // Initialize if needed
            $initResult = Process::path($configPath)
                ->timeout(120)
                ->run('tofu init');

            if (! $initResult->successful()) {
                throw new ProcessFailedException('Init failed: '.$initResult->errorOutput());
            }

            // Generate plan
            $planResult = Process::path($configPath)
                ->timeout(180)
                ->run('tofu plan -out=tfplan');

            if (! $planResult->successful()) {
                return [
                    'success' => false,
                    'error' => $planResult->errorOutput(),
                    'output' => $planResult->output(),
                ];
            }

            // Get plan in JSON format
            $showResult = Process::path($configPath)
                ->timeout(60)
                ->run('tofu show -json tfplan');

            if (! $showResult->successful()) {
                return [
                    'success' => true,
                    'plan_text' => $planResult->output(),
                    'plan_json' => null,
                ];
            }

            return [
                'success' => true,
                'plan_text' => $planResult->output(),
                'plan_json' => json_decode($showResult->output(), true),
                'output' => $planResult->output(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate OpenTofu plan', [
                'config_id' => $configId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'output' => '',
            ];
        }
    }

    /**
     * Deploy configuration
     */
    public function deployConfiguration(string $configId): array
    {
        $configPath = $this->configsPath.'/'.$configId;

        try {
            // Check if plan exists
            if (! File::exists($configPath.'/tfplan')) {
                throw new \RuntimeException('No plan found. Generate plan first.');
            }

            // Apply the plan
            $result = Process::path($configPath)
                ->timeout(1800) // 30 minutes
                ->run('tofu apply tfplan');

            if ($result->successful()) {
                // Get outputs
                $outputResult = Process::path($configPath)
                    ->timeout(60)
                    ->run('tofu output -json');

                $outputs = $outputResult->successful() ?
                    json_decode($outputResult->output(), true) : [];

                // Update metadata
                $this->updateConfigurationStatus($configId, 'deployed', $outputs);

                Log::info('OpenTofu configuration deployed successfully', [
                    'config_id' => $configId,
                ]);

                return [
                    'success' => true,
                    'output' => $result->output(),
                    'outputs' => $outputs,
                ];
            } else {
                $this->updateConfigurationStatus($configId, 'failed');

                return [
                    'success' => false,
                    'error' => $result->errorOutput(),
                    'output' => $result->output(),
                ];
            }

        } catch (\Exception $e) {
            Log::error('Failed to deploy OpenTofu configuration', [
                'config_id' => $configId,
                'error' => $e->getMessage(),
            ]);

            $this->updateConfigurationStatus($configId, 'failed');

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'output' => '',
            ];
        }
    }

    /**
     * Destroy infrastructure
     */
    public function destroyConfiguration(string $configId): array
    {
        $configPath = $this->configsPath.'/'.$configId;

        try {
            $result = Process::path($configPath)
                ->timeout(1800) // 30 minutes
                ->run('tofu destroy -auto-approve');

            if ($result->successful()) {
                $this->updateConfigurationStatus($configId, 'destroyed');

                Log::info('OpenTofu configuration destroyed', [
                    'config_id' => $configId,
                ]);

                return [
                    'success' => true,
                    'output' => $result->output(),
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result->errorOutput(),
                    'output' => $result->output(),
                ];
            }

        } catch (\Exception $e) {
            Log::error('Failed to destroy OpenTofu configuration', [
                'config_id' => $configId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'output' => '',
            ];
        }
    }

    /**
     * Generate main.tf from template
     */
    private function generateMainTf(string $scenario, string $provider, array $variables): string
    {
        $template = $this->getScenarioTemplate($scenario);

        // Replace placeholders with actual values
        $replacements = [
            '{{PROVIDER}}' => $provider,
            '{{PROJECT_NAME}}' => $variables['project_name'] ?? 'dcparty',
            '{{ENVIRONMENT}}' => $variables['environment'] ?? 'development',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Generate variables.tf
     */
    private function generateVariablesTf(string $provider, array $variables): string
    {
        $content = "# Variables for DCParty OpenTofu Configuration\n\n";

        $providerConfig = $this->availableProviders[$provider] ?? [];

        // Add required provider variables
        if (isset($providerConfig['required_vars'])) {
            foreach ($providerConfig['required_vars'] as $varName) {
                $content .= "variable \"{$varName}\" {\n";
                $content .= "  description = \"Required variable for {$provider}\"\n";
                $content .= "  type        = string\n";
                $content .= "  sensitive   = true\n";
                $content .= "}\n\n";
            }
        }

        // Add common variables
        $commonVars = [
            'project_name' => 'string',
            'environment' => 'string',
            'region' => 'string',
        ];

        foreach ($commonVars as $varName => $type) {
            $content .= "variable \"{$varName}\" {\n";
            $content .= '  description = "'.ucfirst(str_replace('_', ' ', $varName))."\"\n";
            $content .= "  type        = {$type}\n";
            $content .= "}\n\n";
        }

        return $content;
    }

    /**
     * Generate terraform.tfvars
     */
    private function generateTfVars(array $variables): string
    {
        $content = "# DCParty Configuration Values\n\n";

        foreach ($variables as $key => $value) {
            if (is_string($value)) {
                $content .= "{$key} = \"{$value}\"\n";
            } elseif (is_bool($value)) {
                $content .= "{$key} = ".($value ? 'true' : 'false')."\n";
            } elseif (is_numeric($value)) {
                $content .= "{$key} = {$value}\n";
            }
        }

        return $content;
    }

    /**
     * Get scenario template
     */
    private function getScenarioTemplate(string $scenario): string
    {
        // Basic template - in production, these would be stored as files
        $templates = [
            'backend-automation' => $this->getBackendAutomationTemplate(),
            'manual-testing' => $this->getManualTestingTemplate(),
            'high-performance-windows' => $this->getWindowsWorkstationTemplate(),
        ];

        return $templates[$scenario] ?? $templates['backend-automation'];
    }

    /**
     * Backend automation template
     */
    private function getBackendAutomationTemplate(): string
    {
        return <<<'EOF'
# DCParty Backend Automation Configuration
# Generated from template

terraform {
  required_version = ">= 1.0"
  required_providers {
    {{PROVIDER}} = {
      source  = "vultr/vultr"
      version = "~> 2.0"
    }
  }
}

# Master server for DCP processing
resource "vultr_instance" "master" {
  count = 1
  
  region = var.region
  plan   = "vc2-4c-8gb"
  os_id  = var.dcp_matic_linux_image_id
  
  label    = "{{PROJECT_NAME}}-master-{{ENVIRONMENT}}"
  hostname = "master.{{PROJECT_NAME}}.local"
  
  enable_ipv6      = false
  ddos_protection  = true
  activation_email = false
  
  tags = [
    "project:{{PROJECT_NAME}}",
    "environment:{{ENVIRONMENT}}",
    "type:master",
    "scenario:backend-automation"
  ]
}

output "master_ip" {
  description = "Master server IP"
  value       = vultr_instance.master[0].main_ip
}
EOF;
    }

    /**
     * Manual testing template
     */
    private function getManualTestingTemplate(): string
    {
        return <<<'EOF'
# DCParty Manual Testing Configuration
# Generated from template

terraform {
  required_version = ">= 1.0"
  required_providers {
    {{PROVIDER}} = {
      source  = "vultr/vultr"
      version = "~> 2.0"
    }
  }
}

# Testing machine with GUI
resource "vultr_instance" "testing" {
  count = 1
  
  region = var.region
  plan   = "vc2-4c-8gb"
  os_id  = var.dcp_matic_linux_image_id
  
  label    = "{{PROJECT_NAME}}-testing-{{ENVIRONMENT}}"
  hostname = "testing.{{PROJECT_NAME}}.local"
  
  enable_ipv6      = false
  ddos_protection  = false
  activation_email = false
  
  tags = [
    "project:{{PROJECT_NAME}}",
    "environment:{{ENVIRONMENT}}",
    "type:testing",
    "scenario:manual-testing"
  ]
}

output "testing_ip" {
  description = "Testing machine IP"
  value       = vultr_instance.testing[0].main_ip
}

output "guacamole_url" {
  description = "Guacamole access URL"
  value       = "http://${vultr_instance.testing[0].main_ip}:8080/guacamole"
}
EOF;
    }

    /**
     * Windows workstation template
     */
    private function getWindowsWorkstationTemplate(): string
    {
        return <<<'EOF'
# DCParty High-Performance Windows Workstation
# Generated from template

terraform {
  required_version = ">= 1.0"
  required_providers {
    {{PROVIDER}} = {
      source  = "vultr/vultr"
      version = "~> 2.0"
    }
  }
}

# High-performance Windows workstation
resource "vultr_instance" "workstation" {
  count = 1
  
  region = var.region
  plan   = "vc2-16c-32gb"  # Maximum CPU cores
  os_id  = var.windows_image_id
  
  label    = "{{PROJECT_NAME}}-workstation-{{ENVIRONMENT}}"
  hostname = "workstation.{{PROJECT_NAME}}.local"
  
  enable_ipv6      = false
  ddos_protection  = false
  activation_email = false
  
  tags = [
    "project:{{PROJECT_NAME}}",
    "environment:{{ENVIRONMENT}}",
    "type:workstation",
    "scenario:high-performance-windows"
  ]
}

output "workstation_ip" {
  description = "Windows workstation IP"
  value       = vultr_instance.workstation[0].main_ip
}

output "rdp_access" {
  description = "RDP connection string"
  value       = "${vultr_instance.workstation[0].main_ip}:3389"
}
EOF;
    }

    /**
     * Update configuration status in metadata
     */
    private function updateConfigurationStatus(string $configId, string $status, array $outputs = []): void
    {
        $configPath = $this->configsPath.'/'.$configId;
        $metadataFile = $configPath.'/metadata.json';

        if (File::exists($metadataFile)) {
            $metadata = json_decode(File::get($metadataFile), true);
            $metadata['status'] = $status;
            $metadata['updated_at'] = now()->toISOString();

            if (! empty($outputs)) {
                $metadata['outputs'] = $outputs;
            }

            File::put($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
        }
    }
}
