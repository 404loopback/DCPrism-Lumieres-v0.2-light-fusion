<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FestivalParameter extends Model
{
    protected $fillable = [
        'festival_id',
        'parameter_id',
        'is_enabled',
        'custom_default_value',
        'custom_formatting_rules',
        'display_order',
        'festival_specific_notes'
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'custom_formatting_rules' => 'array',
        'display_order' => 'integer'
    ];

    /**
     * Relation vers le festival
     */
    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    /**
     * Relation vers le paramètre
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    /**
     * Scope pour les paramètres activés
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope pour ordonner par display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at');
    }

    /**
     * Scope pour un festival spécifique
     */
    public function scopeForFestival($query, $festivalId)
    {
        return $query->where('festival_id', $festivalId);
    }

    /**
     * Scope pour un paramètre spécifique
     */
    public function scopeForParameter($query, $parameterId)
    {
        return $query->where('parameter_id', $parameterId);
    }

    /**
     * Obtenir la valeur par défaut (personnalisée ou globale)
     */
    public function getEffectiveDefaultValue()
    {
        return $this->custom_default_value ?? $this->parameter->default_value;
    }

    /**
     * Obtenir les règles de formatage (personnalisées ou globales)
     */
    public function getEffectiveFormattingRules(): array
    {
        $globalRules = $this->parameter->validation_rules ?? [];
        $customRules = $this->custom_formatting_rules ?? [];
        
        return array_merge($globalRules, $customRules);
    }

    /**
     * Vérifier si le paramètre est système pour ce festival
     */
    public function isSystemParameter(): bool
    {
        return $this->parameter->is_system ?? false;
    }

    /**
     * Vérifier si ce paramètre peut être désactivé
     */
    public function canBeDisabled(): bool
    {
        return !$this->isSystemParameter();
    }
}
