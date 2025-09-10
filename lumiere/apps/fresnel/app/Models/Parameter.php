<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Parameter extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'name',
        'code',
        'type',
        'possible_values',
        'description',
        'is_active',
        'is_system',
        'extraction_source',
        'extraction_pattern',
        'validation_rules',
        'default_value',
        'category',
    ];
    
    protected $casts = [
        'possible_values' => 'array',
        'validation_rules' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];
    
    // Constantes des types
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_BOOL = 'bool';
    const TYPE_FLOAT = 'float';
    const TYPE_DATE = 'date';
    const TYPE_JSON = 'json';
    
    // Constantes des sources d'extraction
    const SOURCE_DCP = 'DCP';
    const SOURCE_METADATA = 'metadata';
    const SOURCE_MANUAL = 'manual';
    const SOURCE_AUTO = 'auto';
    
    // Constantes des catégories
    const CATEGORY_AUDIO = 'audio';
    const CATEGORY_VIDEO = 'video';
    const CATEGORY_SUBTITLE = 'subtitle';
    const CATEGORY_CONTENT = 'content';
    const CATEGORY_TECHNICAL = 'technical';
    
    /**
     * Configuration du logging d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Paramètre créé',
                'updated' => 'Paramètre modifié',
                'deleted' => 'Paramètre supprimé',
                default => $eventName
            });
    }
    
    /**
     * Relation avec les nomenclatures des festivals (ancienne)
     */
    public function nomenclatures(): HasMany
    {
        return $this->hasMany(Nomenclature::class);
    }
    
    /**
     * Relation avec festival_parameters (nouvelle)
     */
    public function festivalParameters(): HasMany
    {
        return $this->hasMany(FestivalParameter::class);
    }
    
    /**
     * Relation many-to-many avec les festivals via festival_parameters
     */
    public function festivals(): BelongsToMany
    {
        return $this->belongsToMany(Festival::class, 'festival_parameters')
                    ->withPivot([
                        'is_enabled', 
                        'custom_default_value', 
                        'custom_formatting_rules',
                        'display_order',
                        'festival_specific_notes'
                    ])
                    ->withTimestamps();
    }
    
    /**
     * Relation many-to-many avec les films via movie_parameters
     */
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_parameters')
                    ->withPivot(['value', 'status', 'extraction_method', 'metadata'])
                    ->withTimestamps();
    }
    
    /**
     * Scope pour les paramètres actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
    
    /**
     * Scope pour les paramètres système
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }
    
    /**
     * Scope pour les paramètres disponibles pour sélection par les managers
     * Les paramètres système sont toujours disponibles, les autres doivent être activés
     */
    public function scopeAvailableForFestivals($query)
    {
        return $query->where(function ($query) {
            $query->where('is_system', true)
                  ->orWhere('is_active', true);
        });
    }
    
    /**
     * Scope pour les paramètres non-système (modifiables)
     */
    public function scopeNonSystem($query)
    {
        return $query->where('is_system', false);
    }
    
    /**
     * Obtenir tous les types disponibles
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_STRING => 'Chaîne de caractères',
            self::TYPE_INT => 'Nombre entier',
            self::TYPE_BOOL => 'Booléen',
            self::TYPE_FLOAT => 'Nombre décimal',
            self::TYPE_DATE => 'Date',
            self::TYPE_JSON => 'JSON'
        ];
    }
    
    /**
     * Obtenir toutes les catégories disponibles
     */
    public static function getAvailableCategories(): array
    {
        // Récupérer les catégories existantes depuis la base de données
        $dbCategories = static::distinct('category')
            ->whereNotNull('category')
            ->pluck('category')
            ->mapWithKeys(function ($category) {
                return [$category => ucfirst($category)];
            })
            ->toArray();
        
        // Catégories par défaut (au cas où la DB serait vide)
        $defaultCategories = [
            self::CATEGORY_AUDIO => 'Audio',
            self::CATEGORY_VIDEO => 'Vidéo',
            self::CATEGORY_SUBTITLE => 'Sous-titres',
            self::CATEGORY_CONTENT => 'Contenu',
            self::CATEGORY_TECHNICAL => 'Technique',
            'metadata' => 'Metadata',
            'accessibility' => 'Accessibility'
        ];
        
        // Fusionner les catégories par défaut avec celles de la DB
        return array_merge($defaultCategories, $dbCategories);
    }
    
    /**
     * Obtenir toutes les sources d'extraction disponibles
     */
    public static function getAvailableExtractionSources(): array
    {
        return [
            self::SOURCE_DCP => 'Extraction DCP',
            self::SOURCE_METADATA => 'Métadonnées',
            self::SOURCE_MANUAL => 'Saisie manuelle',
            self::SOURCE_AUTO => 'Automatique'
        ];
    }
    
    /**
     * Valider une valeur selon les règles du paramètre
     */
    public function validateValue($value): bool
    {
        // Vérifier le type
        if (!$this->validateType($value)) {
            return false;
        }
        
        // Vérifier les valeurs possibles
        if (!empty($this->possible_values) && !in_array($value, $this->possible_values)) {
            return false;
        }
        
        // Appliquer les règles de validation personnalisées
        if (!empty($this->validation_rules)) {
            // Logique de validation personnalisée basée sur les règles
            return $this->applyValidationRules($value);
        }
        
        return true;
    }
    
    /**
     * Valider le type d'une valeur
     */
    private function validateType($value): bool
    {
        return match ($this->type) {
            self::TYPE_STRING => is_string($value),
            self::TYPE_INT => is_int($value) || (is_string($value) && ctype_digit($value)),
            self::TYPE_BOOL => is_bool($value) || in_array($value, ['true', 'false', '1', '0', 1, 0]),
            self::TYPE_FLOAT => is_float($value) || is_numeric($value),
            self::TYPE_DATE => $this->isValidDate($value),
            self::TYPE_JSON => $this->isValidJson($value),
            default => true
        };
    }
    
    /**
     * Vérifier si une valeur est une date valide
     */
    private function isValidDate($value): bool
    {
        return $value instanceof \DateTime || 
               (is_string($value) && strtotime($value) !== false);
    }
    
    /**
     * Vérifier si une valeur est du JSON valide
     */
    private function isValidJson($value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Appliquer les règles de validation personnalisées
     */
    private function applyValidationRules($value): bool
    {
        // Implémentation des règles de validation personnalisées
        // À développer selon les besoins spécifiques
        return true;
    }
}
