<?php

namespace Modules\Fresnel\app\Services\VersionManagement\Contracts;

use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Version;
use Modules\Fresnel\app\Services\VersionManagement\DTOs\MovieVersionData;

/**
 * Interface for movie version management services
 */
interface MovieVersionServiceInterface
{
    /**
     * Get all festival parameters and nomenclature configuration for version creation
     */
    public function getFestivalParametersData(Festival $festival): MovieVersionData;

    /**
     * Preview what the version nomenclature will look like with given parameters
     */
    public function previewVersionNomenclature(
        Movie $movie,
        Festival $festival,
        array $parameterValues
    ): array;

    /**
     * Create a version for a movie using festival configuration
     */
    public function createVersionForMovie(
        Movie $movie,
        Festival $festival,
        array $parameterValues
    ): Version;

    /**
     * Validate that festival has proper configuration for version creation
     */
    public function validateFestivalConfiguration(Festival $festival): array;
}
