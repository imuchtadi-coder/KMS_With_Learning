<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'score',
        'total_points',
        'started_at',
        'finished_at',
        'status',
        'question_order',
        'tab_switch_count',
        'ip_address',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'total_points' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'tab_switch_count' => 'integer',
        'question_order' => 'array',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
