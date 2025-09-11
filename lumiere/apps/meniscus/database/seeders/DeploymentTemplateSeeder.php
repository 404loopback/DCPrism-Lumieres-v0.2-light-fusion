<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeploymentTemplate;
use App\Models\ProviderProfile;
use App\Models\User;

class DeploymentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user (should exist from UserSeeder)
        $user = User::first();
        if (!$user) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // Get provider profiles
        $localProfile = ProviderProfile::where('provider', 'local')->first();
        $vultrProfile = ProviderProfile::where('provider', 'vultr')->first();
        $awsProfile = ProviderProfile::where('provider', 'aws')->first();

        if (!$localProfile) {
            $this->command->error('No provider profiles found. Please run ProviderProfileSeeder first.');
            return;
        }

        // Create sample deployment templates
        $templates = [
            [
                'name' => 'Local Development Server',
                'description' => 'Template pour un serveur de développement local avec Docker',
                'scenario' => 'backend-automation',
                'provider_profile_id' => $localProfile->id,
                'machine_config' => [
                    'instance_type' => 'development',
                    'cpu_cores' => 2,
                    'memory_gb' => 4,
                    'storage_gb' => 20,
                    'docker_enabled' => true,
                    'environment' => 'development'
                ],
                'network_config' => [
                    'ports' => [8000, 3000, 5432, 6379],
                    'firewall_rules' => ['allow_local']
                ],
                'ansible_config' => [
                    'playbook' => 'setup-dev-server.yml',
                    'roles' => ['docker', 'nodejs', 'php'],
                    'vars' => [
                        'environment' => 'development',
                        'enable_xdebug' => true
                    ]
                ],
                'metadata' => [
                    'category' => 'development',
                    'tags' => ['local', 'development', 'docker']
                ],
                'is_active' => true,
                'is_favorite' => false,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]
        ];

        // Add Vultr template if profile exists
        if ($vultrProfile) {
            $templates[] = [
                'name' => 'Vultr Production Server',
                'description' => 'Template pour un serveur de production sur Vultr',
                'scenario' => 'backend-automation',
                'provider_profile_id' => $vultrProfile->id,
                'machine_config' => [
                    'instance_type' => 'vc2-2c-4gb',
                    'region' => 'ewr',
                    'os' => 'ubuntu-22.04',
                    'cpu_cores' => 2,
                    'memory_gb' => 4,
                    'storage_gb' => 80,
                    'backups' => true
                ],
                'network_config' => [
                    'ports' => [80, 443, 22],
                    'firewall_rules' => ['allow_http', 'allow_https', 'allow_ssh'],
                    'load_balancer' => false
                ],
                'ansible_config' => [
                    'playbook' => 'setup-production-server.yml',
                    'roles' => ['nginx', 'php-fpm', 'mysql', 'redis', 'certbot'],
                    'vars' => [
                        'environment' => 'production',
                        'ssl_enabled' => true,
                        'monitoring_enabled' => true
                    ]
                ],
                'metadata' => [
                    'category' => 'production',
                    'tags' => ['vultr', 'production', 'web-server']
                ],
                'is_active' => true,
                'is_favorite' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ];
        }

        // Add AWS template if profile exists
        if ($awsProfile) {
            $templates[] = [
                'name' => 'AWS Scalable Backend',
                'description' => 'Template pour un backend scalable sur AWS avec EC2 et RDS',
                'scenario' => 'backend-automation',
                'provider_profile_id' => $awsProfile->id,
                'machine_config' => [
                    'instance_type' => 't3.medium',
                    'region' => 'us-east-1',
                    'ami' => 'ami-0c7217cdde317cfec',
                    'cpu_cores' => 2,
                    'memory_gb' => 4,
                    'storage_gb' => 20,
                    'auto_scaling' => true,
                    'min_instances' => 1,
                    'max_instances' => 3
                ],
                'network_config' => [
                    'vpc_enabled' => true,
                    'subnets' => ['public', 'private'],
                    'load_balancer' => true,
                    'security_groups' => ['web', 'database']
                ],
                'ansible_config' => [
                    'playbook' => 'setup-aws-backend.yml',
                    'roles' => ['nginx', 'php-fpm', 'supervisor', 'cloudwatch'],
                    'vars' => [
                        'environment' => 'production',
                        'use_rds' => true,
                        'use_elasticache' => true,
                        'cloudwatch_enabled' => true
                    ]
                ],
                'metadata' => [
                    'category' => 'cloud-production',
                    'tags' => ['aws', 'production', 'scalable', 'backend']
                ],
                'is_active' => true,
                'is_favorite' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ];
        }

        // Create templates
        foreach ($templates as $templateData) {
            DeploymentTemplate::create($templateData);
        }

        $this->command->info('Created ' . count($templates) . ' deployment templates');
    }
}
