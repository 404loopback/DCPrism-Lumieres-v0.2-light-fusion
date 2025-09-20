<?php

namespace Modules\Fresnel\app\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\FestivalParameter;
use Modules\Fresnel\app\Models\Parameter;

/**
 * Service pour gérer les paramètres de festival
 * Logique système vs personnalisés
 */
class FestivalParameterService
{
    /**
     * Ajouter automatiquement tous les paramètres système à un nouveau festival
     */
    public function addSystemParametersToFestival(Festival $festival): array
    {
        $systemParameters = Parameter::where('is_system', true)
            ->where('is_active', true)
            ->get();

        if ($systemParameters->isEmpty()) {
            return [
                'success' => true,
                'added_count' => 0,
                'message' => 'Aucun paramètre système à ajouter'
            ];
        }

        $addedCount = 0;

        try {
            DB::beginTransaction();

            foreach ($systemParameters as $parameter) {
                // Vérifier s'il n'existe pas déjà
                $exists = FestivalParameter::where('festival_id', $festival->id)
                    ->where('parameter_id', $parameter->id)
                    ->exists();

                if (!$exists) {
                    FestivalParameter::create([
                        'festival_id' => $festival->id,
                        'parameter_id' => $parameter->id,
                        'is_enabled' => true, // Système = toujours activé
                        'is_required' => true, // Système = requis par défaut
                        'is_visible_in_nomenclature' => true, // Visible par défaut
                        'is_system' => true, // Marquer comme système
                        'display_order' => $parameter->id, // Ordre par défaut
                        'custom_default_value' => null,
                        'custom_formatting_rules' => null,
                        'festival_specific_notes' => 'Paramètre système ajouté automatiquement'
                    ]);
                    $addedCount++;
                }
            }

            DB::commit();

            Log::info('[FestivalParameterService] System parameters added to festival', [
                'festival_id' => $festival->id,
                'festival_name' => $festival->name,
                'added_count' => $addedCount
            ]);

            return [
                'success' => true,
                'added_count' => $addedCount,
                'message' => "Ajouté {$addedCount} paramètre(s) système au festival"
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('[FestivalParameterService] Failed to add system parameters', [
                'festival_id' => $festival->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Ajouter un paramètre personnalisé à un festival
     */
    public function addCustomParameterToFestival(
        Festival $festival, 
        Parameter $parameter, 
        array $options = []
    ): FestivalParameter {
        // Vérifier que ce n'est pas un paramètre système
        if ($parameter->is_system) {
            throw new \InvalidArgumentException('Les paramètres système sont ajoutés automatiquement');
        }

        // Vérifier s'il n'existe pas déjà
        $existing = FestivalParameter::where('festival_id', $festival->id)
            ->where('parameter_id', $parameter->id)
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('Ce paramètre est déjà ajouté au festival');
        }

        try {
            DB::beginTransaction();

            $festivalParameter = FestivalParameter::create([
                'festival_id' => $festival->id,
                'parameter_id' => $parameter->id,
                'is_enabled' => $options['is_enabled'] ?? true,
                'is_required' => $options['is_required'] ?? false,
                'is_visible_in_nomenclature' => $options['is_visible_in_nomenclature'] ?? true,
                'is_system' => false,
                'display_order' => $options['display_order'] ?? $this->getNextDisplayOrder($festival),
                'custom_default_value' => $options['custom_default_value'] ?? null,
                'custom_formatting_rules' => $options['custom_formatting_rules'] ?? null,
                'festival_specific_notes' => $options['festival_specific_notes'] ?? null
            ]);

            DB::commit();

            Log::info('[FestivalParameterService] Custom parameter added to festival', [
                'festival_id' => $festival->id,
                'parameter_id' => $parameter->id,
                'parameter_name' => $parameter->name
            ]);

            return $festivalParameter;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Créer un paramètre personnalisé avec logique spéciale
     */
    public function createCustomParameterWithLogic(
        Festival $festival,
        string $name,
        string $logicType,
        array $logicConfig = [],
        array $options = []
    ): array {
        try {
            DB::beginTransaction();

            // Créer le paramètre
            $parameter = Parameter::create([
                'name' => $name,
                'code' => strtoupper(str_replace(' ', '_', $name)),
                'type' => 'string',
                'is_active' => true,
                'is_system' => false,
                'category' => 'custom',
                'description' => "Paramètre personnalisé avec logique: {$logicType}",
                'extraction_source' => 'auto',
                'extraction_pattern' => $this->buildExtractionPattern($logicType, $logicConfig),
                'default_value' => $this->buildDefaultValue($logicType, $logicConfig, $festival)
            ]);

            // Ajouter au festival
            $festivalParameter = $this->addCustomParameterToFestival($festival, $parameter, $options);

            DB::commit();

            return [
                'success' => true,
                'parameter' => $parameter,
                'festival_parameter' => $festivalParameter,
                'message' => "Paramètre personnalisé '{$name}' créé avec succès"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Supprimer un paramètre personnalisé d'un festival
     */
    public function removeCustomParameterFromFestival(Festival $festival, Parameter $parameter): bool
    {
        // Vérifier que ce n'est pas un paramètre système
        if ($parameter->is_system) {
            throw new \InvalidArgumentException('Les paramètres système ne peuvent pas être supprimés');
        }

        $festivalParameter = FestivalParameter::where('festival_id', $festival->id)
            ->where('parameter_id', $parameter->id)
            ->first();

        if (!$festivalParameter) {
            return false; // Déjà supprimé ou n'existe pas
        }

        if ($festivalParameter->is_system) {
            throw new \InvalidArgumentException('Ce paramètre système ne peut pas être supprimé');
        }

        $festivalParameter->delete();

        Log::info('[FestivalParameterService] Custom parameter removed from festival', [
            'festival_id' => $festival->id,
            'parameter_id' => $parameter->id,
            'parameter_name' => $parameter->name
        ]);

        return true;
    }

    /**
     * Obtenir les paramètres disponibles pour un festival (non système, non déjà ajoutés)
     */
    public function getAvailableParametersForFestival(Festival $festival): \Illuminate\Database\Eloquent\Collection
    {
        $alreadyAddedIds = FestivalParameter::where('festival_id', $festival->id)
            ->pluck('parameter_id')
            ->toArray();

        return Parameter::where('is_system', false)
            ->where('is_active', true)
            ->whereNotIn('id', $alreadyAddedIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Mettre à jour la visibilité dans la nomenclature
     */
    public function updateNomenclatureVisibility(
        Festival $festival, 
        Parameter $parameter, 
        bool $visible
    ): bool {
        $festivalParameter = FestivalParameter::where('festival_id', $festival->id)
            ->where('parameter_id', $parameter->id)
            ->first();

        if (!$festivalParameter) {
            return false;
        }

        $festivalParameter->update([
            'is_visible_in_nomenclature' => $visible
        ]);

        Log::info('[FestivalParameterService] Nomenclature visibility updated', [
            'festival_id' => $festival->id,
            'parameter_id' => $parameter->id,
            'visible' => $visible
        ]);

        return true;
    }

    /**
     * Méthodes privées utilitaires
     */

    private function getNextDisplayOrder(Festival $festival): int
    {
        return FestivalParameter::where('festival_id', $festival->id)
            ->max('display_order') + 1;
    }

    private function buildExtractionPattern(string $logicType, array $config): ?string
    {
        return match ($logicType) {
            'increment' => json_encode(['type' => 'increment', 'start' => $config['start'] ?? 1, 'padding' => $config['padding'] ?? 3]),
            'festival_initials' => json_encode(['type' => 'festival_initials', 'length' => $config['length'] ?? 3]),
            'cinema_initials' => json_encode(['type' => 'cinema_initials', 'length' => $config['length'] ?? 3]),
            'date_format' => json_encode(['type' => 'date_format', 'format' => $config['format'] ?? 'Y']),
            'custom_logic' => json_encode(['type' => 'custom', 'logic' => $config['logic'] ?? '']),
            default => null
        };
    }

    private function buildDefaultValue(string $logicType, array $config, Festival $festival): ?string
    {
        return match ($logicType) {
            'increment' => '001',
            'festival_initials' => strtoupper(substr($festival->name, 0, $config['length'] ?? 3)),
            'cinema_initials' => 'CIN', // À adapter selon le cinéma
            'date_format' => now()->format($config['format'] ?? 'Y'),
            'custom_logic' => $config['default'] ?? null,
            default => null
        };
    }

    /**
     * Types de logiques disponibles
     */
    public static function getAvailableLogicTypes(): array
    {
        return [
            'increment' => [
                'name' => 'Incrémentation',
                'description' => 'Génère des numéros séquentiels (001, 002, 003...)',
                'config_fields' => ['start', 'padding']
            ],
            'festival_initials' => [
                'name' => 'Initiales du festival',
                'description' => 'Premières lettres du nom du festival',
                'config_fields' => ['length']
            ],
            'cinema_initials' => [
                'name' => 'Initiales du cinéma',
                'description' => 'Premières lettres du nom du cinéma',
                'config_fields' => ['length']
            ],
            'date_format' => [
                'name' => 'Format de date',
                'description' => 'Date formatée (2025, 25, 0917...)',
                'config_fields' => ['format']
            ],
            'custom_logic' => [
                'name' => 'Logique personnalisée',
                'description' => 'Règle définie manuellement',
                'config_fields' => ['logic', 'default']
            ]
        ];
    }
}
