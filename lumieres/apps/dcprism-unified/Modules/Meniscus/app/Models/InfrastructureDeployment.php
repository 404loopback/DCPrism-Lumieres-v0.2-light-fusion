<?php

namespace Modules\Meniscus\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Fresnel\app\Models\User;

class InfrastructureDeployment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'scenario',
        'environment',
        'project_name',
        'status',
        'terraform_config',
        'terraform_state',
        'terraform_outputs',
        'provider_config',
        'deployment_logs',
        'resource_details',
        'estimated_cost',
        'deployed_at',
        'destroyed_at',
    ];

    protected $casts = [
        'terraform_config' => 'array',
        'terraform_state' => 'array', 
        'terraform_outputs' => 'array',
        'provider_config' => 'array',
        'deployment_logs' => 'array',
        'resource_details' => 'array',
        'estimated_cost' => 'decimal:2',
        'deployed_at' => 'datetime',
        'destroyed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PLANNING = 'planning';
    const STATUS_DEPLOYING = 'deploying'; 
    const STATUS_DEPLOYED = 'deployed';
    const STATUS_FAILED = 'failed';
    const STATUS_DESTROYING = 'destroying';
    const STATUS_DESTROYED = 'destroyed';

    // Scenario constants
    const SCENARIO_BACKEND_AUTOMATION = 'backend-automation';
    const SCENARIO_MANUAL_TESTING = 'manual-testing';

    // Environment constants
    const ENV_DEVELOPMENT = 'development';
    const ENV_STAGING = 'staging';
    const ENV_PRODUCTION = 'production';

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes and Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PLANNING => 'blue',
            self::STATUS_DEPLOYING => 'orange',
            self::STATUS_DEPLOYED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_DESTROYING => 'orange',
            self::STATUS_DESTROYED => 'gray',
            default => 'gray',
        };
    }

    public function getScenarioLabelAttribute(): string
    {
        return match ($this->scenario) {
            self::SCENARIO_BACKEND_AUTOMATION => 'Backend Automation',
            self::SCENARIO_MANUAL_TESTING => 'Manual Testing',
            default => ucfirst(str_replace('-', ' ', $this->scenario)),
        };
    }

    public function getEnvironmentColorAttribute(): string
    {
        return match ($this->environment) {
            self::ENV_DEVELOPMENT => 'blue',
            self::ENV_STAGING => 'orange',
            self::ENV_PRODUCTION => 'red',
            default => 'gray',
        };
    }

    /**
     * Helper methods
     */
    public function isDeployed(): bool
    {
        return $this->status === self::STATUS_DEPLOYED;
    }

    public function canDestroy(): bool
    {
        return in_array($this->status, [self::STATUS_DEPLOYED, self::STATUS_FAILED]);
    }

    public function canRedeploy(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_DESTROYED]);
    }

    public function getAccessUrls(): array
    {
        $outputs = $this->terraform_outputs ?? [];
        
        if (!isset($outputs['access_urls'])) {
            return [];
        }

        return $outputs['access_urls'];
    }

    public function getResourceCount(): int
    {
        $details = $this->resource_details ?? [];
        return count($details);
    }

    /**
     * Generate Terraform configuration
     */
    public function generateTerraformConfig(): array
    {
        $config = [
            'project_name' => $this->project_name,
            'environment' => $this->environment,
            'scenario' => $this->scenario,
        ];

        // Add provider specific config
        if ($this->provider_config) {
            $config = array_merge($config, $this->provider_config);
        }

        return $config;
    }
}
