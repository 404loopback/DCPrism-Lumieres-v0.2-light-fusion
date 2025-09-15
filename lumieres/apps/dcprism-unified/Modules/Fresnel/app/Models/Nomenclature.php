<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Nomenclature extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'festival_id',
        'parameter_id',
        'festival_parameter_id',
        'order_position',
        'separator',
        'is_active',
        'is_required',
        'prefix',
        'suffix',
        'default_value',
        'formatting_rules',
        'conditional_rules'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'order_position' => 'integer',
        'formatting_rules' => 'array',
        'conditional_rules' => 'array'
    ];
    
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
                'created' => 'Nomenclature créée',
                'updated' => 'Nomenclature modifiée',
                'deleted' => 'Nomenclature supprimée',
                default => $eventName
            });
    }
    
    /**
     * Relation avec le festival
     */
    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }
    
    /**
     * Relation avec le paramètre
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }
    
    /**
     * Relation avec le paramètre de festival (nouvelle architecture)
     */
    public function festivalParameter(): BelongsTo
    {
        return $this->belongsTo(FestivalParameter::class);
    }
    
    /**
     * Scope pour les nomenclatures actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope pour les nomenclatures d'un festival
     */
    public function scopeForFestival($query, int $festivalId)
    {
        return $query->where('festival_id', $festivalId);
    }
    
    /**
     * Scope pour ordonner par position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_position');
    }
    
    /**
     * Formater une valeur selon les règles de la nomenclature
     */
    public function formatValue($rawValue, ?Movie $movie = null): ?string
    {
        // Si pas de valeur, utiliser la valeur par défaut
        if (empty($rawValue)) {
            $rawValue = $this->default_value;
        }
        
        // Si toujours pas de valeur et que c'est requis, retourner null
        if (empty($rawValue)) {
            return $this->is_required ? null : '';
        }
        
        // Appliquer les règles conditionnelles si définies
        if (!empty($this->conditional_rules) && $movie) {
            $rawValue = $this->applyConditionalRules($rawValue, $movie);
        }
        
        // Appliquer les règles de formatage
        $formattedValue = $this->applyFormattingRules($rawValue);
        
        // Ajouter préfixe et suffixe
        $result = '';
        if (!empty($this->prefix)) {
            $result .= $this->prefix;
        }
        
        $result .= $formattedValue;
        
        if (!empty($this->suffix)) {
            $result .= $this->suffix;
        }
        
        // Ajouter le séparateur à la fin si défini
        if (!empty($this->separator)) {
            $result .= $this->separator;
        }
        
        return $result;
    }
    
    /**
     * Appliquer les règles de formatage
     */
    private function applyFormattingRules($value): string
    {
        if (empty($this->formatting_rules)) {
            return (string) $value;
        }
        
        $formatted = $value;
        
        foreach ($this->formatting_rules as $rule => $config) {
            $formatted = match ($rule) {
                'uppercase' => strtoupper($formatted),
                'lowercase' => strtolower($formatted),
                'capitalize' => ucfirst(strtolower($formatted)),
                'trim' => trim($formatted),
                'max_length' => substr($formatted, 0, $config),
                'replace' => str_replace($config['search'], $config['replace'], $formatted),
                'regex' => preg_replace($config['pattern'], $config['replacement'], $formatted),
                'pad_left' => str_pad($formatted, $config['length'], $config['char'] ?? '0', STR_PAD_LEFT),
                'pad_right' => str_pad($formatted, $config['length'], $config['char'] ?? '0', STR_PAD_RIGHT),
                default => $formatted
            };
        }
        
        return $formatted;
    }
    
    /**
     * Appliquer les règles conditionnelles
     */
    private function applyConditionalRules($value, Movie $movie): string
    {
        if (empty($this->conditional_rules)) {
            return $value;
        }
        
        foreach ($this->conditional_rules as $condition) {
            if ($this->evaluateCondition($condition['if'], $movie)) {
                return $condition['then'] ?? $value;
            }
        }
        
        return $value;
    }
    
    /**
     * Accesseur pour le titre d'affichage
     */
    public function getTitleAttribute(): string
    {
        $parts = [];
        
        if (!empty($this->prefix)) {
            $parts[] = $this->prefix;
        }
        
        if ($this->parameter) {
            $parts[] = $this->parameter->name ?? 'Paramètre';
        }
        
        if (!empty($this->suffix)) {
            $parts[] = $this->suffix;
        }
        
        return implode(' - ', array_filter($parts)) ?: "Nomenclature #{$this->id}";
    }

    /**
     * Évaluer une condition
     */
    private function evaluateCondition(array $condition, Movie $movie): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $expectedValue = $condition['value'] ?? null;
        
        if (!$field) {
            return false;
        }
        
        // Récupérer la valeur du champ depuis le movie ou ses relations
        $actualValue = $this->getMovieFieldValue($movie, $field);
        
        return match ($operator) {
            '=' => $actualValue == $expectedValue,
            '!=' => $actualValue != $expectedValue,
            '>' => $actualValue > $expectedValue,
            '<' => $actualValue < $expectedValue,
            '>=' => $actualValue >= $expectedValue,
            '<=' => $actualValue <= $expectedValue,
            'contains' => str_contains($actualValue, $expectedValue),
            'starts_with' => str_starts_with($actualValue, $expectedValue),
            'ends_with' => str_ends_with($actualValue, $expectedValue),
            'in' => in_array($actualValue, (array) $expectedValue),
            'not_in' => !in_array($actualValue, (array) $expectedValue),
            default => false
        };
    }
    
    /**
     * Récupérer la valeur d'un champ depuis un movie
     */
    private function getMovieFieldValue(Movie $movie, string $field): mixed
    {
        // Gestion des champs directs du movie
        if (in_array($field, $movie->getFillable())) {
            return $movie->getAttribute($field);
        }
        
        // Gestion des paramètres du movie
        if (str_starts_with($field, 'parameter.')) {
            $parameterName = substr($field, 10);
            return $movie->getParameterValue($parameterName);
        }
        
        // Gestion des relations
        if (str_contains($field, '.')) {
            $parts = explode('.', $field, 2);
            $relation = $parts[0];
            $relationField = $parts[1];
            
            if ($movie->relationLoaded($relation)) {
                $related = $movie->getRelation($relation);
                return $related ? $related->getAttribute($relationField) : null;
            }
        }
        
        return null;
    }
    
    /**
     * Valider la configuration de la nomenclature
     */
    public function validateConfiguration(): array
    {
        $errors = [];
        
        // Vérifier que le paramètre existe et est actif
        if (!$this->parameter || !$this->parameter->is_active) {
            $errors[] = "Le paramètre associé n'existe pas ou n'est pas actif";
        }
        
        // Vérifier que la position est unique pour le festival
        $duplicate = self::where('festival_id', $this->festival_id)
                         ->where('order_position', $this->order_position)
                         ->where('id', '!=', $this->id)
                         ->exists();
        
        if ($duplicate) {
            $errors[] = "La position {$this->order_position} est déjà utilisée pour ce festival";
        }
        
        // Valider les règles de formatage JSON
        if (!empty($this->formatting_rules) && !is_array($this->formatting_rules)) {
            $errors[] = "Les règles de formatage doivent être un tableau valide";
        }
        
        return $errors;
    }
    
    /**
     * Obtenir un aperçu de la nomenclature formatée
     */
    public function getPreview(?string $sampleValue = null): string
    {
        $value = $sampleValue ?? $this->parameter->name ?? 'SAMPLE';
        return $this->formatValue($value) ?? '';
    }
    
    /**
     * Réorganiser les nomenclatures en évitant les violations de contrainte unique
     */
    public static function reorderSafely(array $orderArray, int $festivalId): void
    {
        \Log::info('reorderSafely called', ['orderArray' => $orderArray, 'festivalId' => $festivalId]);
        
        // Convertir le tableau d'ordre [id1, id2, id3] en tableau id => position
        $orderData = [];
        foreach ($orderArray as $position => $id) {
            $orderData[$id] = $position + 1; // Les positions commencent à 1
        }
        
        \Log::info('Converted order data', ['orderData' => $orderData]);
        
        \DB::transaction(function () use ($orderData, $festivalId) {
            // Récupérer tous les IDs affectés
            $affectedIds = array_keys($orderData);
            \Log::info('Affected IDs', ['affectedIds' => $affectedIds]);
            
            // Étape 1: Mettre toutes les positions affectées en négatif temporairement
            // pour éviter les contraintes de unicité
            $updated1 = self::whereIn('id', $affectedIds)
                ->where('festival_id', $festivalId)
                ->update([
                    'order_position' => \DB::raw('order_position * -1 - 10000'),
                    'updated_at' => now()
                ]);
            \Log::info('Step 1 updated rows', ['count' => $updated1]);
            
            // Étape 2: Appliquer les nouvelles positions définitives
            foreach ($orderData as $id => $position) {
                $updated2 = self::where('id', $id)
                    ->where('festival_id', $festivalId)
                    ->update([
                        'order_position' => $position,
                        'updated_at' => now()
                    ]);
                \Log::info('Step 2 update', ['id' => $id, 'position' => $position, 'updated' => $updated2]);
            }
        });
        
        \Log::info('reorderSafely completed');
    }
}
