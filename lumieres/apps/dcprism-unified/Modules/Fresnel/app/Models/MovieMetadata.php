<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieMetadata extends Model
{
    protected $table = 'movie_metadata';

    protected $fillable = [
        'movie_id',
        'metadata_key',
        'metadata_value',
        'data_type',
        'source',
        'is_verified',
        'is_critical',
        'validation_rules',
        'notes',
        'extracted_at',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_verified' => 'boolean',
        'is_critical' => 'boolean',
        'extracted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
