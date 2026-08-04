<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ProgressController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'video_id' => ['nullable', 'integer', 'exists:videos,id'],
            'watched_seconds' => ['required', 'integer', 'min:0', 'max:604800'],
            'total_seconds' => ['required', 'integer', 'min:1', 'max:604800'],
        ]);

        $user = $request->user();
        $lesson = Lesson::with(['section.course', 'videos'])->findOrFail($validated['lesson_id']);
        $course = $lesson->section->course;

        Gate::forUser($user)->authorize('view', $course);
        $this->ensureActiveEnrollment($request, $course);

        $videoId = $validated['video_id'] ?? null;
        $video = $videoId
            ? $lesson->videos->firstWhere('id', $videoId)
            : $lesson->videos->firstWhere('status', 'ready');

        if (! $video) {
            throw ValidationException::withMessages([
                'video_id' => 'The selected video does not belong to this lesson or is unavailable.',
            ]);
        }

        $progress = DB::transaction(function () use ($lesson, $user, $validated, $video): Progress {
            $lockedVideo = Video::query()->lockForUpdate()->findOrFail($video->id);
            $reportedDuration = (int) $validated['total_seconds'];

            if ($lockedVideo->isYoutube() && ! $lockedVideo->duration_seconds) {
                $lockedVideo->update(['duration_seconds' => $reportedDuration]);
            }

            $serverDuration = (int) $lockedVideo->duration_seconds;
            if ($serverDuration <= 0) {
                $serverDuration = (int) $lesson->duration_minutes * 60;
            }
            if ($serverDuration <= 0) {
                $serverDuration = $reportedDuration;
            }

            $progress = Progress::query()
                ->where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->lockForUpdate()
                ->first() ?? new Progress([
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                ]);

            $watchedSeconds = min(
                $serverDuration,
                max((int) $progress->watched_seconds, (int) $validated['watched_seconds'])
            );
            $percentage = min(100, ($watchedSeconds / $serverDuration) * 100);
            $completed = (bool) $progress->completed || $percentage >= 80;

            $progress->fill([
                'watched_seconds' => $watchedSeconds,
                'total_seconds' => $serverDuration,
                'percentage' => $percentage,
                'completed' => $completed,
                'completed_at' => $completed ? ($progress->completed_at ?? now()) : null,
            ]);
            $progress->save();

            return $progress;
        });

        return response()->json([
            'message' => 'Progress updated',
            'progress' => $progress,
        ]);
    }

    public function getCourseProgress(Request $request, $courseId)
    {
        $user = $request->user();
        $course = Course::findOrFail($courseId);

        Gate::forUser($user)->authorize('view', $course);
        $this->ensureCanViewProgress($request, $course);

        $progress = Progress::whereIn('lesson_id', function ($query) use ($courseId) {
            $query->select('id')
                ->from('lessons')
                ->whereIn('section_id', function ($subquery) use ($courseId) {
                    $subquery->select('id')
                        ->from('sections')
                        ->where('course_id', $courseId);
                });
        })
            ->where('user_id', $user->id)
            ->get();

        $totalLessons = Lesson::whereIn('section_id', function ($query) use ($courseId) {
            $query->select('id')
                ->from('sections')
                ->where('course_id', $courseId);
        })->count();

        $completedLessons = $progress->where('completed', true)->count();
        $overallPercentage = ($completedLessons / max($totalLessons, 1)) * 100;

        return response()->json([
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'overall_percentage' => $overallPercentage,
            'progress' => $progress,
        ]);
    }

    private function ensureActiveEnrollment(Request $request, Course $course): void
    {
        abort_unless(
            $course->enrollments()
                ->where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->exists(),
            403
        );
    }

    private function ensureCanViewProgress(Request $request, Course $course): void
    {
        $user = $request->user();

        if ($user->id === $course->created_by || $user->hasRole('tenant_admin')) {
            return;
        }

        $this->ensureActiveEnrollment($request, $course);
    }
}
