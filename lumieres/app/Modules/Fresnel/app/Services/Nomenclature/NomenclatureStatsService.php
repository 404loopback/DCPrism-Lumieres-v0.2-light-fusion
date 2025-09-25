<?php

namespace Modules\Fresnel\app\Services\Nomenclature;

use Illuminate\Support\Collection;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Movie;

/**
 * Service specialized in nomenclature statistics and analytics
 * Extracted from UnifiedNomenclatureService
 */
class NomenclatureStatsService
{
    public function __construct(
        private NomenclatureBuilder $nomenclatureBuilder,
        private NomenclatureRepository $nomenclatureRepository
    ) {}

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
            'nomenclature_patterns' => [],
            'average_nomenclature_length' => 0,
            'longest_nomenclature' => '',
            'shortest_nomenclature' => '',
            'validation_errors' => 0,
        ];

        if ($movies->isEmpty()) {
            return $stats;
        }

        $parameterUsage = [];
        $nomenclaturePatterns = [];
        $nomenclatureLengths = [];
        $allNomenclatures = [];

        foreach ($movies as $movie) {
            $nomenclature = $this->nomenclatureBuilder->build($movie, $festival);
            $preview = $this->nomenclatureBuilder->preview($movie, $festival);

            $allNomenclatures[] = $nomenclature;
            $nomenclatureLengths[] = strlen($nomenclature);

            // Pattern analysis
            $pattern = $this->analyzeNomenclaturePattern($nomenclature);
            $nomenclaturePatterns[$pattern] = ($nomenclaturePatterns[$pattern] ?? 0) + 1;

            // Completion analysis
            $completionStatus = $this->analyzeCompletionStatus($preview);
            $stats[$completionStatus]++;

            // Validation errors
            if (!$preview['is_valid']) {
                $stats['validation_errors']++;
            }

            // Parameter usage
            foreach ($movie->movieParameters as $movieParam) {
                $paramName = $movieParam->parameter->name;
                $parameterUsage[$paramName] = ($parameterUsage[$paramName] ?? 0) + 1;
            }
        }

        // Calculate derived statistics
        $stats['most_used_parameters'] = collect($parameterUsage)
            ->sortDesc()
            ->take(10)
            ->toArray();

        $stats['nomenclature_patterns'] = collect($nomenclaturePatterns)
            ->sortDesc()
            ->take(5)
            ->toArray();

        // Length statistics
        if (!empty($nomenclatureLengths)) {
            $stats['average_nomenclature_length'] = round(array_sum($nomenclatureLengths) / count($nomenclatureLengths), 1);
            
            $longestIndex = array_keys($nomenclatureLengths, max($nomenclatureLengths))[0];
            $shortestIndex = array_keys($nomenclatureLengths, min($nomenclatureLengths))[0];
            
            $stats['longest_nomenclature'] = $allNomenclatures[$longestIndex];
            $stats['shortest_nomenclature'] = $allNomenclatures[$shortestIndex];
        }

        // Calculate parameter completion rates
        foreach ($this->nomenclatureRepository->getActiveNomenclatures($festival) as $nomenclature) {
            $paramName = $nomenclature->parameter->name;
            $completionRate = ($parameterUsage[$paramName] ?? 0) / max($movies->count(), 1) * 100;
            $stats['parameter_completion_rate'][$paramName] = round($completionRate, 1);
        }

        return $stats;
    }

    /**
     * Obtenir les détails de validation pour tous les films
     */
    public function getValidationReport(Festival $festival): array
    {
        $movies = $festival->movies()->with(['movieParameters.parameter'])->get();
        $report = [
            'total_movies' => $movies->count(),
            'valid_nomenclatures' => 0,
            'invalid_nomenclatures' => 0,
            'common_issues' => [],
            'movies_details' => []
        ];

        $allIssues = [];

        foreach ($movies as $movie) {
            $preview = $this->nomenclatureBuilder->preview($movie, $festival);
            $movieDetail = [
                'movie_id' => $movie->id,
                'movie_title' => $movie->title,
                'nomenclature' => $preview['final_nomenclature'],
                'is_valid' => $preview['is_valid'],
                'warnings' => $preview['warnings'] ?? [],
                'missing_required' => []
            ];

            if ($preview['is_valid']) {
                $report['valid_nomenclatures']++;
            } else {
                $report['invalid_nomenclatures']++;
                
                // Collecter les problèmes pour analyse globale
                foreach ($preview['warnings'] as $warning) {
                    $allIssues[] = $warning;
                }
            }

            // Identifier les paramètres requis manquants
            foreach ($preview['preview_parts'] as $part) {
                if ($part['is_required'] && empty($part['raw_value'])) {
                    $movieDetail['missing_required'][] = $part['parameter'];
                }
            }

            $report['movies_details'][] = $movieDetail;
        }

        // Analyser les problèmes les plus fréquents
        $issueCounts = array_count_values($allIssues);
        arsort($issueCounts);
        $report['common_issues'] = array_slice($issueCounts, 0, 5, true);

        return $report;
    }

    /**
     * Comparer les performances entre festivals
     */
    public function compareFestivals(Collection $festivals): array
    {
        $comparison = [
            'festivals' => [],
            'summary' => [
                'best_completion_rate' => ['festival' => null, 'rate' => 0],
                'most_movies' => ['festival' => null, 'count' => 0],
                'most_complex_nomenclature' => ['festival' => null, 'avg_length' => 0]
            ]
        ];

        foreach ($festivals as $festival) {
            $stats = $this->getNomenclatureStats($festival);
            
            $festivalData = [
                'festival_id' => $festival->id,
                'festival_name' => $festival->name,
                'total_movies' => $stats['total_movies'],
                'completion_rate' => $stats['total_movies'] > 0 
                    ? round(($stats['movies_with_complete_nomenclature'] / $stats['total_movies']) * 100, 1)
                    : 0,
                'average_length' => $stats['average_nomenclature_length'],
                'validation_errors' => $stats['validation_errors'],
                'active_parameters' => count($stats['parameter_completion_rate'])
            ];

            $comparison['festivals'][] = $festivalData;

            // Track summary statistics
            if ($festivalData['completion_rate'] > $comparison['summary']['best_completion_rate']['rate']) {
                $comparison['summary']['best_completion_rate'] = [
                    'festival' => $festival->name,
                    'rate' => $festivalData['completion_rate']
                ];
            }

            if ($festivalData['total_movies'] > $comparison['summary']['most_movies']['count']) {
                $comparison['summary']['most_movies'] = [
                    'festival' => $festival->name,
                    'count' => $festivalData['total_movies']
                ];
            }

            if ($festivalData['average_length'] > $comparison['summary']['most_complex_nomenclature']['avg_length']) {
                $comparison['summary']['most_complex_nomenclature'] = [
                    'festival' => $festival->name,
                    'avg_length' => $festivalData['average_length']
                ];
            }
        }

        // Trier par taux de completion
        usort($comparison['festivals'], function($a, $b) {
            return $b['completion_rate'] <=> $a['completion_rate'];
        });

        return $comparison;
    }

    /**
     * Obtenir les tendances temporelles d'utilisation
     */
    public function getUsageTrends(Festival $festival, int $days = 30): array
    {
        $endDate = now();
        $startDate = now()->subDays($days);

        // Cette méthode nécessiterait des données de tracking plus avancées
        // Pour l'instant, retournons une structure de base
        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $days
            ],
            'trends' => [
                'nomenclature_generation' => [],
                'configuration_changes' => [],
                'validation_errors' => []
            ],
            'insights' => [
                'most_active_day' => null,
                'peak_error_period' => null,
                'configuration_stability' => 'stable' // stable, changing, volatile
            ]
        ];
    }

    /**
     * Recommandations d'optimisation
     */
    public function getOptimizationRecommendations(Festival $festival): array
    {
        $stats = $this->getNomenclatureStats($festival);
        $recommendations = [];

        // Recommandations basées sur les statistiques
        if ($stats['validation_errors'] > ($stats['total_movies'] * 0.1)) {
            $recommendations[] = [
                'type' => 'error_rate',
                'severity' => 'high',
                'message' => 'Taux d\'erreur élevé dans les nomenclatures générées',
                'action' => 'Vérifier la configuration des paramètres requis'
            ];
        }

        if ($stats['average_nomenclature_length'] > 100) {
            $recommendations[] = [
                'type' => 'length',
                'severity' => 'medium',
                'message' => 'Nomenclatures très longues détectées',
                'action' => 'Considérer raccourcir les préfixes/suffixes ou simplifier les règles'
            ];
        }

        $incompletionRate = $stats['total_movies'] > 0 
            ? ($stats['movies_with_partial_nomenclature'] + $stats['movies_without_nomenclature']) / $stats['total_movies']
            : 0;

        if ($incompletionRate > 0.3) {
            $recommendations[] = [
                'type' => 'completion',
                'severity' => 'high',
                'message' => 'Beaucoup de films ont des nomenclatures incomplètes',
                'action' => 'Vérifier que tous les paramètres requis sont disponibles'
            ];
        }

        // Recommandations sur les patterns
        $patternDiversity = count($stats['nomenclature_patterns']);
        if ($patternDiversity > 10) {
            $recommendations[] = [
                'type' => 'consistency',
                'severity' => 'medium',
                'message' => 'Nombreux patterns différents détectés',
                'action' => 'Vérifier la cohérence de la configuration'
            ];
        }

        return [
            'recommendations' => $recommendations,
            'overall_health' => $this->calculateOverallHealth($stats),
            'priority_actions' => $this->getPriorityActions($recommendations)
        ];
    }

    /**
     * Méthodes privées utilitaires
     */

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
        $requiredParams = collect($preview['preview_parts'])->where('is_required', true);
        $completedRequired = $requiredParams->where('raw_value', '!=', null)->whereNotIn('raw_value', ['', null]);

        if ($requiredParams->isEmpty()) {
            return 'movies_without_nomenclature';
        }

        if ($completedRequired->count() === $requiredParams->count()) {
            return 'movies_with_complete_nomenclature';
        }

        return 'movies_with_partial_nomenclature';
    }

    private function calculateOverallHealth(array $stats): string
    {
        $score = 100;

        if ($stats['total_movies'] == 0) return 'no_data';

        // Facteurs de santé
        $errorRate = $stats['validation_errors'] / max($stats['total_movies'], 1);
        $completionRate = $stats['movies_with_complete_nomenclature'] / max($stats['total_movies'], 1);

        $score -= $errorRate * 40; // Les erreurs pèsent lourd
        $score -= (1 - $completionRate) * 30; // L'incomplétude aussi
        
        if ($stats['average_nomenclature_length'] > 120) {
            $score -= 10; // Pénalité pour longueur excessive
        }

        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'fair';
        if ($score >= 40) return 'poor';
        return 'critical';
    }

    private function getPriorityActions(array $recommendations): array
    {
        $highPriority = array_filter($recommendations, fn($r) => $r['severity'] === 'high');
        return array_slice($highPriority, 0, 3); // Top 3 des actions prioritaires
    }
}
