<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProviderProfile;

class ProviderProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Profils Vultr
        ProviderProfile::create([
            'name' => 'Vultr Development',
            'provider' => 'vultr',
            'credentials' => [
                'api_key' => 'YOUR_VULTR_DEV_API_KEY_HERE'
            ],
            'default_config' => [
                'region' => 'fra',
                'master_type' => 'medium',
                'worker_type' => 'small'
            ],
            'description' => 'Profil Vultr pour l\'environnement de développement',
            'is_default' => true,
            'is_active' => true
        ]);

        ProviderProfile::create([
            'name' => 'Vultr Production',
            'provider' => 'vultr',
            'credentials' => [
                'api_key' => 'YOUR_VULTR_PROD_API_KEY_HERE'
            ],
            'default_config' => [
                'region' => 'fra',
                'master_type' => 'large',
                'worker_type' => 'medium'
            ],
            'description' => 'Profil Vultr pour l\'environnement de production',
            'is_default' => false,
            'is_active' => true
        ]);

        // Profils AWS
        ProviderProfile::create([
            'name' => 'AWS US-East Development',
            'provider' => 'aws',
            'credentials' => [
                'access_key' => 'YOUR_AWS_ACCESS_KEY_HERE',
                'secret_key' => 'YOUR_AWS_SECRET_KEY_HERE'
            ],
            'default_config' => [
                'region' => 'us-east-1',
                'master_type' => 'medium',
                'worker_type' => 'small'
            ],
            'description' => 'Profil AWS US-East pour le développement',
            'is_default' => true,
            'is_active' => true
        ]);

        ProviderProfile::create([
            'name' => 'AWS EU-Central Production',
            'provider' => 'aws',
            'credentials' => [
                'access_key' => 'YOUR_AWS_EU_ACCESS_KEY_HERE',
                'secret_key' => 'YOUR_AWS_EU_SECRET_KEY_HERE'
            ],
            'default_config' => [
                'region' => 'eu-central-1',
                'master_type' => 'large',
                'worker_type' => 'medium'
            ],
            'description' => 'Profil AWS EU-Central pour la production',
            'is_default' => false,
            'is_active' => true
        ]);

        // Profil GCP
        ProviderProfile::create([
            'name' => 'GCP Development',
            'provider' => 'gcp',
            'credentials' => [
                'credentials_file' => '/path/to/gcp-service-account.json'
            ],
            'default_config' => [
                'project_id' => 'your-gcp-project-id',
                'region' => 'us-central1',
                'zone' => 'us-central1-a',
                'master_type' => 'medium',
                'worker_type' => 'small'
            ],
            'description' => 'Profil GCP pour le développement',
            'is_default' => true,
            'is_active' => true
        ]);

        // Profil Local
        ProviderProfile::create([
            'name' => 'Local Development',
            'provider' => 'local',
            'credentials' => [],
            'default_config' => [
                'region' => 'local',
                'master_type' => 'medium',
                'worker_type' => 'small'
            ],
            'description' => 'Profil pour développement local avec Docker',
            'is_default' => true,
            'is_active' => true
        ]);
    }
}
