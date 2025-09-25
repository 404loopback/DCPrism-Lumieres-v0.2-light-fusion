<?php

namespace Modules\Fresnel\app\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Fresnel\app\Http\Controllers\Controller;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Services\Context\FestivalContextService;
use Modules\Fresnel\app\Services\VersionManagement\MovieVersionService;

/**
 * API Controller for version preview functionality
 */
class VersionPreviewController extends Controller
{
    public function __construct(
        private MovieVersionService $versionService,
        private FestivalContextService $festivalContext
    ) {}

    /**
     * Preview what the version nomenclature will look like
     */
    public function previewVersion(Request $request): JsonResponse
    {
        try {
            // Validation
            $request->validate([
                'title' => 'required|string|max:255',
                'parameters' => 'array',
            ]);

            // Get current festival
            $festival = $this->festivalContext->getCurrentFestival();
            if (!$festival) {
                return response()->json([
                    'success' => false,
                    'message' => 'No festival selected. Please select a festival first.',
                ], 400);
            }

            // Create a mock movie for preview
            $mockMovie = new Movie(['title' => $request->title]);

            // Map parameter values by parameter name instead of ID
            $parameterValues = $this->mapParameterValues($request->parameters ?? [], $festival);

            // Get nomenclature preview
            $preview = $this->versionService->previewVersionNomenclature(
                $mockMovie,
                $festival,
                $parameterValues
            );

            // Resolve version attributes for display
            $versionAttributes = $this->resolveVersionAttributes($parameterValues);

            return response()->json([
                'success' => $preview['success'],
                'preview' => $preview,
                'version_attributes' => $versionAttributes,
                'festival' => [
                    'id' => $festival->id,
                    'name' => $festival->name,
                ],
                'parameters_received' => $parameterValues,
            ]);

        } catch (\Exception $e) {
            Log::error('Version preview failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Preview generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Map parameter values from parameter IDs to parameter names
     */
    private function mapParameterValues(array $parameterIds, $festival): array
    {
        $parameterValues = [];
        
        // Get festival parameters to map IDs to names
        $festivalParams = $this->versionService->getFestivalParametersData($festival);
        $parameterMap = [];
        
        foreach ($festivalParams->festival_parameters as $param) {
            $parameterMap[$param['id']] = $param['name'];
        }

        // Map the values
        foreach ($parameterIds as $paramId => $value) {
            if (isset($parameterMap[$paramId])) {
                $parameterName = $parameterMap[$paramId];
                $parameterValues[$parameterName] = $value;
            }
        }

        return $parameterValues;
    }

    /**
     * Resolve version attributes for preview display
     * This mirrors the logic in MovieVersionService::resolveVersionAttributes
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
}
