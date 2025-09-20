<?php

namespace Modules\Fresnel\app\Services\Nomenclature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Nomenclature;
use Modules\Fresnel\app\Models\Parameter;

/**
 * Service specialized in nomenclature configuration management
 * Extracted from UnifiedNomenclatureService
 */
class NomenclatureConfigService
{
    public function __construct(
        private NomenclatureValidator $validator,
        private NomenclatureRepository $nomenclatureRepository
    ) {}

    /**
     * Configurer la nomenclature pour un festival
     */
    public function configureFestivalNomenclature(
        Festival $festival,
        array $parameterConfigs
    ): array {
        // Validation préalable
        $validation = $this->validator->validateParameterConfigs($parameterConfigs);
        if (!$validation['is_valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors'],
                'message' => 'Configuration invalide'
            ];
        }

        try {
            DB::beginTransaction();

            // Désactiver toutes les nomenclatures existantes
            Nomenclature::where('festival_id', $festival->id)
                ->update(['is_active' => false]);

            $createdNomenclatures = [];

            foreach ($parameterConfigs as $config) {
                $parameter = $this->findOrCreateParameter($config);

                $nomenclature = Nomenclature::create([
                    'festival_id' => $festival->id,
                    'parameter_id' => $parameter->id,
                    'festival_parameter_id' => $config['festival_parameter_id'] ?? null,
                    'order_position' => $config['order_position'],
                    'separator' => $config['separator'] ?? '_',
                    'is_active' => true,
                    'is_required' => $config['is_required'] ?? false,
                    'prefix' => $config['prefix'] ?? null,
                    'suffix' => $config['suffix'] ?? null,
                    'default_value' => $config['default_value'] ?? null,
                    'formatting_rules' => $config['formatting_rules'] ?? null,
                    'conditional_rules' => $config['conditional_rules'] ?? null,
                ]);

                // Valider la nomenclature créée
                $nomenclatureValidation = $this->validator->validateNomenclatureConfig($nomenclature);
                if (!$nomenclatureValidation['is_valid']) {
                    throw new \Exception('Nomenclature invalide: ' . implode(', ', $nomenclatureValidation['errors']));
                }

                $createdNomenclatures[] = $nomenclature;
            }

            DB::commit();

            // Invalider le cache
            $this->nomenclatureRepository->clearCache($festival);

            Log::info('[NomenclatureConfigService] Festival nomenclature configured', [
                'festival_id' => $festival->id,
                'nomenclatures_count' => count($createdNomenclatures),
            ]);

            return [
                'success' => true,
                'nomenclatures' => $createdNomenclatures,
                'message' => 'Nomenclature configurée avec succès',
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('[NomenclatureConfigService] Failed to configure nomenclature', [
                'festival_id' => $festival->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la configuration'
            ];
        }
    }

    /**
     * Mettre à jour une nomenclature existante
     */
    public function updateNomenclature(Nomenclature $nomenclature, array $data): array
    {
        try {
            DB::beginTransaction();

            // Valider les nouvelles données
            $tempNomenclature = $nomenclature->replicate()->fill($data);
            $validation = $this->validator->validateNomenclatureConfig($tempNomenclature);

            if (!$validation['is_valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors'],
                    'warnings' => $validation['warnings'] ?? []
                ];
            }

            $nomenclature->update($data);
            
            DB::commit();

            // Invalider le cache
            $this->nomenclatureRepository->clearCache($nomenclature->festival);

            Log::info('[NomenclatureConfigService] Nomenclature updated', [
                'nomenclature_id' => $nomenclature->id,
                'festival_id' => $nomenclature->festival_id,
            ]);

            return [
                'success' => true,
                'nomenclature' => $nomenclature->fresh(),
                'warnings' => $validation['warnings'] ?? []
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('[NomenclatureConfigService] Failed to update nomenclature', [
                'nomenclature_id' => $nomenclature->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Réorganiser l'ordre des nomenclatures
     */
    public function reorderNomenclatures(Festival $festival, array $orderArray): array
    {
        try {
            // Utiliser la méthode de réorganisation sécurisée du modèle
            Nomenclature::reorderSafely($orderArray, $festival->id);

            // Invalider le cache
            $this->nomenclatureRepository->clearCache($festival);

            Log::info('[NomenclatureConfigService] Nomenclatures reordered', [
                'festival_id' => $festival->id,
                'new_order' => $orderArray,
            ]);

            return [
                'success' => true,
                'message' => 'Ordre mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('[NomenclatureConfigService] Failed to reorder nomenclatures', [
                'festival_id' => $festival->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Dupliquer la configuration d'un festival vers un autre
     */
    public function duplicateConfiguration(Festival $sourceFestival, Festival $targetFestival): array
    {
        $sourceNomenclatures = $sourceFestival->nomenclatures()
            ->where('is_active', true)
            ->ordered()
            ->get();

        if ($sourceNomenclatures->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Aucune nomenclature active à dupliquer'
            ];
        }

        try {
            DB::beginTransaction();

            // Désactiver les nomenclatures existantes du festival cible
            Nomenclature::where('festival_id', $targetFestival->id)
                ->update(['is_active' => false]);

            $duplicatedCount = 0;

            foreach ($sourceNomenclatures as $sourceNomenclature) {
                $newNomenclature = $sourceNomenclature->replicate([
                    'festival_id'
                ]);
                $newNomenclature->festival_id = $targetFestival->id;
                $newNomenclature->save();

                $duplicatedCount++;
            }

            DB::commit();

            // Invalider le cache des deux festivals
            $this->nomenclatureRepository->clearCache($sourceFestival);
            $this->nomenclatureRepository->clearCache($targetFestival);

            Log::info('[NomenclatureConfigService] Configuration duplicated', [
                'source_festival_id' => $sourceFestival->id,
                'target_festival_id' => $targetFestival->id,
                'duplicated_count' => $duplicatedCount,
            ]);

            return [
                'success' => true,
                'duplicated_count' => $duplicatedCount,
                'message' => "Configuration dupliquée avec succès ({$duplicatedCount} nomenclatures)"
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('[NomenclatureConfigService] Failed to duplicate configuration', [
                'source_festival_id' => $sourceFestival->id,
                'target_festival_id' => $targetFestival->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Réinitialiser la configuration d'un festival
     */
    public function resetConfiguration(Festival $festival): array
    {
        try {
            DB::beginTransaction();

            $deletedCount = Nomenclature::where('festival_id', $festival->id)->count();
            Nomenclature::where('festival_id', $festival->id)->delete();

            DB::commit();

            // Invalider le cache
            $this->nomenclatureRepository->clearCache($festival);

            Log::info('[NomenclatureConfigService] Configuration reset', [
                'festival_id' => $festival->id,
                'deleted_count' => $deletedCount,
            ]);

            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => 'Configuration réinitialisée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('[NomenclatureConfigService] Failed to reset configuration', [
                'festival_id' => $festival->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Obtenir un template de configuration par défaut
     */
    public function getDefaultTemplate(): array
    {
        return [
            [
                'parameter_name' => 'title',
                'order_position' => 1,
                'separator' => '_',
                'is_required' => true,
                'formatting_rules' => [
                    'trim' => true,
                    'max_length' => 30
                ]
            ],
            [
                'parameter_name' => 'year',
                'order_position' => 2,
                'separator' => '_',
                'is_required' => false,
                'formatting_rules' => [
                    'pad_left' => ['length' => 4, 'char' => '0']
                ]
            ],
            [
                'parameter_name' => 'festival_code',
                'order_position' => 3,
                'separator' => '',
                'is_required' => false,
                'formatting_rules' => [
                    'uppercase' => true,
                    'max_length' => 5
                ]
            ]
        ];
    }

    /**
     * Méthodes privées utilitaires
     */

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
                'description' => $config['description'] ?? null,
                'is_active' => true,
            ]);

            Log::info('[NomenclatureConfigService] Parameter created', [
                'parameter_name' => $parameter->name,
                'parameter_id' => $parameter->id,
            ]);
        }

        return $parameter;
    }
}
