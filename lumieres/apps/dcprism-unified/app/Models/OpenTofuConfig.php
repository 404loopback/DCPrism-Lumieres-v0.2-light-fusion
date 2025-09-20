<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Fresnel\app\Services\OpenTofuManager;

class OpenTofuConfig extends Model
{
    use HasFactory;

    protected $table = 'open_tofu_configs';

    protected $fillable = [
        'name',
        'scenario',
        'provider',
        'variables',
        'status',
        'description',
        'region',
        'instance_count',
        'tags',
    ];

    protected $casts = [
        'variables' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the OpenTofuManager instance for this config
     */
    public function getOpenTofuManager(): OpenTofuManager
    {
        return app(OpenTofuManager::class);
    }

    /**
     * Generate terraform files for this configuration
     */
    public function generateFiles(): bool
    {
        $manager = $this->getOpenTofuManager();

        return $manager->createConfiguration(
            $this->name,
            $this->scenario,
            $this->provider,
            $this->variables ?? []
        );
    }

    /**
     * Deploy this configuration
     */
    public function deploy(): bool
    {
        $manager = $this->getOpenTofuManager();
        $result = $manager->deployConfiguration($this->name);

        if ($result) {
            $this->update(['status' => 'deployed']);
        }

        return $result;
    }

    /**
     * Destroy this configuration
     */
    public function destroyInfrastructure(): bool
    {
        $manager = $this->getOpenTofuManager();
        $result = $manager->destroyConfiguration($this->name);

        if ($result) {
            $this->update(['status' => 'destroyed']);
        }

        return $result;
    }
}
