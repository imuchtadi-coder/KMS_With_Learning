<?php

namespace App\Http\Resources\Academy;

use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyCourseDetailResource extends JsonResource
{
    private ?int $userId = null;

    public function forUser(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function toArray($request): array
    {
        $totalLessons = $this->getTotalLessons();
        $totalMinutes = Lesson::whereIn('section_id', $this->sections()->pluck('id'))->sum('duration_minutes');

        return [
            'id' => $this->id,
            'courseTitle' => $this->title,
            'slug' => $this->slug,
            'desc' => $this->description,
            'about' => $this->description,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'tutorImg' => $this->thumbnail ?: $this->getInstructorAvatar(),
            'tags' => $this->resolveTagNames(),
            'rating' => (float) ($this->rating ?? 0),
            'ratingCount' => (int) ($this->reviews_count ?? $this->reviews?->count() ?? 0),
            'level' => $this->level,
            'skillLevel' => $this->resolveSkillLevel(),
            'price' => (float) $this->price,
            'learningOutcomes' => $this->learning_outcomes,
            'category' => $this->category?->name,
            'categoryId' => $this->category_id,
            'totalStudents' => (int) ($this->enrollments_count ?? $this->enrollments?->count() ?? 0),
            'totalLectures' => $totalLessons,
            'length' => $this->formatDuration($totalMinutes),
            'language' => $this->language ?? 'English',
            'isCaptions' => $this->is_captions ?? false,
            // Instructor
            'instructor' => $this->instructor?->name ?? 'Unknown Instructor',
            'instructorAvatar' => $this->getInstructorAvatar(),
            'instructorPosition' => $this->instructor?->department ?? 'Instructor',
            // Content curriculum
            'content' => $this->buildCurriculum(),
            // Status flags
            'published' => (bool) $this->published,
            'createdAt' => $this->created_at,
        ];
    }

    private function getInstructorAvatar(): string
    {
        if ($this->relationLoaded('instructor') && $this->instructor) {
            return $this->instructor->avatar_url;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->instructor?->name ?? 'User');
    }

    private function getTotalLessons(): int
    {
        if ($this->relationLoaded('sections')) {
            return $this->sections->sum(fn($s) => $s->lessons->count());
        }

        return Lesson::whereIn('section_id', $this->sections()->pluck('id'))->count();
    }

    private function buildCurriculum(): array
    {
        if (!$this->relationLoaded('sections')) {
            return [];
        }

        return $this->sections->map(function ($section, $index) {
            $totalTopics = $section->lessons->count();
            $completedTopics = 0;

            $topics = $section->lessons->map(function ($lesson) use (&$completedTopics) {
                $isCompleted = false;

                if ($this->userId && $lesson->relationLoaded('progresses')) {
                    $progress = $lesson->progresses
                        ->where('user_id', $this->userId)
                        ->first();
                    $isCompleted = $progress && $progress->completed;
                }

                if ($isCompleted) {
                    $completedTopics++;
                }

                return [
                    'title' => $lesson->title,
                    'time' => $lesson->duration_minutes
                        ? $lesson->duration_minutes . ' min'
                        : '0 min',
                    'isCompleted' => $isCompleted,
                    'lessonId' => $lesson->id,
                    'type' => $lesson->type ?? 'video',
                ];
            });

            return [
                'id' => 'section-' . $section->id,
                'title' => $section->title,
                'status' => "{$completedTopics}/{$totalTopics}",
                'time' => $this->formatDuration(
                    $section->lessons->sum('duration_minutes')
                ),
                'topics' => $topics,
            ];
        })->toArray();
    }

    private function resolveSkillLevel(): string
    {
        return match ($this->level) {
            'beginner' => 'All Level',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            default => 'All Level',
        };
    }

    private function resolveTagNames(): string
    {
        if ($this->relationLoaded('tags') && $this->tags->isNotEmpty()) {
            return $this->tags->pluck('name')->implode(', ');
        }

        return $this->category?->name ?? 'General';
    }

    private function formatDuration(int $totalMinutes): string
    {
        if ($totalMinutes === 0) {
            return '0 hours';
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}.{$minutes} total hours";
        }
        if ($hours > 0) {
            return "{$hours} total hours";
        }

        return "{$minutes} total minutes";
    }
}
