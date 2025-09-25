<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidationResult extends Model
{
    protected $table = 'validation_results';

    protected $fillable = [
        'movie_id',
        'parameter_id',
        'validation_type',
        'status',
        'severity',
        'rule_name',
        'description',
        'expected_value',
        'actual_value',
        'details',
        'suggestion',
        'can_auto_fix',
        'validated_at',
        'validator_version',
    ];

    protected $casts = [
        'details' => 'array',
        'can_auto_fix' => 'boolean',
        'validated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }
}
