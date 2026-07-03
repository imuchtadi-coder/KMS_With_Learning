<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    protected $table = 'progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'watched_seconds',
        'total_seconds',
        'percentage',
        'completed',
        'completed_at',
    ];

    protected $casts = [
        'watched_seconds' => 'integer',
        'total_seconds' => 'integer',
        'percentage' => 'decimal:2',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
