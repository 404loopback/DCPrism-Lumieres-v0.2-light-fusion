<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Movie extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'source_email',
        'status',
        'format',
        'created_by',
        'description',
        'duration',
        'genre',
        'year',
        'country',
        'language',
        'backblaze_folder',
        'backblaze_file_id',
        'upload_progress',
        'DCP_metadata',
        'technical_notes',
        'validated_by',
        'validated_at',
        'file_path',
        'file_size',
        'original_filename',
        'uploaded_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'DCP_metadata' => 'array',
        'upload_progress' => 'integer',
        'validated_at' => 'datetime',
        'uploaded_at' => 'datetime',
    ];

    // Constantes des statuts - Workflow unifié DCPrism
    const STATUS_PENDING = 'pending';

    const STATUS_CREATED = 'created';

    const STATUS_FILM_CREATED = 'created'; // Alias pour STATUS_CREATED

    const STATUS_SOURCE_VALIDATED = 'source_validated';

    const STATUS_VERSIONS_VALIDATED = 'versions_validated';

    const STATUS_VERSIONS_REJECTED = 'versions_rejected';

    const STATUS_UPLOADING = 'uploading';

    const STATUS_UPLOAD_OK = 'upload_ok';

    const STATUS_UPLOADS_OK = 'upload_ok'; // Alias pour STATUS_UPLOAD_OK

    const STATUS_UPLOAD_ERROR = 'upload_error';

    const STATUS_IN_REVIEW = 'in_review';

    const STATUS_VALIDATED = 'validated';

    const STATUS_VALIDATION_OK = 'validated'; // Alias pour STATUS_VALIDATED

    const STATUS_VALIDATION_ERROR = 'validation_error';

    const STATUS_READY = 'ready';

    const STATUS_DISTRIBUTED = 'distributed';

    const STATUS_DISTRIBUTION_OK = 'distributed'; // Alias pour STATUS_DISTRIBUTED

    const STATUS_DISTRIBUTION_ERROR = 'distribution_error';

    const STATUS_REJECTED = 'rejected';

    const STATUS_ERROR = 'error';

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
                'created' => 'Film créé',
                'updated' => 'Film modifié',
                'deleted' => 'Film supprimé',
                default => $eventName
            });
    }

    /**
     * Récupérer tous les statuts disponibles
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CREATED => 'Film créé',
            self::STATUS_SOURCE_VALIDATED => 'Source validée',
            self::STATUS_VERSIONS_VALIDATED => 'Versions validées',
            self::STATUS_VERSIONS_REJECTED => 'Versions refusées',
            self::STATUS_UPLOADING => 'Téléversement en cours',
            self::STATUS_UPLOAD_OK => 'Téléversement terminé',
            self::STATUS_UPLOAD_ERROR => 'Erreur de téléversement',
            self::STATUS_IN_REVIEW => 'En cours de validation',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_VALIDATION_ERROR => 'Erreur de validation',
            self::STATUS_READY => 'Prêt pour distribution',
            self::STATUS_DISTRIBUTED => 'Distribué',
            self::STATUS_DISTRIBUTION_ERROR => 'Erreur de distribution',
            self::STATUS_REJECTED => 'Refusé',
            self::STATUS_ERROR => 'Erreur',
        ];
    }

    /**
     * Relation many-to-many avec les festivals
     */
    public function festivals(): BelongsToMany
    {
        return $this->belongsToMany(Festival::class, 'movie_festivals')
            ->withPivot(['submission_status', 'selected_versions', 'technical_notes', 'priority'])
            ->withTimestamps();
    }

    /**
     * Relation avec les versions du film
     */
    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }

    /**
     * Relation avec les DCPs du film
     */
    public function dcps(): HasMany
    {
        return $this->hasMany(Dcp::class);
    }

    /**
     * DCPs valides du film
     */
    public function validDcps(): HasMany
    {
        return $this->hasMany(Dcp::class)->where('is_valid', true);
    }

    /**
     * Version originale du film
     */
    public function originalVersion(): HasMany
    {
        return $this->hasMany(Version::class)->where('type', 'VO');
    }

    /**
     * Relation avec l'utilisateur qui a validé le movie
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relation avec le compte upload (source)
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_email', 'email');
    }

    /**
     * Relation avec les paramètres du film
     */
    public function movieParameters(): HasMany
    {
        return $this->hasMany(MovieParameter::class);
    }

    /**
     * Relation many-to-many avec les paramètres via movie_parameters
     */
    public function parameters(): BelongsToMany
    {
        return $this->belongsToMany(Parameter::class, 'movie_parameters')
            ->withPivot(['value', 'status', 'extraction_method', 'metadata'])
            ->withTimestamps();
    }

    /**
     * Relation avec les uploads de fichiers
     */
    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    /**
     * Scope pour filtrer par source
     */
    public function scopeByUploader($query, string $email)
    {
        return $query->where('source_email', $email);
    }

    /**
     * Scope pour films avec versions validées
     */
    public function scopeVersionsValidated($query)
    {
        return $query->where('status', self::STATUS_VERSIONS_VALIDATED);
    }

    /**
     * Scope pour films refusés
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope pour films avec uploads OK
     */
    public function scopeUploaded($query)
    {
        return $query->where('status', self::STATUS_UPLOAD_OK);
    }

    /**
     * Scope pour films validés
     */
    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    /**
     * Scope pour films avec erreurs
     */
    public function scopeWithErrors($query)
    {
        return $query->where('status', self::STATUS_ERROR);
    }

    /**
     * Scope pour films distribués
     */
    public function scopeDistributed($query)
    {
        return $query->where('status', self::STATUS_DISTRIBUTED);
    }

    /**
     * Scope pour films prêts à distribuer
     */
    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }

    /**
     * Scope pour films en attente de validation
     */
    public function scopePendingValidation($query)
    {
        return $query->where('status', self::STATUS_IN_REVIEW);
    }

    /**
     * Scope pour films techniquement validés
     */
    public function scopeTechnicallyValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    /**
     * Scope pour films en attente de validation technique
     */
    public function scopePendingTechnicalValidation($query)
    {
        return $query->where('status', self::STATUS_IN_REVIEW);
    }

    /**
     * Obtenir la valeur d'un paramètre spécifique
     */
    public function getParameterValue(string $parameterName): ?string
    {
        $movieParameter = $this->movieParameters()
            ->whereHas('parameter', function ($query) use ($parameterName) {
                $query->where('name', $parameterName);
            })
            ->first();

        return $movieParameter ? $movieParameter->value : null;
    }

    /**
     * Générer la nomenclature du film pour un festival donné
     */
    public function generateNomenclature(int $festivalId): string
    {
        $nomenclatures = Nomenclature::where('festival_id', $festivalId)
            ->with('parameter')
            ->where('is_active', true)
            ->orderBy('order_position')
            ->get();

        if ($nomenclatures->isEmpty()) {
            return $this->title;
        }

        $parts = [];

        foreach ($nomenclatures as $nomenclature) {
            $parameter = $nomenclature->resolveParameter();
            if (!$parameter) {
                continue;
            }
            $parameterValue = $this->getParameterValue($parameter->name);

            if (! empty($parameterValue)) {
                $part = ($nomenclature->prefix ?? '').$parameterValue.($nomenclature->suffix ?? '');
                if (! empty($part)) {
                    $parts[] = $part;
                }
            }
        }

        return ! empty($parts) ? implode('_', $parts) : $this->title;
    }
}
