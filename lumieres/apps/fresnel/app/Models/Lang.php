<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lang extends Model
{
    use HasFactory;

    protected $fillable = [
        'iso_639_1',
        'iso_639_3', 
        'name',
        'local_name'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Versions utilisant cette langue pour l'audio
     */
    public function audioVersions(): HasMany
    {
        return $this->hasMany(Version::class, 'audio_lang', 'iso_639_1');
    }

    /**
     * Versions utilisant cette langue pour les sous-titres
     */
    public function subtitleVersions(): HasMany
    {
        return $this->hasMany(Version::class, 'sub_lang', 'iso_639_1');
    }

    /**
     * DCP utilisant cette langue pour l'audio
     */
    public function audioDcps(): HasMany
    {
        return $this->hasMany(Dcp::class, 'audio_lang', 'iso_639_1');
    }

    /**
     * DCP utilisant cette langue pour les sous-titres
     */
    public function subtitleDcps(): HasMany
    {
        return $this->hasMany(Dcp::class, 'subtitle_lang', 'iso_639_1');
    }

    /**
     * Scope pour rechercher par code ISO
     */
    public function scopeByIso($query, string $iso)
    {
        return $query->where('iso_639_1', $iso)
                    ->orWhere('iso_639_3', $iso);
    }

    /**
     * Accessor pour afficher le nom complet
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->local_name ? "{$this->name} ({$this->local_name})" : $this->name;
    }
}
