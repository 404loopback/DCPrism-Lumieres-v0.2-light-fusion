<?php

namespace Modules\Meniscus\app\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Yaml\Yaml;

class AnsibleManager
{
    private string $workingDir;
    private string $playbooksPath;
    private string $inventoriesPath;
    private string $rolesPath;
    private array $availableRoles;
    private array $availablePlaybooks;

    public function __construct()
    {
        $this->workingDir = storage_path('app/ansible');
        $this->playbooksPath = $this->workingDir . '/playbooks';
        $this->inventoriesPath = $this->workingDir . '/inventories';
        $this->rolesPath = $this->workingDir . '/roles';
        
        // Ensure directories exist
        $this->createDirectoryStructure();
        
        $this->availableRoles = [
            'dcp-omatic-master' => [
                'name' => 'DCP-O-MATIC Master',
                'description' => 'Configure master server for DCP-O-MATIC processing',
                'variables' => ['dcp_version', 'b2_credentials', 'workers_config']
            ],
            'dcp-omatic-worker' => [
                'name' => 'DCP-O-MATIC Worker',
                'description' => 'Configure worker nodes for DCP-O-MATIC processing',
                'variables' => ['master_ip', 'worker_slots', 'storage_config']
            ],
            'linux-gui-desktop' => [
                'name' => 'Linux GUI Desktop',
                'description' => 'Setup Linux desktop environment with GUI',
                'variables' => ['desktop_environment', 'user_config', 'remote_access']
            ],
            'guacamole-server' => [
                'name' => 'Apache Guacamole',
                'description' => 'Setup Guacamole for web-based remote desktop',
                'variables' => ['guacamole_user', 'guacamole_password', 'connections']
            ],
            'windows-workstation' => [
                'name' => 'Windows Workstation',
                'description' => 'Configure Windows machine with software suite',
                'variables' => ['software_list', 'user_config', 'rdp_config']
            ],
            'security-hardening' => [
                'name' => 'Security Hardening',
                'description' => 'Apply security hardening configurations',
                'variables' => ['firewall_rules', 'ssh_config', 'user_policies']
            ]
        ];

        $this->availablePlaybooks = [
            'backend-automation-setup' => [
                'name' => 'Backend Automation Setup',
                'description' => 'Deploy master + workers for DCP processing',
                'roles' => ['dcp-omatic-master', 'security-hardening']
            ],
            'manual-testing-setup' => [
                'name' => 'Manual Testing Setup',
                'description' => 'Deploy testing machine with GUI',
                'roles' => ['linux-gui-desktop', 'guacamole-server', 'dcp-omatic-master']
            ],
            'windows-workstation-setup' => [
                'name' => 'Windows Workstation Setup',
                'description' => 'Deploy high-performance Windows workstation',
                'roles' => ['windows-workstation']
            ],
            'worker-scaling' => [
                'name' => 'Worker Scaling',
                'description' => 'Deploy additional worker nodes',
                'roles' => ['dcp-omatic-worker', 'security-hardening']
            ]
        ];
    }

    /**
     * Get available roles
     */
    public function getAvailableRoles(): array
    {
        return $this->availableRoles;
    }

    /**
     * Get available playbook templates
     */
    public function getAvailablePlaybooks(): array
    {
        return $this->availablePlaybooks;
    }

