<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Parameter extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'type',
        'possible_values',
        'description',
        'short_description',
        'detailed_description',
        'example_value',
        'use_cases',
        'icon',
        'color',
        'is_active',
        'is_system',
        'extraction_source',
        'extraction_pattern',
        'validation_rules',
        'default_value',
        'format_rules',
        'category',
    ];

    protected $casts = [
        'possible_values' => 'array',
        'validation_rules' => 'array',
        'use_cases' => 'array',
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

    const CATEGORY_ACCESSIBILITY = 'accessibility';

    const CATEGORY_METADATA = 'metadata';

    // Constantes des règles de formatage
    const FORMAT_NO_SPACING = 'no_spacing';
    const FORMAT_CAMEL_CASE = 'camel_case';
    const FORMAT_SNAKE_CASE = 'snake_case';
    const FORMAT_KEBAB_CASE = 'kebab_case';
    const FORMAT_TITLE_CASE = 'title_case';
    const FORMAT_LOWER_CASE = 'lower_case';
    const FORMAT_UPPER_CASE = 'upper_case';
    const FORMAT_TRIM = 'trim';
    const FORMAT_NO_ACCENTS = 'no_accents';
    const FORMAT_BRACKETS = 'brackets';
    const FORMAT_EACH_BRACKETS = 'each_brackets';
    const FORMAT_PARENTHESES = 'parentheses';
    const FORMAT_EACH_PARENTHESES = 'each_parentheses';

    /**
     * Configuration du logging d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
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
                'festival_specific_notes',
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
            self::TYPE_JSON => 'JSON',
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
            'accessibility' => 'Accessibility',
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
            self::SOURCE_AUTO => 'Automatique',
        ];
    }

    /**
     * Valider une valeur selon les règles du paramètre
     */
    public function validateValue($value): bool
    {
        // Vérifier le type
        if (! $this->validateType($value)) {
            return false;
        }

        // Vérifier les valeurs possibles
        if (! empty($this->possible_values) && ! in_array($value, $this->possible_values)) {
            return false;
        }

        // Appliquer les règles de validation personnalisées
        if (! empty($this->validation_rules)) {
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
        if (! is_string($value)) {
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

    /**
     * Obtenir les règles de formatage disponibles
     */
    public static function getAvailableFormatRules(): array
    {
        return [
            self::FORMAT_NO_SPACING => 'Supprimer les espaces',
            self::FORMAT_LOWER_CASE => 'Tout en minuscules',
            self::FORMAT_UPPER_CASE => 'Tout en majuscules',
            self::FORMAT_CAMEL_CASE => 'camelCase',
            self::FORMAT_SNAKE_CASE => 'snake_case',
            self::FORMAT_KEBAB_CASE => 'kebab-case',
            self::FORMAT_TITLE_CASE => 'Title Case',
            self::FORMAT_TRIM => 'Supprimer espaces début/fin',
            self::FORMAT_NO_ACCENTS => 'Supprimer les accents',
            self::FORMAT_BRACKETS => 'Entourer de crochets [texte]',
            self::FORMAT_EACH_BRACKETS => 'Chaque mot entre crochets [mot1] [mot2]',
            self::FORMAT_PARENTHESES => 'Entourer de parenthèses (texte)',
            self::FORMAT_EACH_PARENTHESES => 'Chaque mot entre parenthèses (mot1) (mot2)',
        ];
    }

    /**
     * Appliquer les règles de formatage à une valeur
     */
    public function applyFormatting(string $value): string
    {
        if (empty($this->format_rules)) {
            return $value;
        }

        // Séparer les règles par virgule
        $rules = array_map('trim', explode(',', $this->format_rules));

        foreach ($rules as $rule) {
            $value = $this->applySingleFormatRule($value, $rule);
        }

        return $value;
    }

    /**
     * Appliquer une seule règle de formatage
     */
    private function applySingleFormatRule(string $value, string $rule): string
    {
        return match ($rule) {
            self::FORMAT_NO_SPACING => str_replace(' ', '', $value),
            self::FORMAT_UPPER_CASE => strtoupper($value),
            self::FORMAT_LOWER_CASE => strtolower($value),
            self::FORMAT_TRIM => trim($value),
            self::FORMAT_TITLE_CASE => ucwords(strtolower($value)),
            self::FORMAT_CAMEL_CASE => $this->toCamelCase($value),
            self::FORMAT_SNAKE_CASE => $this->toSnakeCase($value),
            self::FORMAT_KEBAB_CASE => $this->toKebabCase($value),
            self::FORMAT_NO_ACCENTS => $this->removeAccents($value),
            self::FORMAT_BRACKETS => '[' . $value . ']',
            self::FORMAT_EACH_BRACKETS => $this->addBracketsToEachWord($value),
            self::FORMAT_PARENTHESES => '(' . $value . ')',
            self::FORMAT_EACH_PARENTHESES => $this->addParenthesesToEachWord($value),
            default => $value // Règle inconnue, retourner la valeur sans modification
        };
    }

    /**
     * Convertir en camelCase
     */
    private function toCamelCase(string $value): string
    {
        // Remplacer espaces et tirets par des espaces, puis camelCase
        $value = str_replace(['-', '_'], ' ', $value);
        $value = ucwords(strtolower($value));
        $value = str_replace(' ', '', $value);
        return lcfirst($value);
    }

    /**
     * Convertir en snake_case
     */
    private function toSnakeCase(string $value): string
    {
        // D'abord remplacer les espaces et tirets par des underscores
        $value = str_replace([' ', '-'], '_', $value);
        // Puis ajouter des underscores avant les majuscules
        $value = preg_replace('/([A-Z])/', '_$1', $value);
        // Nettoyer les underscores multiples et en début/fin
        $value = preg_replace('/_+/', '_', $value);
        return strtolower(trim($value, '_'));
    }

    /**
     * Convertir en kebab-case
     */
    private function toKebabCase(string $value): string
    {
        // D'abord remplacer les espaces et underscores par des tirets
        $value = str_replace([' ', '_'], '-', $value);
        // Puis ajouter des tirets avant les majuscules
        $value = preg_replace('/([A-Z])/', '-$1', $value);
        // Nettoyer les tirets multiples et en début/fin
        $value = preg_replace('/-+/', '-', $value);
        return strtolower(trim($value, '-'));
    }

    /**
     * Supprimer les accents
     */
    private function removeAccents(string $value): string
    {
        $accents = [
            'à', 'á', 'â', 'ã', 'ä', 'å', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å',
            'è', 'é', 'ê', 'ë', 'È', 'É', 'Ê', 'Ë',
            'ì', 'í', 'î', 'ï', 'Ì', 'Í', 'Î', 'Ï',
            'ò', 'ó', 'ô', 'õ', 'ö', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö',
            'ù', 'ú', 'û', 'ü', 'Ù', 'Ú', 'Û', 'Ü',
            'ç', 'Ç', 'ñ', 'Ñ'
        ];
        
        $without = [
            'a', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'A',
            'e', 'e', 'e', 'e', 'E', 'E', 'E', 'E',
            'i', 'i', 'i', 'i', 'I', 'I', 'I', 'I',
            'o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'O',
            'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U',
            'c', 'C', 'n', 'N'
        ];
        
        return str_replace($accents, $without, $value);
    }

    /**
     * Ajouter des crochets autour de chaque mot
     */
    private function addBracketsToEachWord(string $value): string
    {
        $words = explode(' ', trim($value));
        $words = array_filter($words); // Supprimer les espaces vides
        $words = array_map(fn($word) => '[' . $word . ']', $words);
        return implode(' ', $words);
    }

    /**
     * Ajouter des parenthèses autour de chaque mot
     */
    private function addParenthesesToEachWord(string $value): string
    {
        $words = explode(' ', trim($value));
        $words = array_filter($words); // Supprimer les espaces vides
        $words = array_map(fn($word) => '(' . $word . ')', $words);
        return implode(' ', $words);
    }
}
