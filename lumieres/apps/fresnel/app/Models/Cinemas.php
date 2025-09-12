<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cinemas extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city', 
        'postal_code',
        'country',
        'contact_email',
        'contact_phone',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    /**
     * Relation avec les screenings
     */
    public function screenings(): HasMany
    {
        return $this->hasMany(Screenings::class);
    }
    
    /**
     * Scope pour les cinémas actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
