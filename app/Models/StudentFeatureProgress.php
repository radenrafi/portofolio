<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeatureProgress extends Model
{
    use HasFactory;

    protected $table = 'student_feature_progress';

    protected $fillable = [
        'user_id',
        'feature',
        'started_at',
        'last_accessed_at',
        'access_count',
        'percent',
        'state',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'percent' => 'integer',
        'access_count' => 'integer',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

