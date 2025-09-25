<?php

namespace Modules\Fresnel\app\Services\VersionManagement;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\FestivalParameter;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Version;
use Modules\Fresnel\app\Services\Nomenclature\NomenclatureBuilder;
use Modules\Fresnel\app\Services\Nomenclature\NomenclatureRepository;
use Modules\Fresnel\app\Services\VersionManagement\DTOs\MovieVersionData;

/**
 * Service for creating movie versions based on festival configuration
 * Uses existing nomenclature system instead of complex auto-detection
 */
class MovieVersionService
{
    public function __construct(
        private NomenclatureBuilder $nomenclatureBuilder,
        private NomenclatureRepository $nomenclatureRepository
    ) {}

    /**
     * Get all festival parameters for version creation
     */
    public function getFestivalParametersData(Festival $festival): MovieVersionData
    {
        // Get all active festival parameters
        $festivalParameters = FestivalParameter::where('festival_id', $festival->id)
            ->where('is_enabled', true)
            ->with('parameter')
            ->ordered()
            ->get()
            ->filter(fn($fp) => $fp->parameter && $fp->parameter->is_active)
            ->map(function ($festivalParam) {
                return [
                    'id' => $festivalParam->parameter->id,
                    'name' => $festivalParam->parameter->name,
                    'code' => $festivalParam->parameter->code,
                    'category' => $festivalParam->parameter->category,
                    'type' => $festivalParam->parameter->type,
                    'is_required' => $festivalParam->parameter->is_required,
                    'possible_values' => $festivalParam->parameter->possible_values,
                    'default_value' => $festivalParam->getEffectiveDefaultValue(),
                    'description' => $festivalParam->parameter->description,
                    'festival_specific_notes' => $festivalParam->festival_specific_notes,
                ];
            })
            ->toArray();

        // Get nomenclature configuration
        $nomenclatureConfig = $this->nomenclatureRepository
            ->getActiveNomenclatures($festival)
            ->map(function ($nomenclature) {
                return [
                    'id' => $nomenclature->id,
                    'parameter_id' => $nomenclature->resolveParameter()?->id,
                    'parameter_name' => $nomenclature->resolveParameter()?->name,
                    'order_position' => $nomenclature->order_position,
                    'is_required' => $nomenclature->is_required,
                    'prefix' => $nomenclature->prefix,
                    'suffix' => $nomenclature->suffix,
                    'formatting_rules' => $nomenclature->formatting_rules,
                    'separator' => $nomenclature->separator,
                ];
            })
            ->toArray();

        return new MovieVersionData(
            festival: $festival,
            festival_parameters: $festivalParameters,
            nomenclature_config: $nomenclatureConfig,
            parameter_values: []
        );
    }

