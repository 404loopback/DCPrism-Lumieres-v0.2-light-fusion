<?php

namespace App\Contracts;

interface CloudProviderInterface
{
    /**
     * Deploy workers based on queue analysis
     */
    public function deployWorkers(array $config): array;

    /**
     * Get current worker status
     */
    public function getWorkerStatus(): array;

    /**
     * Terminate specific workers
     */
    public function terminateWorkers(array $workerIds): bool;

    /**
     * Get provider pricing information
     */
    public function getPricing(): array;

    /**
     * Check provider availability in region
     */
    public function checkAvailability(string $region): bool;

    /**
     * Get available instance types
     */
    public function getAvailableInstanceTypes(): array;

    /**
     * Get available regions
     */
    public function getAvailableRegions(): array;

    /**
     * Calculate estimated cost
     */
    public function calculateCost(array $config): float;

    /**
     * Validate provider configuration
     */
    public function validateConfig(array $config): array;

    /**
     * Test provider connectivity
     */
    public function testConnection(): bool;
}