    /**
     * Create a new playbook from template
     */
    public function createPlaybook(string $name, string $template, array $hosts, array $variables = []): array
    {
        try {
            $playbookId = uniqid('playbook_');
            $playbookPath = $this->playbooksPath . '/' . $playbookId;
            
            // Create playbook directory
            File::makeDirectory($playbookPath, 0755, true);
            
            // Generate main playbook file
            $playbookContent = $this->generatePlaybook($template, $hosts, $variables);
            $playbookFile = $playbookPath . '/playbook.yml';
            File::put($playbookFile, $playbookContent);
            
            // Generate inventory file
            $inventoryContent = $this->generateInventory($hosts);
            $inventoryFile = $playbookPath . '/inventory.ini';
            File::put($inventoryFile, $inventoryContent);
            
            // Generate group_vars and host_vars
            $this->generateVariableFiles($playbookPath, $hosts, $variables);
            
            // Store playbook metadata
            $metadata = [
                'id' => $playbookId,
                'name' => $name,
                'template' => $template,
                'hosts' => $hosts,
                'variables' => $variables,
                'created_at' => now()->toISOString(),
                'status' => 'draft',
                'path' => $playbookPath,
                'playbook_file' => $playbookFile,
                'inventory_file' => $inventoryFile
            ];
            
            File::put($playbookPath . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
            
            Log::info('Ansible playbook created', [
                'playbook_id' => $playbookId,
                'name' => $name,
                'template' => $template
            ]);
            
            return $metadata;
            
        } catch (\Exception $e) {
            Log::error('Failed to create Ansible playbook', [
                'name' => $name,
                'template' => $template,
                'error' => $e->getMessage()
            ]);
            
            throw new \RuntimeException('Failed to create playbook: ' . $e->getMessage());
        }
    }

    /**
     * List all playbooks
     */
    public function listPlaybooks(): array
    {
        $playbooks = [];
        
        if (!File::exists($this->playbooksPath)) {
            return $playbooks;
        }
        
        $playbookDirs = File::directories($this->playbooksPath);
        
        foreach ($playbookDirs as $playbookDir) {
            $metadataFile = $playbookDir . '/metadata.json';
            
            if (File::exists($metadataFile)) {
                $metadata = json_decode(File::get($metadataFile), true);
                $playbooks[] = $metadata;
            }
        }
        
        // Sort by created_at desc
        usort($playbooks, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $playbooks;
    }

    /**
     * Get playbook details
     */
    public function getPlaybook(string $playbookId): ?array
    {
        $playbookPath = $this->playbooksPath . '/' . $playbookId;
        $metadataFile = $playbookPath . '/metadata.json';
        
        if (!File::exists($metadataFile)) {
            return null;
        }
        
        $metadata = json_decode(File::get($metadataFile), true);
        
        // Add file contents
        $metadata['files'] = [];
        
        $files = ['playbook.yml', 'inventory.ini'];
        foreach ($files as $file) {
            $filePath = $playbookPath . '/' . $file;
            if (File::exists($filePath)) {
                $metadata['files'][$file] = File::get($filePath);
            }
        }
        
        // Add variable files
        $varsPath = $playbookPath . '/group_vars';
        if (File::exists($varsPath)) {
            $varFiles = File::files($varsPath);
            foreach ($varFiles as $varFile) {
                $fileName = 'group_vars/' . basename($varFile);
                $metadata['files'][$fileName] = File::get($varFile);
            }
        }
        
        return $metadata;
    }

    /**
     * Update playbook files
     */
    public function updatePlaybook(string $playbookId, array $files): bool
    {
        try {
            $playbookPath = $this->playbooksPath . '/' . $playbookId;
            
            if (!File::exists($playbookPath)) {
                throw new \RuntimeException('Playbook not found');
            }
            
            // Update files
            foreach ($files as $filename => $content) {
                $allowedFiles = ['playbook.yml', 'inventory.ini'];
                $allowedDirs = ['group_vars/', 'host_vars/'];
                
                $isAllowed = in_array($filename, $allowedFiles);
                foreach ($allowedDirs as $dir) {
                    if (str_starts_with($filename, $dir)) {
                        $isAllowed = true;
                        break;
                    }
                }
                
                if ($isAllowed) {
                    $filePath = $playbookPath . '/' . $filename;
                    
                    // Create directory if needed
                    $directory = dirname($filePath);
                    if (!File::exists($directory)) {
                        File::makeDirectory($directory, 0755, true);
                    }
                    
                    File::put($filePath, $content);
                }
            }
            
            // Update metadata
            $metadataFile = $playbookPath . '/metadata.json';
            if (File::exists($metadataFile)) {
                $metadata = json_decode(File::get($metadataFile), true);
                $metadata['updated_at'] = now()->toISOString();
                File::put($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
            }
            
            Log::info('Ansible playbook updated', ['playbook_id' => $playbookId]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to update Ansible playbook', [
                'playbook_id' => $playbookId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Validate playbook syntax
     */
    public function validatePlaybook(string $playbookId): array
    {
        $playbookPath = $this->playbooksPath . '/' . $playbookId;
        $playbookFile = $playbookPath . '/playbook.yml';
        $inventoryFile = $playbookPath . '/inventory.ini';
        
        try {
            // Check YAML syntax
            $yamlContent = File::get($playbookFile);
            Yaml::parse($yamlContent);
            
            // Run ansible-playbook --syntax-check
            $result = Process::path($playbookPath)
                ->timeout(60)
                ->run("ansible-playbook -i inventory.ini --syntax-check playbook.yml");
            
            if ($result->successful()) {
                return [
                    'valid' => true,
                    'errors' => [],
                    'output' => $result->output()
                ];
            } else {
                return [
                    'valid' => false,
                    'errors' => ['Syntax check failed: ' . $result->errorOutput()],
                    'output' => $result->output()
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Exception: ' . $e->getMessage()],
                'output' => ''
            ];
        }
    }

    /**
     * Run dry-run (check mode) of playbook
     */
    public function dryRunPlaybook(string $playbookId): array
    {
        $playbookPath = $this->playbooksPath . '/' . $playbookId;
        
        try {
            $result = Process::path($playbookPath)
                ->timeout(300) // 5 minutes
                ->run("ansible-playbook -i inventory.ini --check --diff playbook.yml");
            
            return [
                'success' => $result->successful(),
                'output' => $result->output(),
                'error' => $result->successful() ? null : $result->errorOutput()
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to run Ansible dry-run', [
                'playbook_id' => $playbookId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'output' => ''
            ];
        }
    }

    /**
     * Execute playbook
     */
    public function executePlaybook(string $playbookId, array $options = []): array
    {
        $playbookPath = $this->playbooksPath . '/' . $playbookId;
        
        try {
            // Build command
            $command = "ansible-playbook -i inventory.ini";
            
            // Add options
            if (isset($options['verbose']) && $options['verbose']) {
                $command .= " -vvv";
            }
            
            if (isset($options['tags']) && !empty($options['tags'])) {
                $command .= " --tags " . implode(',', $options['tags']);
            }
            
            if (isset($options['skip_tags']) && !empty($options['skip_tags'])) {
                $command .= " --skip-tags " . implode(',', $options['skip_tags']);
            }
            
            if (isset($options['limit']) && !empty($options['limit'])) {
                $command .= " --limit " . $options['limit'];
            }
            
            $command .= " playbook.yml";
            
            // Execute playbook
            $result = Process::path($playbookPath)
                ->timeout(3600) // 60 minutes
                ->run($command);
            
            if ($result->successful()) {
                $this->updatePlaybookStatus($playbookId, 'executed');
                
                Log::info('Ansible playbook executed successfully', [
                    'playbook_id' => $playbookId
                ]);
                
                return [
                    'success' => true,
                    'output' => $result->output()
                ];
            } else {
                $this->updatePlaybookStatus($playbookId, 'failed');
                
                return [
                    'success' => false,
                    'error' => $result->errorOutput(),
                    'output' => $result->output()
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to execute Ansible playbook', [
                'playbook_id' => $playbookId,
                'error' => $e->getMessage()
            ]);
            
            $this->updatePlaybookStatus($playbookId, 'failed');
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'output' => ''
            ];
        }
    }

    /**
     * Create role structure
     */
    public function createRole(string $roleName, array $tasks = [], array $variables = []): array
    {
        try {
            $rolePath = $this->rolesPath . '/' . $roleName;
            
            // Create role directory structure
            $directories = ['tasks', 'handlers', 'templates', 'files', 'vars', 'defaults', 'meta'];
            
            foreach ($directories as $dir) {
                File::makeDirectory($rolePath . '/' . $dir, 0755, true);
            }
            
            // Create main tasks file
            $tasksContent = $this->generateTasksFile($tasks);
            File::put($rolePath . '/tasks/main.yml', $tasksContent);
            
            // Create defaults file
            $defaultsContent = Yaml::dump($variables, 2);
            File::put($rolePath . '/defaults/main.yml', $defaultsContent);
            
            // Create meta file
            $metaContent = $this->generateRoleMeta($roleName);
            File::put($rolePath . '/meta/main.yml', $metaContent);
            
            Log::info('Ansible role created', ['role_name' => $roleName]);
            
            return [
                'name' => $roleName,
                'path' => $rolePath,
                'created_at' => now()->toISOString()
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to create Ansible role', [
                'role_name' => $roleName,
                'error' => $e->getMessage()
            ]);
            
            throw new \RuntimeException('Failed to create role: ' . $e->getMessage());
        }
    }

    /**
     * Generate playbook content from template
     */
    private function generatePlaybook(string $template, array $hosts, array $variables): string
    {
        $templateConfig = $this->availablePlaybooks[$template] ?? [];
        $roles = $templateConfig['roles'] ?? [];
        
        $playbook = [
            [
                'name' => $templateConfig['name'] ?? 'DCParty Playbook',
                'hosts' => 'all',
                'become' => true,
                'gather_facts' => true,
                'vars' => $variables,
                'roles' => $roles
            ]
        ];
        
        return "---\n" . Yaml::dump($playbook, 4, 2);
    }

    /**
     * Generate inventory content
     */
    private function generateInventory(array $hosts): string
    {
        $inventory = "[dcparty]\n";
        
        foreach ($hosts as $host) {
            $line = $host['hostname'] ?? $host['ip'];
            
            if (isset($host['ip']) && $host['ip'] !== ($host['hostname'] ?? '')) {
                $line .= " ansible_host=" . $host['ip'];
            }
            
            if (isset($host['user'])) {
                $line .= " ansible_user=" . $host['user'];
            }
            
            if (isset($host['port'])) {
                $line .= " ansible_port=" . $host['port'];
            }
            
            if (isset($host['private_key'])) {
                $line .= " ansible_ssh_private_key_file=" . $host['private_key'];
            }
            
            $inventory .= $line . "\n";
        }
        
        // Add groups if needed
        if (count($hosts) > 1) {
            $inventory .= "\n[masters]\n";
            $inventory .= ($hosts[0]['hostname'] ?? $hosts[0]['ip']) . "\n";
            
            if (count($hosts) > 1) {
                $inventory .= "\n[workers]\n";
                for ($i = 1; $i < count($hosts); $i++) {
                    $inventory .= ($hosts[$i]['hostname'] ?? $hosts[$i]['ip']) . "\n";
                }
            }
        }
        
        return $inventory;
    }

    /**
     * Generate variable files
     */
    private function generateVariableFiles(string $playbookPath, array $hosts, array $variables): void
    {
        // Create group_vars directory
        $groupVarsPath = $playbookPath . '/group_vars';
        File::makeDirectory($groupVarsPath, 0755, true);
        
        // Create all.yml with common variables
        $allVars = array_merge([
            'project_name' => 'dcparty',
            'environment' => 'development'
        ], $variables);
        
        File::put($groupVarsPath . '/all.yml', "---\n" . Yaml::dump($allVars, 2));
        
        // Create host_vars directory if needed
        $hostVarsPath = $playbookPath . '/host_vars';
        File::makeDirectory($hostVarsPath, 0755, true);
        
        // Create individual host variable files if there are host-specific variables
        foreach ($hosts as $host) {
            if (isset($host['variables']) && !empty($host['variables'])) {
                $hostname = $host['hostname'] ?? $host['ip'];
                File::put($hostVarsPath . '/' . $hostname . '.yml', 
                         "---\n" . Yaml::dump($host['variables'], 2));
            }
        }
    }

    /**
     * Generate tasks file content
     */
    private function generateTasksFile(array $tasks): string
    {
        if (empty($tasks)) {
            $tasks = [
                [
                    'name' => 'Ensure system is updated',
                    'package' => [
                        'name' => '*',
                        'state' => 'latest'
                    ],
                    'when' => 'ansible_os_family == "Debian"'
                ]
            ];
        }
        
        return "---\n" . Yaml::dump($tasks, 4, 2);
    }

    /**
     * Generate role meta file
     */
    private function generateRoleMeta(string $roleName): string
    {
        $meta = [
            'galaxy_info' => [
                'author' => 'DCParty',
                'description' => 'DCParty role: ' . $roleName,
                'company' => 'DCParty',
                'license' => 'MIT',
                'min_ansible_version' => '2.9',
                'platforms' => [
                    [
                        'name' => 'Ubuntu',
                        'versions' => ['20.04', '22.04', '24.04']
                    ]
                ],
                'galaxy_tags' => ['dcparty', 'dcp', 'cinema']
            ],
            'dependencies' => []
        ];
        
        return "---\n" . Yaml::dump($meta, 4, 2);
    }

    /**
     * Create directory structure
     */
    private function createDirectoryStructure(): void
    {
        $directories = [
            $this->workingDir,
            $this->playbooksPath,
            $this->inventoriesPath,
            $this->rolesPath
        ];
        
        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }
    }

    /**
     * Update playbook status in metadata
     */
    private function updatePlaybookStatus(string $playbookId, string $status): void
    {
        $playbookPath = $this->playbooksPath . '/' . $playbookId;
        $metadataFile = $playbookPath . '/metadata.json';
        
        if (File::exists($metadataFile)) {
            $metadata = json_decode(File::get($metadataFile), true);
            $metadata['status'] = $status;
            $metadata['updated_at'] = now()->toISOString();
            
            File::put($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
        }
    }
}
