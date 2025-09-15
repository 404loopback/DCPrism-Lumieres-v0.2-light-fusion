<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name', 
        'description',
        'permissions',
        'is_active'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean'
    ];

    // Constantes des rôles DCPrism
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPERVISOR = 'supervisor';
    const ROLE_SOURCE = 'source';
    const ROLE_CINEMA = 'cinema';
    const ROLE_VALIDATOR = 'validator';

    /**
     * Users with this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Scope pour les rôles actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtenir tous les rôles disponibles
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_SUPERVISOR => 'Superviseur',
            self::ROLE_SOURCE => 'Source/Producteur',
            self::ROLE_CINEMA => 'Cinéma',
            self::ROLE_VALIDATOR => 'Validateur Technique'
        ];
    }
}
