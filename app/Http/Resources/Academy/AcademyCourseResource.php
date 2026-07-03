<?php

namespace App\Http\Resources\Academy;

use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyCourseResource extends JsonResource
{
    private ?int $userId = null;

    public function forUser(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
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

    private function getCompletedLessons(): int
    {
        if (!$this->userId) {
            return 0;
        }

        $lessonIds = Lesson::whereIn('section_id', $this->sections()->pluck('id'))->pluck('id');

        return Progress::where('user_id', $this->userId)
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->count();
    }

    private function getProgress(): int
    {
        $total = $this->getTotalLessons();

        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->getCompletedLessons() / $total) * 100);
    }

    public function toArray($request): array
    {
        $totalLessons = $this->getTotalLessons();
        $completedLessons = $this->getCompletedLessons();

        // Pick first tag as the chip label
        $tagName = $this->relationLoaded('tags') && $this->tags->isNotEmpty()
            ? $this->tags->first()->name
            : ($this->category?->name ?? 'General');

        return [
            'id' => $this->id,
            'courseTitle' => $this->title,
            'slug' => $this->slug,
            'desc' => $this->description,
            'thumbnail' => $this->thumbnail,
            'tutorImg' => $this->thumbnail ?: $this->getInstructorAvatar(),
            'tags' => $tagName,
            'rating' => (float) ($this->rating ?? 0),
            'ratingCount' => (int) ($this->reviews_count ?? $this->reviews?->count() ?? 0),
            'time' => $this->formatDuration(),
            'completedTasks' => $completedLessons,
            'totalTasks' => $totalLessons,
            'progress' => $this->getProgress(),
            'level' => $this->level,
            'price' => (float) $this->price,
            'category' => $this->category?->name,
            'categoryId' => $this->category_id,
            'instructorName' => $this->instructor?->name ?? 'Unknown',
            'instructorAvatar' => $this->getInstructorAvatar(),
            'user' => $this->instructor?->name ?? 'Unknown',
            'image' => $this->getInstructorAvatar(),
            'enrollmentCount' => (int) ($this->enrollments_count ?? $this->enrollments?->count() ?? 0),
            'userCount' => (int) ($this->enrollments_count ?? $this->enrollments?->count() ?? 0),
            'note' => $totalLessons,
            'view' => $this->views_count ?? 0,
            'logo' => $this->resolveIcon(),
            'color' => $this->resolveColor(),
            'isEnrolled' => $this->enrolled ?? false,
            'published' => (bool) $this->published,
            'createdAt' => $this->created_at,
        ];
    }

    private function formatDuration(): string
    {
        $totalMinutes = Lesson::whereIn('section_id', $this->sections()->pluck('id'))->sum('duration_minutes');

        if ($totalMinutes === 0) {
            return '0h 0m';
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }

    private function resolveIcon(): string
    {
        $icons = [
            'Web' => 'tabler-code',
            'Design' => 'tabler-color-swatch',
            'Art' => 'tabler-edit',
            'UI/UX' => 'tabler-brand-figma',
            'Psychology' => 'tabler-brain',
        ];

        $tagName = $this->relationLoaded('tags') && $this->tags->isNotEmpty()
            ? $this->tags->first()->name
            : ($this->category?->name ?? '');

        return $icons[$tagName] ?? 'tabler-star';
    }

    private function resolveColor(): string
    {
        $colors = [
            'Web' => 'info',
            'Design' => 'warning',
            'Art' => 'success',
            'UI/UX' => 'error',
            'Psychology' => 'primary',
        ];

        $tagName = $this->relationLoaded('tags') && $this->tags->isNotEmpty()
            ? $this->tags->first()->name
            : ($this->category?->name ?? '');

        return $colors[$tagName] ?? 'primary';
    }
}
