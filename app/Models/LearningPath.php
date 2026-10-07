<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningPath extends Model
{
    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'slug',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Courses dalam learning path (ordered)
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'learning_path_courses', 'learning_path_id', 'course_id')
            ->withPivot('order', 'is_mandatory')
            ->orderBy('learning_path_courses.order')
            ->orderBy('learning_path_courses.id');
    }

    /**
     * User enrollments dalam path ini
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(LearningPathEnrollment::class);
    }

    /**
     * Progress tracking untuk path ini
     */
    public function progress(): HasMany
    {
        return $this->hasMany(LearningPathProgress::class);
    }

    /**
     * Get all users enrolled in this path
     */
    public function enrolledUsers()
    {
        return $this->hasManyThrough(
            User::class,
            LearningPathEnrollment::class,
            'learning_path_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * Check if learning path is published
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at <= now();
    }

    /**
     * Publish path
     */
    public function publish(): void
    {
        $this->update(['published_at' => now()]);
    }

    /**
     * Unpublish path
     */
    public function unpublish(): void
    {
        $this->update(['published_at' => null]);
    }

    /**
     * Add course to learning path
     */
    public function addCourse(Course $course, int $order = 0, bool $isMandatory = true): void
    {
        $this->courses()->attach($course->id, [
            'order' => $order,
            'is_mandatory' => $isMandatory,
            'tenant_id' => $this->tenant_id,
        ]);
    }

    /**
     * Remove course from learning path
     */
    public function removeCourse(Course $course): void
    {
        $this->courses()->detach($course->id);
    }

    /**
     * Get next unlocked course for user
     */
    public function getNextCourseForUser(User $user): ?Course
    {
        $completedCourseIds = $this->progress()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('course_id')
            ->toArray();

        return $this->courses()
            ->whereNotIn('course_id', $completedCourseIds)
            ->first();
    }

    /**
     * Get user's path progress percentage
     */
    public function getUserProgress(User $user): int
    {
        $totalCourses = $this->courses()->count();
        if ($totalCourses === 0) {
            return 0;
        }

        $completedCourses = $this->progress()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->distinct('course_id')
            ->count();

        return (int) (($completedCourses / $totalCourses) * 100);
    }

    /**
     * Check if user completed path
     */
    public function isCompletedByUser(User $user): bool
    {
        return $this->getUserProgress($user) === 100;
    }

    /**
     * Get certificate for user (if completed)
     */
    public function generateCertificateForUser(User $user): ?Certificate
    {
        if (!$this->isCompletedByUser($user)) {
            return null;
        }

        return Certificate::create([
            'user_id' => $user->id,
            'course_id' => null,
            'learning_path_id' => $this->id,
            'certificate_number' => $this->generateCertificateNumber(),
            'issued_at' => now(),
        ]);
    }

    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber(): string
    {
        return 'PATH-' . strtoupper(Str::random(12));
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }
}
