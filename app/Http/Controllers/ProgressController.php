<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'watched_seconds' => 'required|integer|min:0',
            'total_seconds' => 'required|integer|min:0',
        ]);

        $user = $request->user();
        $lesson = Lesson::findOrFail($validated['lesson_id']);

        $progress = Progress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $validated['lesson_id'],
            ],
            [
                'watched_seconds' => $validated['watched_seconds'],
                'total_seconds' => $validated['total_seconds'],
                'percentage' => ($validated['watched_seconds'] / max($validated['total_seconds'], 1)) * 100,
                'completed' => ($validated['watched_seconds'] / max($validated['total_seconds'], 1)) >= 0.8, // 80% = complete
                'completed_at' => ($validated['watched_seconds'] / max($validated['total_seconds'], 1)) >= 0.8
                    ? now()
                    : null,
            ]
        );

        return response()->json([
            'message' => 'Progress updated',
            'progress' => $progress,
        ]);
    }

    public function getCourseProgress(Request $request, $courseId)
    {
        $user = $request->user();

        $progress = Progress::whereIn('lesson_id', function($query) use ($courseId) {
            $query->select('id')
                ->from('lessons')
                ->whereIn('section_id', function($subquery) use ($courseId) {
                    $subquery->select('id')
                        ->from('sections')
                        ->where('course_id', $courseId);
                });
        })
        ->where('user_id', $user->id)
        ->get();

        $totalLessons = Lesson::whereIn('section_id', function($query) use ($courseId) {
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
}
