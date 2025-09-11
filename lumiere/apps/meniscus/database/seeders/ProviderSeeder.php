<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'vultr',
                'display_name' => 'Vultr',
                'description' => 'High performance SSD cloud servers, compute instances and block storage',
                'config' => [
                    'required_fields' => ['api_key'],
                    'regions' => ['ewr', 'ord', 'dfw', 'sea', 'lax', 'atl', 'ams', 'lhr', 'fra', 'sjc', 'sgp', 'nrt', 'syd'],
                    'instance_types' => [
                        'vc2-1c-1gb' => '1 vCPU, 1GB RAM',
                        'vc2-1c-2gb' => '1 vCPU, 2GB RAM',
                        'vc2-2c-4gb' => '2 vCPU, 4GB RAM',
                        'vc2-4c-8gb' => '4 vCPU, 8GB RAM',
                        'vc2-6c-16gb' => '6 vCPU, 16GB RAM',
                        'vc2-8c-32gb' => '8 vCPU, 32GB RAM'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'aws',
                'display_name' => 'Amazon Web Services',
                'description' => 'Comprehensive cloud computing platform by Amazon',
                'config' => [
                    'required_fields' => ['access_key', 'secret_key', 'region'],
                    'regions' => ['us-east-1', 'us-west-2', 'eu-west-1', 'ap-southeast-1'],
                    'instance_types' => [
                        't3.micro' => '1 vCPU, 1GB RAM',
                        't3.small' => '2 vCPU, 2GB RAM',
                        't3.medium' => '2 vCPU, 4GB RAM',
                        't3.large' => '2 vCPU, 8GB RAM',
                        'm5.large' => '2 vCPU, 8GB RAM'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'digitalocean',
                'display_name' => 'DigitalOcean',
                'description' => 'Simple cloud computing, designed for developers',
                'config' => [
                    'required_fields' => ['token'],
                    'regions' => ['nyc1', 'nyc3', 'sfo3', 'ams3', 'sgp1', 'lon1', 'fra1', 'tor1', 'blr1'],
                    'instance_types' => [
                        's-1vcpu-1gb' => '1 vCPU, 1GB RAM',
                        's-1vcpu-2gb' => '1 vCPU, 2GB RAM',
                        's-2vcpu-2gb' => '2 vCPU, 2GB RAM',
                        's-2vcpu-4gb' => '2 vCPU, 4GB RAM',
                        's-4vcpu-8gb' => '4 vCPU, 8GB RAM'
                    ]
                ],
                'is_active' => true
            ]
        ];

        foreach ($providers as $provider) {
            Provider::create($provider);
        }

        echo "\u2705 Providers créés :\n";
        echo "☁\ufe0f  Vultr - " . Provider::where('name', 'vultr')->count() . " provider\n";
        echo "☁\ufe0f  AWS - " . Provider::where('name', 'aws')->count() . " provider\n";
        echo "☁\ufe0f  DigitalOcean - " . Provider::where('name', 'digitalocean')->count() . " provider\n";
    }
}
