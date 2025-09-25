<?php

namespace Modules\Fresnel\app\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\User;

/**
 * Service centralisé pour gérer les accès basés sur les festivals
 * 
 * À utiliser dans TOUS les resources Filament pour appliquer automatiquement
 * les bonnes restrictions selon le rôle de l'utilisateur connecté
 */
class FestivalAccessService
{
    /**
     * Applique le scope festival sur une table/resource Filament
     * 
     * Usage dans un Resource :
     * public static function table(Table $table): Table
     * {
     *     return $table
     *         ->modifyQueryUsing(fn (Builder $query) => FestivalAccessService::applyFestivalScope($query))
     *         // ... reste de la configuration
     * }
     */
    public static function applyFestivalScope(Builder $query): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Admin et super_admin voient tout
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return $query;
        }

        $modelClass = $query->getModel();
        $className = class_basename($modelClass);

        // Appliquer le scope selon le modèle
        return match ($className) {
            'Movie' => self::scopeMovies($query, $user),
            'Dcp' => self::scopeDcps($query, $user),
            'Version' => self::scopeVersions($query, $user), 
            'Upload' => self::scopeUploads($query, $user),
            'Festival' => self::scopeFestivals($query, $user),
            'Screening' => self::scopeScreenings($query, $user),
            'Screen' => self::scopeScreens($query, $user),
            default => self::scopeDefault($query, $user)
        };
    }

    /**
     * Scope pour les movies
     */
    private static function scopeMovies(Builder $query, User $user): Builder
    {
        $festivalIds = $user->festivals()->pluck('festivals.id')->toArray();
        
        if (empty($festivalIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('festivals', function ($q) use ($festivalIds) {
            $q->whereIn('festivals.id', $festivalIds);
        });
    }

    /**
     * Scope pour les DCPs (via movies)
     */
    private static function scopeDcps(Builder $query, User $user): Builder
    {
        $festivalIds = $user->festivals()->pluck('festivals.id')->toArray();
        
        if (empty($festivalIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('movie.festivals', function ($q) use ($festivalIds) {
            $q->whereIn('festivals.id', $festivalIds);
        });
    }

    /**
     * Scope pour les versions (via movies)  
     */
    private static function scopeVersions(Builder $query, User $user): Builder
    {
        return self::scopeDcps($query, $user); // Même logique que DCP
    }

    /**
     * Scope pour les uploads (via movies)
     */
    private static function scopeUploads(Builder $query, User $user): Builder
    {
        return self::scopeDcps($query, $user); // Même logique que DCP
    }

    /**
     * Scope pour les festivals eux-mêmes
     */
    private static function scopeFestivals(Builder $query, User $user): Builder
    {
        $festivalIds = $user->festivals()->pluck('festivals.id')->toArray();
        
        if (empty($festivalIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $festivalIds);
    }

    /**
     * Scope pour les screenings
     */
    private static function scopeScreenings(Builder $query, User $user): Builder
    {
        $festivalIds = $user->festivals()->pluck('festivals.id')->toArray();
        
        if (empty($festivalIds)) {
            return $query->whereRaw('1 = 0');
        }

        // Supposant que screening a une relation directe avec festival
        return $query->whereIn('festival_id', $festivalIds);
    }

    /**
     * Scope pour les screens (via cinemas)
     */
    private static function scopeScreens(Builder $query, User $user): Builder
    {
        $festivalIds = $user->festivals()->pluck('festivals.id')->toArray();
        
        if (empty($festivalIds)) {
            return $query->whereRaw('1 = 0');
        }

        // Supposant que screen → cinema → festivals
        return $query->whereHas('cinema.festivals', function ($q) use ($festivalIds) {
            $q->whereIn('festivals.id', $festivalIds);
        });
    }

    /**
     * Scope par défaut : essaye d'abord une relation directe, puis via movie
     */
    private static function scopeDefault(Builder $query, User $user): Builder
    {
        $festivalIds = $user->festivals()->pluck('festivals.id')->toArray();
        
        if (empty($festivalIds)) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();

        // Essayer relation directe avec festivals
        if (method_exists($model, 'festivals')) {
            return $query->whereHas('festivals', function ($q) use ($festivalIds) {
                $q->whereIn('festivals.id', $festivalIds);
            });
        }

        // Essayer colonne festival_id
        if (in_array('festival_id', $model->getFillable())) {
            return $query->whereIn('festival_id', $festivalIds);
        }

        // Essayer via movie.festivals
        if (method_exists($model, 'movie')) {
            return $query->whereHas('movie.festivals', function ($q) use ($festivalIds) {
                $q->whereIn('festivals.id', $festivalIds);
            });
        }

        // Si rien ne marche, bloquer l'accès par sécurité
        return $query->whereRaw('1 = 0');
    }

    /**
     * Vérifie les permissions d'accès spécifiques par rôle
     */
    public static function canAccess(string $resource, string $action = 'view'): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin ont tous les droits
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        // Matrice des permissions par rôle
        $permissions = [
            'manager' => [
                'Movie' => ['view', 'create', 'edit', 'delete'],
                'Dcp' => ['view', 'create', 'edit'],
                'Version' => ['view', 'create', 'edit', 'delete'],
                'Festival' => ['view'],
            ],
            'tech' => [
                'Movie' => ['view'],
                'Dcp' => ['view', 'edit'], // Validation uniquement
                'Version' => ['view'],
                'Festival' => ['view'],
            ],
            'source' => [
                'Movie' => ['view'],
                'Dcp' => ['create', 'view'], // Upload uniquement 
                'Version' => ['view'],
                'Upload' => ['create', 'view'],
                'Festival' => ['view'],
            ],
            'cinema' => [
                'Movie' => ['view'],
                'Dcp' => ['view'], // Download uniquement
                'Screening' => ['view'],
                'Screen' => ['view'],
                'Festival' => ['view'],
            ],
            'supervisor' => [
                'Movie' => ['view'],
                'Dcp' => ['view'], 
                'Version' => ['view'],
                'Festival' => ['view'],
                'Upload' => ['view'],
                'Screening' => ['view'],
            ],
        ];

        foreach ($user->roles as $role) {
            $roleName = $role->name;
            if (isset($permissions[$roleName][$resource])) {
                return in_array($action, $permissions[$roleName][$resource]);
            }
        }

        return false;
    }
}
