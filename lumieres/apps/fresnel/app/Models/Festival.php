<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Festival extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'subdomain',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'max_storage',
        'backblaze_folder',
        'email',
        'submission_deadline',
        'max_file_size',
        'accept_submissions',
        'accepted_formats',
        'storage_status',
        'storage_info',
        'storage_last_tested_at',
        'nomenclature_separator',
        'nomenclature_template',
        'technical_requirements',
        'contact_phone',
        'website'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submission_deadline' => 'datetime',
        'is_active' => 'boolean',
        'accept_submissions' => 'boolean',
        'storage_info' => 'json',
        'storage_last_tested_at' => 'datetime',
        'accepted_formats' => 'json',
        'technical_requirements' => 'json'
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
                'created' => 'Festival créé',
                'updated' => 'Festival modifié',
                'deleted' => 'Festival supprimé',
                default => $eventName
            });
    }
    
    /**
     * Relation many-to-many avec les movies
     */
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_festivals')
                    ->withPivot(['submission_status', 'selected_versions', 'technical_notes', 'priority'])
                    ->withTimestamps();
    }
    
    /**
     * Relation avec les utilisateurs assignés
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_festivals')
                    ->withTimestamps();
    }
    
    /**
     * Relation avec les nomenclatures du festival (génération noms fichiers)
     */
    public function nomenclatures(): HasMany
    {
        return $this->hasMany(Nomenclature::class);
    }
    
    /**
     * Relation avec festival_parameters (configuration générale paramètres)
     */
    public function festivalParameters(): HasMany
    {
        return $this->hasMany(FestivalParameter::class);
    }

    /**
     * Relation many-to-many avec les paramètres via festival_parameters (nouvelle)
     */
    public function parameters(): BelongsToMany
    {
        return $this->belongsToMany(Parameter::class, 'festival_parameters')
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
     * Relation avec les paramètres activés seulement
     */
    public function activeParameters(): BelongsToMany
    {
        return $this->parameters()->wherePivot('is_enabled', true);
    }
    
    /**
     * Relation avec les paramètres système (obligatoires)
     */
    public function systemParameters(): BelongsToMany
    {
        return $this->parameters()
                    ->where('parameters.is_system', true)
                    ->where('parameters.is_active', true);
    }
    
    /**
     * Scope pour les festivals actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les festivals acceptant les soumissions
     */
    public function scopeAcceptingSubmissions($query)
    {
        return $query->where('accept_submissions', true)
                     ->where('submission_deadline', '>', now());
    }
    
    /**
     * Obtenir la nomenclature active ordonnée pour ce festival
     */
    public function getActiveNomenclature()
    {
        return $this->nomenclatures()
                   ->with('parameter')
                   ->where('is_active', true)
                   ->orderBy('order_position')
                   ->get();
    }

    /**
     * Obtenir un paramètre spécifique de la nomenclature
     */
    public function getNomenclatureParameter(string $parameterName): ?Nomenclature
    {
        return $this->nomenclatures()
                   ->whereHas('parameter', function($query) use ($parameterName) {
                       $query->where('name', $parameterName);
                   })
                   ->where('is_active', true)
                   ->first();
    }

    /**
     * Ajouter un paramètre à la nomenclature du festival
     */
    public function addParameterToNomenclature(int $parameterId, ?int $order = null, string $separator = '_', array $options = []): Nomenclature
    {
        $nomenclature = new Nomenclature([
            'festival_id' => $this->id,
            'parameter_id' => $parameterId,
            'order_position' => $order ?? $this->nomenclatures()->max('order_position') + 1,
            'separator' => $separator,
            'is_active' => $options['is_active'] ?? true,
            'prefix' => $options['prefix'] ?? null,
            'suffix' => $options['suffix'] ?? null,
            'formatting_rules' => $options['formatting_rules'] ?? null,
        ]);

        $nomenclature->save();
        return $nomenclature;
    }

    /**
     * Vérifier si le festival a une nomenclature configurée
     */
    public function hasNomenclature(): bool
    {
        return $this->nomenclatures()->where('is_active', true)->exists();
    }

    /**
     * Générer une nomenclature pour un film donné
     */
    public function generateMovieNomenclature(Movie $movie): string
    {
        return $movie->generateNomenclature($this->id);
    }

    /**
     * Désactiver le festival et nettoyer les assignations
     */
    public function deactivate(): self
    {
        $this->is_active = false;
        $this->accept_submissions = false;
        $this->save();

        // Supprimer toutes les assignations multi-festivals
        $this->users()->detach();

        return $this;
    }

    /**
     * Obtenir les statistiques du festival
     */
    public function getStats(): array
    {
        return [
            'total_movies' => $this->movies()->count(),
            'validated_movies' => $this->movies()->where('status', Movie::STATUS_VALIDATED)->count(),
            'pending_movies' => $this->movies()->whereIn('status', [
                Movie::STATUS_UPLOAD_OK,
                Movie::STATUS_IN_REVIEW,
                Movie::STATUS_TESTED
            ])->count(),
            'total_users' => $this->users()->count(),
            'days_until_deadline' => $this->submission_deadline ? now()->diffInDays($this->submission_deadline, false) : null,
        ];
    }
}
