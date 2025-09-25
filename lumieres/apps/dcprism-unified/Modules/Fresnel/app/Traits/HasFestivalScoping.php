<?php

namespace Modules\Fresnel\app\Traits;

use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\Festival;

/**
 * Trait pour gérer les accès basés sur les festivals selon les rôles
 * 
 * Règles d'accès par rôle :
 * - admin/super_admin : Accès à tout
 * - manager : Gestion des films/DCPs de leurs festivals
 * - tech : Validation des DCPs de leurs festivals  
 * - source : Upload vers leurs festivals assignés
 * - cinema : Accès aux DCPs de leurs festivals
 * - supervisor : Lecture seule de leurs festivals
 */
trait HasFestivalScoping
{
    /**
     * Applique un scope basé sur les festivals de l'utilisateur connecté
     */
    public function scopeFestivalAccess(Builder $query, ?string $relation = 'festivals'): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Aucun accès si non connecté
        }

        // Admin et super_admin ont accès à tout
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return $query;
        }

        // Récupérer les festivals assignés à l'utilisateur
        $userFestivalIds = $user->festivals()->pluck('festivals.id')->toArray();
        
        if (empty($userFestivalIds)) {
            return $query->whereRaw('1 = 0'); // Aucun accès si pas de festivals
        }

        // Appliquer le scope selon le type de relation
        return $this->applyFestivalScope($query, $userFestivalIds, $relation);
    }

    /**
     * Applique le scope festival selon le type de relation
     */
    protected function applyFestivalScope(Builder $query, array $festivalIds, string $relation): Builder
    {
        $modelClass = $query->getModel();
        
        // Si le modèle a une relation directe avec festivals
        if (method_exists($modelClass, $relation)) {
            return $query->whereHas($relation, function ($q) use ($festivalIds) {
                $q->whereIn('festivals.id', $festivalIds);
            });
        }

        // Si le modèle a une colonne festival_id directe
        if (in_array('festival_id', $modelClass->getFillable())) {
            return $query->whereIn('festival_id', $festivalIds);
        }

        // Relations indirectes spécifiques
        return $this->applyIndirectFestivalScope($query, $festivalIds, $modelClass);
    }

    /**
     * Gère les relations indirectes vers les festivals
     */
    protected function applyIndirectFestivalScope(Builder $query, array $festivalIds, $modelClass): Builder
    {
        $className = class_basename($modelClass);

        switch ($className) {
            case 'Dcp':
            case 'Version':
                // DCP/Version → Movie → Festivals
                return $query->whereHas('movie.festivals', function ($q) use ($festivalIds) {
                    $q->whereIn('festivals.id', $festivalIds);
                });

            case 'Upload':
                // Upload → Movie → Festivals
                return $query->whereHas('movie.festivals', function ($q) use ($festivalIds) {
                    $q->whereIn('festivals.id', $festivalIds);
                });

            case 'Screening':
                // Screening → Festival (relation directe probable)
                return $query->whereIn('festival_id', $festivalIds);

            case 'Screen':
                // Screen → Cinema → Festival (ou autre logique selon votre modèle)
                if (method_exists($modelClass, 'cinema')) {
                    return $query->whereHas('cinema.festivals', function ($q) use ($festivalIds) {
                        $q->whereIn('festivals.id', $festivalIds);
                    });
                }
                break;

            default:
                // Par défaut, essayer la relation movie.festivals
                if (method_exists($modelClass, 'movie')) {
                    return $query->whereHas('movie.festivals', function ($q) use ($festivalIds) {
                        $q->whereIn('festivals.id', $festivalIds);
                    });
                }
        }

        return $query;
    }

    /**
     * Vérifie si l'utilisateur a accès à un festival spécifique
     */
    public static function canAccessFestival(int $festivalId): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin ont accès à tout
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        // Vérifier si le festival est dans ceux assignés à l'utilisateur
        return $user->festivals()->where('festivals.id', $festivalId)->exists();
    }

    /**
     * Récupère les festivals accessibles selon le rôle
     */
    public static function getAccessibleFestivals(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();
        
        if (!$user) {
            return collect();
        }

        // Admin ont accès à tout
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return Festival::where('is_active', true)->get();
        }

        // Autres rôles : seulement leurs festivals
        return $user->festivals()->where('is_active', true)->get();
    }

    /**
     * Scope pour les films accessibles selon le rôle
     */
    public function scopeAccessibleMovies(Builder $query): Builder
    {
        return $this->scopeFestivalAccess($query, 'festivals');
    }

    /**
     * Scope pour les DCPs accessibles selon le rôle
     */
    public function scopeAccessibleDcps(Builder $query): Builder
    {
        return $this->scopeFestivalAccess($query, 'movie.festivals');
    }

    /**
     * Scope pour les versions accessibles selon le rôle
     */
    public function scopeAccessibleVersions(Builder $query): Builder
    {
        return $this->scopeFestivalAccess($query, 'movie.festivals');
    }

    /**
     * Récupère les options de films pour un utilisateur
     */
    public static function getAccessibleMovieOptions(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [];
        }

        $query = \Modules\Fresnel\app\Models\Movie::with('festivals');

        // Appliquer le scope selon le rôle
        if (!$user->hasAnyRole(['admin', 'super_admin'])) {
            $festivalIds = $user->festivals()->pluck('festivals.id')->toArray();
            if (empty($festivalIds)) {
                return [];
            }
            
            $query->whereHas('festivals', function ($q) use ($festivalIds) {
                $q->whereIn('festivals.id', $festivalIds);
            });
        }

        return $query->get()
            ->mapWithKeys(function ($movie) {
                $festivals = $movie->festivals->pluck('name')->join(', ');
                $label = $movie->title . ($festivals ? ' (' . $festivals . ')' : '');
                return [$movie->id => $label];
            })
            ->toArray();
    }
}
