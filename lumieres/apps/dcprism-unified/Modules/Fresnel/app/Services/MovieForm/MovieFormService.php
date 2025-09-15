<?php

namespace Modules\Fresnel\app\Services\MovieForm;

use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\MovieParameter;
use Modules\Fresnel\app\Models\FestivalParameter;
use Modules\Fresnel\app\Filament\Builders\FormFieldBuilder;
use Modules\Fresnel\app\Services\Context\FestivalContextService;
use Modules\Fresnel\app\Services\UnifiedNomenclatureService;
use Illuminate\Support\Facades\Log;

/**
 * Service to handle movie form creation and parameter management
 * Extracted from the bloated MovieResource.php
 */
class MovieFormService
{
    public function __construct(
        private FormFieldBuilder $fieldBuilder,
        private FestivalContextService $festivalContext,
        private UnifiedNomenclatureService $nomenclatureService
    ) {}

    /**
     * Build version parameter fields for the current festival
     */
    public function buildVersionParametersFields(array $festivalIds = []): array
    {
        return $this->fieldBuilder->buildParameterFields($festivalIds);
    }

    /**
     * Build metadata parameter fields
     */
    public function buildMetadataParametersFields(): array
    {
        return $this->fieldBuilder->buildMetadataFields();
    }

    /**
     * Generate real-time nomenclature for preview
     */
    public function generateRealtimeNomenclature(callable $get): ?string
    {
        try {
            $festivalId = $this->festivalContext->getCurrentFestivalId();
            $title = $get('../../title'); // Access movie title from upper level
            
            if (!$festivalId || !$title) {
                return 'Please select festival and enter title';
            }
            
            $festival = $this->festivalContext->getCurrentFestival();
            if (!$festival) {
                return 'Festival not found';
            }
            
            // Create a mock movie for nomenclature generation
            $mockMovie = new Movie(['title' => $title]);
            
            // Get parameter values from form
            $parameters = $this->extractParametersFromForm($get, $festivalId);
            
            // Generate nomenclature preview
            $nomenclature = $this->nomenclatureService->previewNomenclature(
                $mockMovie,
                $festival,
                $parameters
            );
            
            return $nomenclature['final_nomenclature'] ?? $title . '_Generated';
            
        } catch (\Exception $e) {
            Log::warning('Failed to generate realtime nomenclature', [
                'error' => $e->getMessage(),
                'festival_id' => $this->festivalContext->getCurrentFestivalId()
            ]);
            
            return 'Generation failed';
        }
    }

    /**
     * Associate movie with current festival
     */
    public function associateMovieToFestivals(Movie $movie, array $data): void
    {
        $festival = $this->festivalContext->getCurrentFestival();
        
        if (!$festival) {
            Log::warning('No festival selected when associating movie', [
                'movie_id' => $movie->id
            ]);
            return;
        }
        
        $movie->festivals()->attach($festival->id, [
            'submission_status' => 'created',
            'selected_versions' => null,
            'priority' => 1,
        ]);
        
        Log::info('Movie associated with festival', [
            'movie_id' => $movie->id,
            'festival_id' => $festival->id
        ]);
    }

    /**
     * Create movie parameters from form data
     */
    public function createMovieParametersFromFormData(Movie $movie, array $data): void
    {
        $festival = $this->festivalContext->getCurrentFestival();
        
        if (!$festival) {
            Log::warning('No festival context when creating movie parameters', [
                'movie_id' => $movie->id
            ]);
            return;
        }
        
        $festivalParameters = FestivalParameter::where('festival_id', $festival->id)
            ->where('is_enabled', true)
            ->with('parameter')
            ->get();
        
        foreach ($festivalParameters as $festivalParameter) {
            $parameter = $festivalParameter->parameter;
            if (!$parameter?->is_active) {
                continue;
            }
            
            $fieldName = "parameter_{$parameter->id}";
            $value = $data[$fieldName] ?? null;
            
            // Use default value if none provided
            if ($value === null || $value === '') {
                $value = $festivalParameter->getEffectiveDefaultValue();
            }
            
            // Create MovieParameter if value is defined
            if ($value !== null && $value !== '') {
                MovieParameter::create([
                    'movie_id' => $movie->id,
                    'parameter_id' => $parameter->id,
                    'value' => $value,
                    'status' => MovieParameter::STATUS_VALIDATED,
                    'extraction_method' => MovieParameter::EXTRACTION_MANUAL
                ]);
            }
        }
        
        Log::info('Movie parameters created', [
            'movie_id' => $movie->id,
            'parameters_count' => $festivalParameters->count()
        ]);
    }

    /**
     * Extract parameter values from form data
     */
    private function extractParametersFromForm(callable $get, int $festivalId): array
    {
        $parameters = [];
        
        $festivalParameters = FestivalParameter::where('festival_id', $festivalId)
            ->where('is_enabled', true)
            ->with('parameter')
            ->get();
            
        foreach ($festivalParameters as $festivalParameter) {
            $parameter = $festivalParameter->parameter;
            if (!$parameter?->is_active) {
                continue;
            }
            
            $fieldName = "parameter_{$parameter->id}";
            $value = $get($fieldName);
            
            if ($value !== null) {
                $parameters[$parameter->name] = $value;
            }
        }
        
        return $parameters;
    }

    // Méthodes de détection automatique supprimées - on demande explicitement 
    // le type, langue audio et sous-titres lors de la création des versions

    /**
     * Generate nomenclature for a version
     */
    public function generateVersionNomenclature(Movie $movie, array $versionData): string
    {
        $festival = $this->festivalContext->getCurrentFestival();
        
        if (!$festival) {
            return $movie->title . '_' . ($versionData['name'] ?? 'Unknown');
        }
        
        try {
            return $this->nomenclatureService->generateMovieNomenclature($movie, $festival);
        } catch (\Exception $e) {
            Log::warning('Failed to generate version nomenclature', [
                'movie_id' => $movie->id,
                'festival_id' => $festival->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to simple nomenclature
            return $movie->title . '_' . ($versionData['name'] ?? 'Unknown') . '_' . now()->format('Ymd');
        }
    }
}
