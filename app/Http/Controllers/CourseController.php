<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('published', true)
            ->with(['sections.lessons'])
            ->paginate(12);

        return response()->json($courses);
    }

    public function show(Request $request, Course $course)
    {
        Gate::forUser($request->user())->authorize('view', $course);

        return response()->json(
            $course->load([
                'sections.lessons.videos' => fn ($query) => $query
                    ->select('id', 'lesson_id', 'title', 'duration_seconds', 'status'),
            ])
        );
    }

    public function enroll(Request $request, Course $course)
    {
        $user = $request->user();

        Gate::forUser($user)->authorize('view', $course);

        abort_unless($course->published, 404);

        $enrollment = Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ], [
            'status' => 'active',
            'price_paid' => $course->price,
        ]);

        return response()->json([
            'message' => 'Enrolled successfully',
            'enrollment' => $enrollment,
        ]);
    }

    public function myEnrollments(Request $request)
    {
        $enrollments = $request->user()
            ->enrollments()
            ->with('course.sections.lessons')
            ->get();

        return response()->json($enrollments);
    }

    public function store(Request $request)
    {
        Gate::forUser($request->user())->authorize('create', Course::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:courses',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced',
        ]);

        $course = Course::create([
            ...$validated,
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
            'published' => false,
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course,
        ], 201);
    }

    public function update(Request $request, Course $course)
    {
        Gate::forUser($request->user())->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'level' => 'sometimes|in:beginner,intermediate,advanced',
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course,
        ]);
    }
}
