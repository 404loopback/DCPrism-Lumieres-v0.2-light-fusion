<?php

namespace Modules\Fresnel\app\Services;

use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Services\Nomenclature\NomenclatureBuilder;
use Modules\Fresnel\app\Services\Nomenclature\NomenclatureConfigService;
use Modules\Fresnel\app\Services\Nomenclature\NomenclatureStatsService;
use Modules\Fresnel\app\Services\Nomenclature\NomenclatureValidator;
use Modules\Fresnel\app\Services\Nomenclature\ParameterExtractor;

/**
 * Unified service that orchestrates nomenclature operations
 * Now delegates to specialized services for better maintainability
 */
class UnifiedNomenclatureService
{
    public function __construct(
        private NomenclatureBuilder $nomenclatureBuilder,
        private NomenclatureConfigService $configService,
        private NomenclatureStatsService $statsService,
        private NomenclatureValidator $validator,
        private ParameterExtractor $parameterExtractor
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
        return $this->configService->configureFestivalNomenclature($festival, $parameterConfigs);
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
        return $this->validator->validateGeneratedNomenclature($nomenclature, $festival);
    }

    /**
     * Obtenir les statistiques de nomenclature pour un festival
     */
    public function getNomenclatureStats(Festival $festival): array
    {
        return $this->statsService->getNomenclatureStats($festival);
    }

    /**
     * Méthodes déléguées aux services spécialisés
     */

    /**
     * Obtenir un rapport de validation détaillé
     */
    public function getValidationReport(Festival $festival): array
    {
        return $this->statsService->getValidationReport($festival);
    }

    /**
     * Obtenir des recommandations d'optimisation
     */
    public function getOptimizationRecommendations(Festival $festival): array
    {
        return $this->statsService->getOptimizationRecommendations($festival);
    }

    /**
     * Valider la configuration d'un festival
     */
    public function validateFestivalConfiguration(Festival $festival): array
    {
        return $this->validator->validateFestivalNomenclatureConfig($festival);
    }

    /**
     * Réorganiser les nomenclatures
     */
    public function reorderNomenclatures(Festival $festival, array $orderArray): array
    {
        return $this->configService->reorderNomenclatures($festival, $orderArray);
    }

    /**
     * Dupliquer la configuration entre festivals
     */
    public function duplicateConfiguration(Festival $sourceFestival, Festival $targetFestival): array
    {
        return $this->configService->duplicateConfiguration($sourceFestival, $targetFestival);
    }

    /**
     * Réinitialiser la configuration
     */
    public function resetConfiguration(Festival $festival): array
    {
        return $this->configService->resetConfiguration($festival);
    }
}
