<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [
        'title',
        'description', 
        'status',
        'progress',
        'source_file_path',
        'source_file_size',
        'output_path',
        'format',
        'worker_id',
        'started_at',
        'completed_at',
        'estimated_completion',
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime', 
        'estimated_completion' => 'datetime',
    ];
}
