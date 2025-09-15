<?php

namespace Modules\Fresnel\app\Services\Nomenclature;

use Modules\Fresnel\app\Models\{Festival, Nomenclature};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * Centralized repository for nomenclature data access
 * Eliminates duplication of getActiveNomenclatures across services
 */
class NomenclatureRepository
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get active nomenclatures for a festival with caching
     */
    public function getActiveNomenclatures(Festival $festival): Collection
    {
        $cacheKey = "nomenclature_active_{$festival->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($festival) {
            return Nomenclature::where('festival_id', $festival->id)
                             ->where('is_active', true)
                             ->with('parameter')
                             ->orderBy('order_position')
                             ->get();
        });
    }

    /**
     * Clear nomenclature cache for a festival
     */
    public function clearCache(Festival $festival): void
    {
        Cache::forget("nomenclature_active_{$festival->id}");
    }

    /**
     * Get cached nomenclatures or fetch from database
     */
    public function getNomenclatures(Festival $festival, bool $activeOnly = true): Collection
    {
        if ($activeOnly) {
            return $this->getActiveNomenclatures($festival);
        }

        return $festival->nomenclatures()
            ->with('parameter')
            ->orderBy('order_position')
            ->get();
    }
}
