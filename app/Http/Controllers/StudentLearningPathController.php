<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LearningPath;
use App\Models\LearningPathEnrollment;
use App\Models\LearningPathProgress;
use App\Services\LearningPathRecommendationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentLearningPathController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tenantId = auth()->user()->tenant_id;

        $publishedPaths = LearningPath::where('tenant_id', $tenantId)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('courses')
            ->paginate(10);

        $enrolledPathIds = LearningPathEnrollment::where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->pluck('learning_path_id');

        foreach ($publishedPaths as $path) {
            $path->isEnrolled = $enrolledPathIds->contains($path->id);
            $path->progress = $path->getUserProgress($user);
        }

        $recommendationService = new LearningPathRecommendationService;
        $recommendations = $recommendationService->getDashboardRecommendations($user, 3);

        return Inertia::render('Student/LearningPaths/Index', [
            'learningPaths' => $publishedPaths,
            'recommendations' => $recommendations,
        ]);
    }

    public function show(LearningPath $learningPath)
    {
        $user = auth()->user();
        if ($learningPath->tenant_id !== $user->tenant_id) {
            abort(404);
        }

        if (! $learningPath->isPublished()) {
            abort(404);
        }

        $learningPath->load('courses');

        $enrollment = LearningPathEnrollment::where('learning_path_id', $learningPath->id)
            ->where('user_id', $user->id)
            ->first();

        $isLocked = ! $enrollment;

        $progress = 0;
        $nextCourse = null;
        $completedCourseIds = [];

        if ($enrollment) {
            $progress = $learningPath->getUserProgress($user);
            $nextCourse = $learningPath->getNextCourseForUser($user);

            $completedCourseIds = LearningPathProgress::where('learning_path_id', $learningPath->id)
                ->where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->pluck('course_id')
                ->toArray();
        }

        return Inertia::render('Student/LearningPaths/Show', [
            'learningPath' => $learningPath,
            'enrollment' => $enrollment,
            'progress' => $progress,
            'nextCourse' => $nextCourse,
            'completedCourseIds' => $completedCourseIds,
            'isLocked' => $isLocked,
        ]);
    }

    public function enroll(Request $request, LearningPath $learningPath)
    {
        $user = auth()->user();
        $tenantId = auth()->user()->tenant_id;

        if ($learningPath->tenant_id !== $tenantId) {
            abort(404);
        }

        if (! $learningPath->isPublished()) {
            abort(404);
        }

        if (LearningPathEnrollment::where('learning_path_id', $learningPath->id)
            ->where('user_id', $user->id)
            ->exists()) {
            return back()->with('info', 'Already enrolled in this path!');
        }

        LearningPathEnrollment::create([
            'tenant_id' => $tenantId,
            'learning_path_id' => $learningPath->id,
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        foreach ($learningPath->courses as $course) {
            LearningPathProgress::create([
                'tenant_id' => $tenantId,
                'learning_path_id' => $learningPath->id,
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);
        }

        return redirect()->route('student.learning-paths.show', $learningPath)
            ->with('success', 'Enrolled in learning path! Start with the first course.');
    }

    public function completeCourse(Request $request, LearningPath $learningPath, Course $course)
    {
        $user = auth()->user();

        if ($learningPath->tenant_id !== $user->tenant_id || $course->tenant_id !== $user->tenant_id) {
            abort(404);
        }

        $enrollment = LearningPathEnrollment::where('learning_path_id', $learningPath->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! $learningPath->courses()->where('course_id', $course->id)->exists()) {
            abort(404);
        }

        $allCourses = $learningPath->courses()->pluck('course_id')->toArray();
        $courseIndex = array_search($course->id, $allCourses);

        $previousCourses = array_slice($allCourses, 0, $courseIndex);
        $completedPreviousCourses = LearningPathProgress::where('learning_path_id', $learningPath->id)
            ->where('user_id', $user->id)
            ->whereIn('course_id', $previousCourses)
            ->whereNotNull('completed_at')
            ->count();

        if (count($previousCourses) > 0 && $completedPreviousCourses !== count($previousCourses)) {
            return back()->with('error', 'Complete previous courses first!');
        }

        $progress = LearningPathProgress::where('learning_path_id', $learningPath->id)
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        $progress->markAsCompleted();

        return back()->with('success', 'Course completed!');
    }

    public function getNextCourse(LearningPath $learningPath)
    {
        $user = auth()->user();

        if ($learningPath->tenant_id !== $user->tenant_id) {
            abort(404);
        }

        $nextCourse = $learningPath->getNextCourseForUser($user);

        if (! $nextCourse) {
            return response()->json(['message' => 'No more courses!', 'completed' => true]);
        }

        return response()->json([
            'course' => $nextCourse,
            'completed' => false,
        ]);
    }

    public function myPaths()
    {
        $user = auth()->user();
        $tenantId = auth()->user()->tenant_id;

        $enrollments = LearningPathEnrollment::with([
            'learningPath.courses',
            'progress',
        ])
            ->where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->get()
            ->each(function ($enrollment) {
                $total = $enrollment->learningPath?->courses->count() ?: 1;
                $completed = $enrollment->progress->whereNotNull('completed_at')->count();
                $enrollment->setAttribute('progress', (int) round(($completed / $total) * 100));
                $enrollment->setAttribute('completed_count', $completed);
            });

        return Inertia::render('Student/LearningPaths/MyPaths', [
            'enrollments' => $enrollments,
        ]);
    }
}
