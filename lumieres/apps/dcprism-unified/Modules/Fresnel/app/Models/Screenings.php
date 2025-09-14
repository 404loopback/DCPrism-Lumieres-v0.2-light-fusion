<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Screenings extends Model
{
    protected $fillable = [
        'cinemas_id',
        'name',
        'scheduled_at',
        'notes',
        'is_active'
    ];
    
    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_active' => 'boolean'
    ];
    
    /**
     * Relation avec le cinéma
     */
    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinemas::class, 'cinemas_id');
    }
    
    /**
     * Relation many-to-many avec les versions
     */
    public function versions(): BelongsToMany
    {
        return $this->belongsToMany(Version::class, 'versions_screenings', 'screening_id', 'version_id')
                    ->withPivot(['notes'])
                    ->withTimestamps();
    }
    
    /**
     * Scope pour les screenings actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
