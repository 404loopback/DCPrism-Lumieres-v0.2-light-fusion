<?php

namespace Modules\Fresnel\app\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MovieParameter extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'movie_id',
        'parameter_id',
        'value',
        'status',
        'extraction_method',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes pour les statuts
    public const STATUS_PENDING = 'pending';

    public const STATUS_EXTRACTED = 'extracted';

    public const STATUS_VALIDATED = 'validated';

    public const STATUS_ERROR = 'error';

    // Constantes pour les méthodes d'extraction
    public const EXTRACTION_MANUAL = 'manual';

    public const EXTRACTION_AUTO = 'auto';

    public const EXTRACTION_COMPUTED = 'computed';

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Paramètre de film créé',
                'updated' => 'Paramètre de film modifié',
                'deleted' => 'Paramètre de film supprimé',
                default => $eventName
            });
    }

    /**
     * Relation avec le film
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Relation avec le paramètre
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    /**
     * Récupérer tous les statuts disponibles
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_EXTRACTED => 'Extrait',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_ERROR => 'Erreur',
        ];
    }

    /**
     * Récupérer toutes les méthodes d'extraction disponibles
     */
    public static function getExtractionMethods(): array
    {
        return [
            self::EXTRACTION_MANUAL => 'Manuel',
            self::EXTRACTION_AUTO => 'Automatique',
            self::EXTRACTION_COMPUTED => 'Calculé',
        ];
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour filtrer par méthode d'extraction
     */
    public function scopeByExtractionMethod($query, string $method)
    {
        return $query->where('extraction_method', $method);
    }

    /**
     * Scope pour les paramètres validés
     */
    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    /**
     * Scope pour les paramètres en erreur
     */
    public function scopeWithErrors($query)
    {
        return $query->where('status', self::STATUS_ERROR);
    }

    /**
     * Marquer le paramètre comme extrait
     */
    public function markAsExtracted(string $value, string $method = self::EXTRACTION_AUTO, ?array $metadata = null): void
    {
        $this->update([
            'value' => $this->formatValueBeforeSaving($value),
            'status' => self::STATUS_EXTRACTED,
            'extraction_method' => $method,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Marquer le paramètre comme validé
     */
    public function markAsValidated(?array $metadata = null): void
    {
        $this->update([
            'status' => self::STATUS_VALIDATED,
            'metadata' => array_merge($this->metadata ?? [], $metadata ?? []),
        ]);
    }

    /**
     * Marquer le paramètre en erreur
     */
    public function markAsError(string $errorMessage, ?array $metadata = null): void
    {
        $this->update([
            'status' => self::STATUS_ERROR,
            'metadata' => array_merge($this->metadata ?? [], [
                'error_message' => $errorMessage,
                'error_date' => Carbon::now()->toDateTimeString(),
            ], $metadata ?? []),
        ]);
    }

    /**
     * Vérifier si le paramètre est validé
     */
    public function isValidated(): bool
    {
        return $this->status === self::STATUS_VALIDATED;
    }

    /**
     * Vérifier si le paramètre a une erreur
     */
    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * Obtenir le message d'erreur s'il y en a un
     */
    public function getErrorMessage(): ?string
    {
        return $this->metadata['error_message'] ?? null;
    }

    /**
     * Mutateur pour appliquer automatiquement le formatage à la valeur
     */
    protected function setValue(string $value): void
    {
        $this->attributes['value'] = $this->formatValueBeforeSaving($value);
    }
    
    /**
     * Formatter la valeur avant sauvegarde selon les règles du paramètre
     */
    private function formatValueBeforeSaving(string $value): string
    {
        // Charger le paramètre si pas déjà chargé
        if (!$this->relationLoaded('parameter')) {
            $this->load('parameter');
        }
        
        $parameter = $this->parameter;
        
        // Si le paramètre a des règles de formatage, les appliquer
        if ($parameter && !empty($parameter->format_rules)) {
            try {
                $formatted = $parameter->applyFormatting($value);
                \Log::debug('[MovieParameter] Applied formatting to parameter value', [
                    'movie_parameter_id' => $this->id,
                    'parameter_id' => $parameter->id,
                    'rules' => $parameter->format_rules,
                    'original' => $value,
                    'formatted' => $formatted,
                ]);
                return $formatted;
            } catch (\Exception $e) {
                \Log::warning('[MovieParameter] Failed to apply formatting, using original value', [
                    'movie_parameter_id' => $this->id,
                    'parameter_id' => $parameter?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Retourner la valeur originale si pas de formatage ou en cas d'erreur
        return $value;
    }

    /**
     * Obtenir la valeur formatée selon le type de paramètre
     */
    public function getFormattedValue(): string
    {
        if (empty($this->value)) {
            return 'N/A';
        }

        // Si le paramètre a une unité, l'ajouter
        if ($this->parameter && $this->parameter->unit) {
            return $this->value.' '.$this->parameter->unit;
        }

        return (string) $this->value;
    }
}
