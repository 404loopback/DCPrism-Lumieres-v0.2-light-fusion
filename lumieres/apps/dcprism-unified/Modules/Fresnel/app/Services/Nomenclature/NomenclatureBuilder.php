<?php

namespace Modules\Fresnel\app\Services\Nomenclature;

use Illuminate\Support\Facades\Log;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Nomenclature;

/**
 * Focused service for building nomenclatures from configured parameters
 * Extracted from the bloated UnifiedNomenclatureService
 */
class NomenclatureBuilder
{
    public function __construct(
        private ParameterExtractor $parameterExtractor,
        private NomenclatureRepository $nomenclatureRepository
    ) {}

    /**
     * Build nomenclature for a movie in a festival
     */
    public function build(Movie $movie, Festival $festival): string
    {
        Log::info('[NomenclatureBuilder] Building nomenclature', [
            'movie_id' => $movie->id,
            'festival_id' => $festival->id,
            'movie_title' => $movie->title,
        ]);

        $nomenclatures = $this->nomenclatureRepository->getActiveNomenclatures($festival);

        if ($nomenclatures->isEmpty()) {
            Log::warning('[NomenclatureBuilder] No active nomenclatures found', [
                'festival_id' => $festival->id,
            ]);

            return $this->generateDefaultNomenclature($movie, $festival);
        }

        $parts = [];
        $missingRequired = [];

        foreach ($nomenclatures as $nomenclature) {
            $parameter = $nomenclature->resolveParameter();
            if (!$parameter) {
                continue;
            }
            
            $value = $this->parameterExtractor->getParameterValueForMovie(
                $movie,
                $parameter
            );

            if ($nomenclature->is_required && empty($value)) {
                $missingRequired[] = $parameter->name;

                continue;
            }

            if (! empty($value)) {
                $formatted = $nomenclature->formatValue($value, $movie);
                if (! empty($formatted)) {
                    $parts[] = $formatted;
                }
            }
        }

        if (! empty($missingRequired)) {
            Log::warning('[NomenclatureBuilder] Missing required parameters', [
                'movie_id' => $movie->id,
                'missing' => $missingRequired,
            ]);
        }

        $result = ! empty($parts)
            ? implode('_', $parts)
            : $this->generateDefaultNomenclature($movie, $festival);

        Log::info('[NomenclatureBuilder] Nomenclature built', [
            'movie_id' => $movie->id,
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Preview nomenclature with custom parameter values
     */
    public function preview(Movie $movie, Festival $festival, array $parameterValues = []): array
    {
        $nomenclatures = $this->nomenclatureRepository->getActiveNomenclatures($festival);
        $preview = [];
        $parts = [];
        $warnings = [];

        foreach ($nomenclatures as $nomenclature) {
            $parameter = $nomenclature->resolveParameter();
            if (!$parameter) {
                continue;
            }
            
            $paramName = $parameter->name;

            $value = $parameterValues[$paramName] ??
                     $this->parameterExtractor->getParameterValueForMovie($movie, $parameter);

            $formatted = $nomenclature->formatValue($value, $movie);

            $preview[] = [
                'parameter' => $paramName,
                'raw_value' => $value,
                'formatted_value' => $formatted,
                'is_required' => $nomenclature->is_required,
                'order' => $nomenclature->order_position,
                'prefix' => $nomenclature->prefix,
                'suffix' => $nomenclature->suffix,
            ];

            if ($nomenclature->is_required && empty($value)) {
                $warnings[] = "Required parameter missing: {$paramName}";
            } elseif (! empty($formatted)) {
                $parts[] = $formatted;
            }
        }

        $finalNomenclature = ! empty($parts)
            ? implode('_', $parts)
            : $this->generateDefaultNomenclature($movie, $festival);

        return [
            'preview_parts' => $preview,
            'final_nomenclature' => $finalNomenclature,
            'warnings' => $warnings,
            'is_valid' => empty($warnings),
        ];
    }

    /**
     * Generate a default nomenclature when no configuration exists
     */
    private function generateDefaultNomenclature(Movie $movie, Festival $festival): string
    {
        $date = now()->format('Ymd');
        $festivalCode = strtoupper(substr($festival->name, 0, 3));

        return "{$movie->title}_{$festivalCode}_{$date}";
    }
}
