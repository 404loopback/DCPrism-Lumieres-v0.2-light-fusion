<?php

namespace Modules\Fresnel\app\Services;

use Modules\Fresnel\app\Models\{Festival, Parameter, Nomenclature, Movie, MovieParameter};
use Modules\Fresnel\app\Services\Nomenclature\{NomenclatureBuilder, ParameterExtractor, NomenclatureRepository};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class UnifiedNomenclatureService
{
    public function __construct(
        private NomenclatureBuilder $nomenclatureBuilder,
        private ParameterExtractor $parameterExtractor,
        private NomenclatureRepository $nomenclatureRepository
    ) {}

    /**
     * Générer la nomenclature complète pour un film dans un festival
     */
    public function generateMovieNomenclature(Movie $movie, Festival $festival): string
    {
        return $this->nomenclatureBuilder->build($movie, $festival);
    }

    /**
     * Configurer la nomenclature pour un festival
     */
    public function configureFestivalNomenclature(
        Festival $festival, 
        array $parameterConfigs
    ): array {
        try {
            DB::beginTransaction();

            // Désactiver toutes les nomenclatures existantes
            Nomenclature::where('festival_id', $festival->id)
                       ->update(['is_active' => false]);

            $createdNomenclatures = [];
            
            foreach ($parameterConfigs as $config) {
                $this->validateParameterConfig($config);
                
                $parameter = $this->findOrCreateParameter($config);
                
                $nomenclature = Nomenclature::create([
                    'festival_id' => $festival->id,
                    'parameter_id' => $parameter->id,
                    'order_position' => $config['order_position'],
                    'separator' => $config['separator'] ?? '_',
                    'is_active' => true,
                    'is_required' => $config['is_required'] ?? false,
                    'prefix' => $config['prefix'] ?? null,
                    'suffix' => $config['suffix'] ?? null,
                    'default_value' => $config['default_value'] ?? null,
                    'formatting_rules' => $config['formatting_rules'] ?? null,
                    'conditional_rules' => $config['conditional_rules'] ?? null
                ]);

                $createdNomenclatures[] = $nomenclature;
            }

            DB::commit();

            // Invalider le cache
            $this->nomenclatureRepository->clearCache($festival);

            Log::info('[NomenclatureService] Festival nomenclature configured', [
                'festival_id' => $festival->id,
                'nomenclatures_count' => count($createdNomenclatures)
            ]);

            return [
                'success' => true,
                'nomenclatures' => $createdNomenclatures,
                'message' => 'Nomenclature configurée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('[NomenclatureService] Failed to configure nomenclature', [
                'festival_id' => $festival->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Prévisualiser la nomenclature pour un film
     */
    public function previewNomenclature(
        Movie $movie, 
        Festival $festival, 
        array $parameterValues = []
    ): array {
        return $this->nomenclatureBuilder->preview($movie, $festival, $parameterValues);
    }

    /**
     * Extraire automatiquement les paramètres depuis les métadonnées DCP
     */
    public function extractParametersFromDcp(Movie $movie): array
    {
        return $this->parameterExtractor->extractFromDcpMetadata($movie);
    }

    /**
     * Valider la conformité d'une nomenclature
     */
    public function validateNomenclature(string $nomenclature, Festival $festival): array
    {
        $rules = $festival->nomenclatures()->where('is_active', true)->get();
        $issues = [];
        $suggestions = [];

        // Validation de la longueur
        if (strlen($nomenclature) > 255) {
            $issues[] = 'Nomenclature trop longue (max 255 caractères)';
        }

        // Validation des caractères
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $nomenclature)) {
            $issues[] = 'Caractères non autorisés détectés';
            $suggestions[] = 'Utiliser uniquement: lettres, chiffres, _, -, .';
        }

        // Validation des parties obligatoires
        $parts = explode('_', $nomenclature);
        $requiredCount = $rules->where('is_required', true)->count();
        
        if (count($parts) < $requiredCount) {
            $issues[] = "Nombre insuffisant de parties (requis: $requiredCount, trouvé: " . count($parts) . ")";
        }

        // Suggestions d'amélioration
        if (count($parts) > 5) {
            $suggestions[] = 'Considérer simplifier la nomenclature (trop de parties)';
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'suggestions' => $suggestions,
            'analyzed_parts' => $parts,
            'score' => $this->calculateNomenclatureScore($nomenclature, $issues, $suggestions)
        ];
    }

    /**
     * Obtenir les statistiques de nomenclature pour un festival
     */
    public function getNomenclatureStats(Festival $festival): array
    {
        $movies = $festival->movies()->with(['movieParameters.parameter'])->get();
        
        $stats = [
            'total_movies' => $movies->count(),
            'movies_with_complete_nomenclature' => 0,
            'movies_with_partial_nomenclature' => 0,
            'movies_without_nomenclature' => 0,
            'most_used_parameters' => [],
            'parameter_completion_rate' => [],
            'nomenclature_patterns' => []
        ];

        $parameterUsage = [];
        $nomenclaturePatterns = [];

        foreach ($movies as $movie) {
            $nomenclature = $this->generateMovieNomenclature($movie, $festival);
            $preview = $this->previewNomenclature($movie, $festival);
            
            // Pattern analysis
            $pattern = $this->analyzeNomenclaturePattern($nomenclature);
            $nomenclaturePatterns[$pattern] = ($nomenclaturePatterns[$pattern] ?? 0) + 1;

            // Completion analysis
            $completionStatus = $this->analyzeCompletionStatus($preview);
            $stats[$completionStatus]++;

            // Parameter usage
            foreach ($movie->movieParameters as $movieParam) {
                $paramName = $movieParam->parameter->name;
                $parameterUsage[$paramName] = ($parameterUsage[$paramName] ?? 0) + 1;
            }
        }

        $stats['most_used_parameters'] = collect($parameterUsage)
            ->sortDesc()
            ->take(10)
            ->toArray();

        $stats['nomenclature_patterns'] = collect($nomenclaturePatterns)
            ->sortDesc()
            ->take(5)
            ->toArray();

        // Calculate parameter completion rates
        foreach ($this->nomenclatureRepository->getActiveNomenclatures($festival) as $nomenclature) {
            $paramName = $nomenclature->parameter->name;
            $completionRate = ($parameterUsage[$paramName] ?? 0) / max($movies->count(), 1) * 100;
            $stats['parameter_completion_rate'][$paramName] = round($completionRate, 1);
        }

        return $stats;
    }

    // Méthodes privées utilitaires



    private function generateDefaultNomenclature(Movie $movie, Festival $festival): string
    {
        $safeName = Str::slug($movie->title, '_');
        $timestamp = Carbon::now()->format('Ymd');
        
        return "{$safeName}_{$movie->id}_{$timestamp}";
    }

    private function validateParameterConfig(array $config): void
    {
        $required = ['parameter_name', 'order_position'];
        
        foreach ($required as $field) {
            if (!isset($config[$field])) {
                throw new ValidationException("Champ requis manquant: {$field}");
            }
        }

        if (!is_int($config['order_position']) || $config['order_position'] < 1) {
            throw new ValidationException("order_position doit être un entier positif");
        }
    }

    private function findOrCreateParameter(array $config): Parameter
    {
        $parameter = Parameter::where('name', $config['parameter_name'])->first();
        
        if (!$parameter) {
            $parameter = Parameter::create([
                'name' => $config['parameter_name'],
                'code' => strtoupper(Str::slug($config['parameter_name'], '_')),
                'type' => $config['parameter_type'] ?? 'string',
                'is_required' => $config['is_required'] ?? false,
                'category' => $config['category'] ?? 'content',
                'description' => $config['description'] ?? null
            ]);
        }

        return $parameter;
    }



    private function calculateNomenclatureScore(string $nomenclature, array $issues, array $suggestions): int
    {
        $score = 100;
        
        // Pénalités pour les problèmes
        $score -= count($issues) * 20;
        $score -= count($suggestions) * 5;
        
        // Bonus pour les bonnes pratiques
        if (strlen($nomenclature) <= 50) $score += 5;
        if (preg_match('/^\w+_\d{4}_/', $nomenclature)) $score += 5;
        
        return max(0, min(100, $score));
    }

    private function analyzeNomenclaturePattern(string $nomenclature): string
    {
        $parts = explode('_', $nomenclature);
        $pattern = [];

        foreach ($parts as $part) {
            if (is_numeric($part)) {
                $pattern[] = 'N'; // Numérique
            } elseif (strlen($part) == 4 && is_numeric($part)) {
                $pattern[] = 'Y'; // Année
            } elseif (preg_match('/^[A-Z]{2,4}$/', $part)) {
                $pattern[] = 'C'; // Code
            } else {
                $pattern[] = 'T'; // Texte
            }
        }

        return implode('-', $pattern);
    }

    private function analyzeCompletionStatus(array $preview): string
    {
        $requiredParams = collect($preview)->where('is_required', true);
        $completedRequired = $requiredParams->where('raw_value', '!=', null);

        if ($requiredParams->isEmpty()) {
            return 'movies_without_nomenclature';
        }

        if ($completedRequired->count() === $requiredParams->count()) {
            return 'movies_with_complete_nomenclature';
        }

        return 'movies_with_partial_nomenclature';
    }
}
