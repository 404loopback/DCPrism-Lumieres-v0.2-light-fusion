<?php

namespace Modules\Fresnel\app\Services\VersionManagement\DTOs;

use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\FestivalParameter;
use Modules\Fresnel\app\Models\Nomenclature;

/**
 * Data Transfer Object for movie version creation
 * Contains all festival parameters and nomenclature configuration
 * Used to generate ONE complete version with proper nomenclature
 */
class MovieVersionData
{
    public function __construct(
        public readonly Festival $festival,
        public readonly array $festival_parameters, // Collection of FestivalParameter with values
        public readonly array $nomenclature_config,  // Collection of active Nomenclature rules
        public readonly array $parameter_values,     // User input values for parameters
        public readonly ?string $generated_nomenclature = null
    ) {}

    /**
     * Get all festival parameters as key-value pairs
     */
    public function getParameterValues(): array
    {
        return $this->parameter_values;
    }

    /**
     * Get a specific parameter value
     */
    public function getParameterValue(string $parameterName, mixed $default = null): mixed
    {
        return $this->parameter_values[$parameterName] ?? $default;
    }

    /**
     * Check if all required parameters have values
     */
    public function hasRequiredParameters(): bool
    {
        foreach ($this->festival_parameters as $festivalParam) {
            if ($festivalParam['is_required'] && empty($this->parameter_values[$festivalParam['name']])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get missing required parameters
     */
    public function getMissingRequiredParameters(): array
    {
        $missing = [];
        foreach ($this->festival_parameters as $festivalParam) {
            if ($festivalParam['is_required'] && empty($this->parameter_values[$festivalParam['name']])) {
                $missing[] = $festivalParam['name'];
            }
        }
        return $missing;
    }

    /**
     * Convert to array for storage or API response
     */
    public function toArray(): array
    {
        return [
            'festival_id' => $this->festival->id,
            'festival_name' => $this->festival->name,
            'festival_parameters' => $this->festival_parameters,
            'nomenclature_config' => $this->nomenclature_config,
            'parameter_values' => $this->parameter_values,
            'generated_nomenclature' => $this->generated_nomenclature,
            'has_required_parameters' => $this->hasRequiredParameters(),
            'missing_required_parameters' => $this->getMissingRequiredParameters(),
        ];
    }
}
