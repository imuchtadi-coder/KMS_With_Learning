<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPathEnrollment extends Model
{
    protected $fillable = [
        'tenant_id',
        'learning_path_id',
        'user_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Path yang diikuti
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'learning_path_id');
    }

    /**
     * Alias untuk path relationship
     */
    public function learningPath(): BelongsTo
    {
        return $this->path();
    }

    /**
     * User yang enroll
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Course progress dalam path ini
     */
    public function progress(): HasMany
    {
        return $this->hasMany(LearningPathProgress::class, 'learning_path_id', 'learning_path_id')
            ->where('user_id', $this->user_id);
    }

    /**
     * Check if path completed
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Get progress percentage
     */
    public function getProgress(): int
    {
        return $this->path->getUserProgress($this->user);
    }

    /**
     * Mark path as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
        ]);
    }
}
