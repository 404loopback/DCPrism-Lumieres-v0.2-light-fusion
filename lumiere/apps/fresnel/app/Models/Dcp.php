<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Dcp extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'version_id',
        'is_ov',
        'backblaze_file_id',
        'uploaded_by',
        'is_valid',
        'validated_at',
        'uploaded_at',
        'audio_lang',
        'subtitle_lang',
        'file_path',
        'file_size',
        'technical_metadata',
        'validation_notes',
        'status'
    ];

    protected $casts = [
        'is_ov' => 'boolean',
        'is_valid' => 'boolean',
        'validated_at' => 'datetime',
        'uploaded_at' => 'datetime',
        'technical_metadata' => 'array',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_UPLOADED = 'uploaded';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_VALID = 'valid';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_ERROR = 'error';

    public const STATUSES = [
        self::STATUS_UPLOADED => 'Uploadé',
        self::STATUS_PROCESSING => 'En traitement',
        self::STATUS_VALID => 'Valide',
        self::STATUS_INVALID => 'Invalide',
        self::STATUS_ERROR => 'Erreur'
    ];

    /**
     * Film auquel appartient ce DCP
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Version linguistique de ce DCP
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }

    /**
     * Utilisateur qui a uploadé ce DCP
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Langue audio de ce DCP
     */
    public function audioLanguage(): BelongsTo
    {
        return $this->belongsTo(Lang::class, 'audio_lang', 'iso_639_1');
    }

    /**
     * Langue des sous-titres de ce DCP
     */
    public function subtitleLanguage(): BelongsTo
    {
        return $this->belongsTo(Lang::class, 'subtitle_lang', 'iso_639_1');
    }

    /**
     * Scope pour les DCPs valides
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * Scope pour les DCPs en cours de traitement
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope pour les versions originales
     */
    public function scopeOriginalVersion($query)
    {
        return $query->where('is_ov', true);
    }

    /**
     * Marque ce DCP comme valide
     */
    public function markAsValid(string $notes = null): void
    {
        $this->update([
            'is_valid' => true,
            'status' => self::STATUS_VALID,
            'validated_at' => now(),
            'validation_notes' => $notes
        ]);
    }

    /**
     * Marque ce DCP comme invalide
     */
    public function markAsInvalid(string $notes = null): void
    {
        $this->update([
            'is_valid' => false,
            'status' => self::STATUS_INVALID,
            'validation_notes' => $notes
        ]);
    }

    /**
     * Obtient la taille du fichier formatée
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Inconnu';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->file_size;
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Vérifie si le DCP est disponible (valide et non supprimé)
     */
    public function isAvailable(): bool
    {
        return $this->is_valid && $this->status === self::STATUS_VALID;
    }

    /**
     * Obtient l'URL de téléchargement du DCP
     */
    public function getDownloadUrl(): ?string
    {
        if ($this->backblaze_file_id) {
            // Logique pour générer l'URL Backblaze
            return config('backblaze.download_base_url') . '/' . $this->backblaze_file_id;
        }
        
        if ($this->file_path && Storage::exists($this->file_path)) {
            return Storage::url($this->file_path);
        }
        
        return null;
    }

    /**
     * Obtient les métadonnées techniques formattées
     */
    public function getTechnicalInfoAttribute(): array
    {
        $metadata = $this->technical_metadata ?? [];
        
        return [
            'resolution' => $metadata['resolution'] ?? 'Inconnue',
            'frame_rate' => $metadata['frame_rate'] ?? 'Inconnue',
            'duration' => $metadata['duration'] ?? 'Inconnue',
            'audio_channels' => $metadata['audio_channels'] ?? 'Inconnue',
            'compression' => $metadata['compression'] ?? 'Inconnue'
        ];
    }
}
