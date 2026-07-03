<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathProgress extends Model
{
    protected $table = 'learning_path_progress';

    protected $fillable = [
        'tenant_id',
        'learning_path_id',
        'user_id',
        'course_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Learning path
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'learning_path_id');
    }

    /**
     * Course yang di-complete
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * User yang complete
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if course completed
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Mark course as completed in path
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
        ]);

        // Check if all mandatory courses completed, if so mark path as completed
        $path = $this->path;
        $mandatoryCourses = $path->courses()
            ->where('is_mandatory', true)
            ->pluck('course_id')
            ->toArray();

        $completedMandatory = $path->progress()
            ->where('user_id', $this->user_id)
            ->whereIn('course_id', $mandatoryCourses)
            ->whereNotNull('completed_at')
            ->count();

        if (count($mandatoryCourses) > 0 && $completedMandatory === count($mandatoryCourses)) {
            // All mandatory courses completed
            $enrollment = $path->enrollments()
                ->where('user_id', $this->user_id)
                ->first();

            if ($enrollment && !$enrollment->isCompleted()) {
                $enrollment->markAsCompleted();
            }
        }
    }
}