    /**
     * Preview what the version nomenclature will look like with given parameters
     */
    public function previewVersionNomenclature(
        Movie $movie,
        Festival $festival,
        array $parameterValues
    ): array {
        try {
            $preview = $this->nomenclatureBuilder->preview($movie, $festival, $parameterValues);
            
            return [
                'success' => true,
                'nomenclature' => $preview['final_nomenclature'] ?? 'Generation failed',
                'preview_parts' => $preview['preview_parts'] ?? [],
                'warnings' => $preview['warnings'] ?? [],
                'is_valid' => $preview['is_valid'] ?? false,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to preview version nomenclature', [
                'movie_id' => $movie->id,
                'festival_id' => $festival->id,
                'error' => $e->getMessage(),
                'parameters' => $parameterValues
            ]);

            return [
                'success' => false,
                'nomenclature' => 'Preview failed',
                'preview_parts' => [],
                'warnings' => ['Failed to generate preview: ' . $e->getMessage()],
                'is_valid' => false,
            ];
        }
    }

    /**
     * Create a version for a movie using festival configuration
     */
    public function createVersionForMovie(
        Movie $movie,
        Festival $festival,
        array $parameterValues
    ): Version {
        // Map parameter values from IDs to names if needed
        $mappedParameters = $this->mapParameterIdsToNames($parameterValues, $festival);
        
        $nomenclature = $this->nomenclatureBuilder->build($movie, $festival);
        
        // Determine version attributes from parameters
        $versionAttributes = $this->resolveVersionAttributes($mappedParameters);
        
        return Version::create([
            'movie_id' => $movie->id,
            'type' => $versionAttributes['type'],
            'audio_lang' => $versionAttributes['audio_lang'],
            'sub_lang' => $versionAttributes['sub_lang'],
            'accessibility' => $versionAttributes['accessibility'],
            'format' => $versionAttributes['format'],
            'generated_nomenclature' => $nomenclature,
        ]);
    }

    /**
     * Resolve version attributes from festival parameters
     * This replaces the complex auto-detection logic
     */
    private function resolveVersionAttributes(array $parameterValues): array
    {
        $attributes = [
            'type' => 'VO', // Default
            'audio_lang' => 'original',
            'sub_lang' => null,
            'accessibility' => null,
            'format' => 'FTR',
        ];

        // Resolve from specific parameters
        if (isset($parameterValues['audio_language'])) {
            $audioLang = $parameterValues['audio_language'];
            if ($audioLang === 'original' || $audioLang === 'vo') {
                $attributes['type'] = 'VO';
                $attributes['audio_lang'] = 'original';
            } else {
                $attributes['type'] = 'DUB';
                $attributes['audio_lang'] = $audioLang;
            }
        }

        if (isset($parameterValues['subtitle_language']) && !empty($parameterValues['subtitle_language'])) {
            $attributes['sub_lang'] = $parameterValues['subtitle_language'];
            // Adjust type to include subtitles
            $attributes['type'] = $attributes['type'] === 'VO' ? 'VOST' : 'DUBST';
        }

        if (isset($parameterValues['accessibility_features'])) {
            $attributes['accessibility'] = $parameterValues['accessibility_features'];
        }

        if (isset($parameterValues['content_format'])) {
            $attributes['format'] = $parameterValues['content_format'];
        }

        return $attributes;
    }

    /**
     * Map parameter IDs to parameter names for consistency
     */
    public function mapParameterIdsToNames(array $parameterValues, Festival $festival): array
    {
        $mappedValues = [];
        $festivalParams = $this->getFestivalParametersData($festival);
        
        // Create ID to name mapping
        $parameterMap = [];
        foreach ($festivalParams->festival_parameters as $param) {
            $parameterMap[$param['id']] = $param['name'];
        }
        
        // Map the values
        foreach ($parameterValues as $paramId => $value) {
            if (isset($parameterMap[$paramId])) {
                $parameterName = $parameterMap[$paramId];
                $mappedValues[$parameterName] = $value;
            } else {
                // If it's already a name, keep it as is
                $mappedValues[$paramId] = $value;
            }
        }
        
        return $mappedValues;
    }

    /**
     * Validate that festival has proper configuration for version creation
     */
    public function validateFestivalConfiguration(Festival $festival): array
    {
        $errors = [];
        $warnings = [];

        // Check if festival has active parameters
        $activeParams = FestivalParameter::where('festival_id', $festival->id)
            ->where('is_enabled', true)
            ->with('parameter')
            ->get()
            ->filter(fn($fp) => $fp->parameter && $fp->parameter->is_active);

        if ($activeParams->isEmpty()) {
            $errors[] = "No active parameters configured for festival '{$festival->name}'";
        }

        // Check if festival has nomenclature configuration
        $nomenclatures = $this->nomenclatureRepository->getActiveNomenclatures($festival);
        if ($nomenclatures->isEmpty()) {
            $warnings[] = "No nomenclature rules configured for festival '{$festival->name}'. Default nomenclature will be used.";
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'active_parameters_count' => $activeParams->count(),
            'nomenclature_rules_count' => $nomenclatures->count(),
        ];
    }
}
