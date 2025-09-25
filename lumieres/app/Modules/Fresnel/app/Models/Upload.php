<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'festival_id',
        'original_filename',
        'file_path',
        'bucket_name',
        'file_size',
        'file_type',
        'mime_type',
        'status',
        'metadata',
        'upload_id',
        'b2_file_id',
        'b2_file_name',
        'storage_path',
        'total_parts',
        'completed_parts',
        'part_sha1_array',
        'progress_percentage',
        'uploaded_bytes',
        'upload_speed_mbps',
        'started_at',
        'completed_at',
        'expires_at',
        'error_message',
        'user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'part_sha1_array' => 'array',
        'file_size' => 'integer',
        'total_parts' => 'integer',
        'completed_parts' => 'integer',
        'uploaded_bytes' => 'integer',
        'progress_percentage' => 'decimal:2',
        'upload_speed_mbps' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the movie that owns the upload.
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Get the user that owns the upload.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the festival that owns the upload.
     */
    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    /**
     * Check if upload is resumable
     */
    public function isResumable(): bool
    {
        return $this->status === 'uploading'
            && $this->upload_id
            && $this->expires_at
            && $this->expires_at->isFuture();
    }

    /**
     * Check if upload is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && $this->completed_at;
    }

    /**
     * Get upload progress percentage
     */
    public function getProgressAttribute(): float
    {
        if ($this->total_parts && $this->completed_parts) {
            return ($this->completed_parts / $this->total_parts) * 100;
        }

        return $this->progress_percentage;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Get estimated time remaining
     */
    public function getEstimatedTimeRemainingAttribute(): ?string
    {
        if (! $this->upload_speed_mbps || $this->status !== 'uploading') {
            return null;
        }

        $remainingBytes = $this->file_size - $this->uploaded_bytes;
        $remainingMB = $remainingBytes / (1024 * 1024);
        $secondsRemaining = $remainingMB / $this->upload_speed_mbps;

        if ($secondsRemaining < 60) {
            return round($secondsRemaining).'s';
        } elseif ($secondsRemaining < 3600) {
            return round($secondsRemaining / 60).'min';
        } else {
            return round($secondsRemaining / 3600, 1).'h';
        }
    }

    /**
     * Scope for active uploads
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'uploading']);
    }

    /**
     * Scope for resumable uploads
     */
    public function scopeResumable($query)
    {
        return $query->where('status', 'uploading')
            ->whereNotNull('upload_id')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for expired uploads
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
}
