<?php

namespace Lumieres\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Base Model for all Lumières applications
 * 
 * Provides common functionality across Fresnel and Meniscus
 */
abstract class BaseModel extends Model
{
    use HasFactory;

    /**
     * Default date format for all models
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Common fields that should be cast
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Common hidden fields
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Add any common model behaviors here
        static::creating(function ($model) {
            // Common creation logic
        });
    }

    /**
     * Get a formatted creation date
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at?->format('d/m/Y H:i') ?? '';
    }

    /**
     * Get a formatted update date
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at?->format('d/m/Y H:i') ?? '';
    }

    /**
     * Scope for recent records
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope for active records (if applicable)
     */
    public function scopeActive($query)
    {
        if (in_array('status', $this->fillable)) {
            return $query->where('status', 'active');
        }
        
        return $query;
    }
}
