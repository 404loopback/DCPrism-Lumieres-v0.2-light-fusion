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

            return 'NO_NOMENCLATURE_CONFIG';
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


            // Si pas de valeur, utiliser le code du paramètre pour TOUS les paramètres
            if (empty($value)) {
                $value = $parameter->code ?? 'PARAM';
                if ($nomenclature->is_required) {
                    $missingRequired[] = $parameter->name;
                }
            }

            // Appliquer les règles de formatage du paramètre
            $value = $this->applyParameterFormatting($parameter, $value);

            // Toujours ajouter le paramètre (maintenant qu'il a forcément une valeur)
            $formatted = $nomenclature->formatParameterValue($value, $movie);
            if (! empty($formatted)) {
                $parts[] = $formatted;
            }
        }

        if (! empty($missingRequired)) {
            Log::warning('[NomenclatureBuilder] Missing required parameters', [
                'movie_id' => $movie->id,
                'missing' => $missingRequired,
            ]);
        }

        $result = implode('_', $parts);

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

            // MÊME LOGIQUE QUE build() : utiliser le code si pas de valeur
            if (empty($value)) {
                $value = $parameter->code ?? 'PARAM';
                if ($nomenclature->is_required) {
                    $warnings[] = "Required parameter missing: {$paramName}";
                }
            }

            // Appliquer les règles de formatage du paramètre
            $value = $this->applyParameterFormatting($parameter, $value);

            $formatted = $nomenclature->formatParameterValue($value, $movie);

            $preview[] = [
                'parameter' => $paramName,
                'raw_value' => $value,
                'formatted_value' => $formatted,
                'is_required' => $nomenclature->is_required,
                'order' => $nomenclature->order_position,
                'prefix' => $nomenclature->prefix,
                'suffix' => $nomenclature->suffix,
            ];

            // Toujours ajouter maintenant qu'on a forcément une valeur
            if (! empty($formatted)) {
                $parts[] = $formatted;
            }
        }

        $finalNomenclature = implode('_', $parts);

        return [
            'preview_parts' => $preview,
            'final_nomenclature' => $finalNomenclature,
            'warnings' => $warnings,
            'is_valid' => empty($warnings),
        ];
    }

    /**
     * Appliquer les règles de formatage du paramètre
     * Utilise le nouveau système de format_rules ou fallback vers nettoyage basique
     */
    private function applyParameterFormatting($parameter, string $value): string
    {
        // Si le paramètre a des règles de formatage définies, les utiliser
        if ($parameter && !empty($parameter->format_rules)) {
            try {
                $formatted = $parameter->applyFormatting($value);
                Log::debug('[NomenclatureBuilder] Applied parameter formatting', [
                    'parameter_id' => $parameter->id,
                    'rules' => $parameter->format_rules,
                    'original' => $value,
                    'formatted' => $formatted,
                ]);
                return $formatted;
            } catch (\Exception $e) {
                Log::warning('[NomenclatureBuilder] Failed to apply parameter formatting, using fallback', [
                    'parameter_id' => $parameter->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Fallback : nettoyage basique pour la compatibilité
        return $this->fallbackCleanValue($value);
    }
    
    /**
     * Nettoyage basique de fallback (ancien comportement)
     */
    private function fallbackCleanValue(string $value): string
    {
        // Supprimer les espaces
        $cleaned = str_replace(' ', '', $value);
        
        // Supprimer les accents et caractères spéciaux
        $cleaned = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cleaned);
        
        // Supprimer tout ce qui n'est pas alphaumérique ou underscore
        $cleaned = preg_replace('/[^A-Za-z0-9_]/', '', $cleaned);
        
        return $cleaned ?: 'CLEAN';
    }

}
