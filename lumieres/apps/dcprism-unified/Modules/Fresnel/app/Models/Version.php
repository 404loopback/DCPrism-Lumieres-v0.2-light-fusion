<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Version extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'type',
        'audio_lang',
        'sub_lang',
        'accessibility',
        'ov_id',
        'vf_ids',
        'generated_nomenclature',
        'format'
    ];

    protected $casts = [
        'vf_ids' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const TYPES = [
        'VO' => 'Version Originale',
        'DUB' => 'Version Dubbée',
        'VOST' => 'Version Originale Sous-Titrée',
        'DUBST' => 'Version Dubbée Sous-Titrée',
        'MUTE' => 'Version Muette'
    ];

    public const FORMATS = [
        'FTR' => 'Long métrage (Feature)',
        'SHR' => 'Court métrage (Short)',
        'TRL' => 'Bande annonce (Trailer)',
        'EPS' => 'Épisode',
        'TST' => 'Test',
        'RTG' => 'Rating',
        'POL' => 'Policy',
        'PSA' => 'Annonce publique',
        'ADV' => 'Publicité',
    ];

    /**
     * Film auquel appartient cette version
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Version originale (si cette version est un doublage)
     */
    public function originalVersion(): BelongsTo
    {
        return $this->belongsTo(Version::class, 'ov_id');
    }

    /**
     * Versions doublées dérivées de cette VO
     */
    public function dubbedVersions(): HasMany
    {
        return $this->hasMany(Version::class, 'ov_id');
    }

    /**
     * Langue audio de cette version
     */
    public function audioLanguage(): BelongsTo
    {
        return $this->belongsTo(Lang::class, 'audio_lang', 'iso_639_1');
    }

    /**
     * Langue des sous-titres de cette version
     */
    public function subtitleLanguage(): BelongsTo
    {
        return $this->belongsTo(Lang::class, 'sub_lang', 'iso_639_1');
    }

    /**
     * DCPs de cette version
     */
    public function dcps(): HasMany
    {
        return $this->hasMany(Dcp::class);
    }

    /**
     * DCP principal validé de cette version
     */
    public function validDcp(): HasOne
    {
        return $this->hasOne(Dcp::class)->where('is_valid', true)->latest('validated_at');
    }

    /**
     * Relation many-to-many avec les screenings
     */
    public function screenings(): BelongsToMany
    {
        return $this->belongsToMany(Screenings::class, 'versions_screenings', 'version_id', 'screening_id')
                    ->withPivot(['notes'])
                    ->withTimestamps();
    }

    /**
     * Scope pour les versions originales
     */
    public function scopeOriginal($query)
    {
        return $query->where('type', 'VO');
    }

    /**
     * Scope pour les versions doublées
     */
    public function scopeDubbed($query)
    {
        return $query->whereIn('type', ['DUB', 'DUBST']);
    }

    /**
     * Scope pour les versions sous-titrées
     */
    public function scopeSubtitled($query)
    {
        return $query->whereIn('type', ['VOST', 'DUBST']);
    }

    /**
     * Vérifie si cette version est la version originale
     */
    public function isOriginal(): bool
    {
        return $this->type === 'VO';
    }

    /**
     * Vérifie si cette version a un DCP valide
     */
    public function hasValidDcp(): bool
    {
        return $this->dcps()->where('is_valid', true)->exists();
    }

    /**
     * Génère la nomenclature automatiquement
     */
    public function generateNomenclature(): string
    {
        $parts = [
            $this->movie->title,
            $this->type,
            $this->audio_lang,
        ];

        if ($this->sub_lang) {
            $parts[] = 'ST' . $this->sub_lang;
        }

        if ($this->accessibility) {
            $parts[] = $this->accessibility;
        }

        return implode('_', $parts);
    }

    /**
     * Boot method pour générer la nomenclature automatiquement
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($version) {
            if (empty($version->generated_nomenclature)) {
                $version->generated_nomenclature = $version->generateNomenclature();
            }
        });
    }
}
