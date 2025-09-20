<?php

namespace Modules\Meniscus\app\Services;

use App\Contracts\CloudProviderInterface;
use App\Services\Providers\AwsProvider;
use App\Services\Providers\AzureProvider;
use App\Services\Providers\GcpProvider;
use App\Services\Providers\VultrProvider;
use InvalidArgumentException;

class CloudProviderFactory
{
    /**
     * Available cloud providers
     */
    const PROVIDERS = [
        'vultr' => VultrProvider::class,
        'aws' => AwsProvider::class,
        'azure' => AzureProvider::class,
        'gcp' => GcpProvider::class,
    ];

    /**
     * Create a cloud provider instance
     */
    public function create(string $provider, array $config = []): CloudProviderInterface
    {
        if (! array_key_exists($provider, self::PROVIDERS)) {
            throw new InvalidArgumentException("Unsupported cloud provider: {$provider}");
        }

        $providerClass = self::PROVIDERS[$provider];

        return new $providerClass($config);
    }

    /**
     * Get list of available providers
     */
    public function getAvailableProviders(): array
    {
        return array_keys(self::PROVIDERS);
    }

    /**
     * Check if a provider is supported
     */
    public function isProviderSupported(string $provider): bool
    {
        return array_key_exists($provider, self::PROVIDERS);
    }

    /**
     * Get provider display names
     */
    public function getProviderDisplayNames(): array
    {
        return [
            'vultr' => 'Vultr',
            'aws' => 'Amazon Web Services (AWS)',
            'azure' => 'Microsoft Azure',
            'gcp' => 'Google Cloud Platform',
        ];
    }

    /**
     * Get provider priorities for auto-selection
     * Lower number = higher priority
     */
    public function getProviderPriorities(): array
    {
        return [
            'vultr' => 1,  // Primary - free egress to B2
            'aws' => 2,    // Secondary - reliable but costs
            'gcp' => 3,    // Tertiary - good performance
            'azure' => 4,  // Backup option
        ];
    }

    /**
     * Select best provider based on criteria
     */
    public function selectBestProvider(array $criteria = []): string
    {
        $providers = $this->getAvailableProviders();
        $priorities = $this->getProviderPriorities();

        // Default criteria
        $defaultCriteria = [
            'region' => 'us-east-1',
            'cost_optimization' => true,
            'availability_check' => true,
        ];

        $criteria = array_merge($defaultCriteria, $criteria);

        // Filter providers based on availability
        $availableProviders = [];
        foreach ($providers as $provider) {
            try {
                $providerInstance = $this->create($provider);
                if ($criteria['availability_check'] &&
                    $providerInstance->checkAvailability($criteria['region'])) {
                    $availableProviders[$provider] = $priorities[$provider] ?? 999;
                }
            } catch (\Exception $e) {
                // Skip unavailable providers
                continue;
            }
        }

        if (empty($availableProviders)) {
            throw new \RuntimeException('No cloud providers available');
        }

        // Sort by priority (lower number = higher priority)
        asort($availableProviders);

        return array_key_first($availableProviders);
    }
}
