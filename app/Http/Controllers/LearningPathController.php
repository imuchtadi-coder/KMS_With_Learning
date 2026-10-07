<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LearningPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class LearningPathController extends Controller
{
    /**
     * List all learning paths (admin)
     */
    public function index()
    {
        $paths = LearningPath::where('tenant_id', auth()->user()->tenant_id)
            ->with('courses')
            ->paginate(10);

        return Inertia::render('Admin/LearningPaths/Index', compact('paths'));
    }

    /**
     * Show learning path details
     */
    public function show(LearningPath $learningPath)
    {
        $this->authorize('view', $learningPath);

        $learningPath->load('courses', 'enrollments.user');
        $availableCourses = Course::where('tenant_id', auth()->user()->tenant_id)
            ->whereNotIn('id', $learningPath->courses->pluck('id'))
            ->with('sections')
            ->get();

        return Inertia::render('Admin/LearningPaths/Show', ['learningPath' => $learningPath, 'availableCourses' => $availableCourses]);
    }

    /**
     * Create new learning path form
     */
    public function create()
    {
        $courses = Course::where('tenant_id', auth()->user()->tenant_id)->get();

        return Inertia::render('Admin/LearningPaths/Create', compact('courses'));
    }

    /**
     * Store new learning path
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $path = LearningPath::create([
            'tenant_id' => auth()->user()->tenant_id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.learning-paths.show', $path)
            ->with('success', 'Learning Path created successfully!');
    }

    /**
     * Edit learning path form
     */
    public function edit(LearningPath $learningPath)
    {
        $this->authorize('update', $learningPath);

        $learningPath->load('courses');

        return Inertia::render('Admin/LearningPaths/Edit', ['learningPath' => $learningPath]);
    }

    /**
     * Update learning path
     */
    public function update(Request $request, LearningPath $learningPath)
    {
        $this->authorize('update', $learningPath);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $learningPath->update($validated);

        return redirect()->route('admin.learning-paths.show', $learningPath)
            ->with('success', 'Learning Path updated successfully!');
    }

    /**
     * Delete learning path
     */
    public function destroy(LearningPath $learningPath)
    {
        $this->authorize('delete', $learningPath);

        $learningPath->delete();

        return redirect()->route('admin.learning-paths.index')
            ->with('success', 'Learning Path deleted successfully!');
    }

    /**
     * Add course to learning path
     */
    public function addCourse(Request $request, LearningPath $learningPath)
    {
        $this->authorize('update', $learningPath);

        $validated = $request->validate([
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where('tenant_id', $learningPath->tenant_id),
            ],
        ]);

        $course = Course::findOrFail($validated['course_id']);

        // Appending after the highest order avoids colliding with an existing
        // course when an earlier course has been removed.
        $nextOrder = ((int) $learningPath->courses()->max('learning_path_courses.order')) + 1;

        $learningPath->addCourse($course, $nextOrder);

        return back()->with('success', 'Course added to learning path!');
    }

    /**
     * Reorder course in learning path
     */
    public function reorderCourse(
        Request $request,
        LearningPath $learningPath,
        Course $course
    ) {
        $this->authorize('update', $learningPath);
        abort_unless($course->tenant_id === $learningPath->tenant_id, 404);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        DB::transaction(function () use ($learningPath, $course, $validated) {
            // Serialize reorder requests for this path so quick repeated clicks
            // cannot overwrite one another with stale positions.
            LearningPath::whereKey($learningPath->id)->lockForUpdate()->firstOrFail();

            $orderedCourses = DB::table('learning_path_courses')
                ->where('learning_path_id', $learningPath->id)
                ->orderBy('order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id', 'course_id']);

            $courseIds = $orderedCourses->pluck('course_id')->map(fn ($id) => (int) $id)->values()->all();
            $currentIndex = array_search((int) $course->id, $courseIds, true);

            if ($currentIndex === false) {
                abort(404, 'Course not found in this learning path.');
            }

            $targetIndex = $currentIndex + ($validated['direction'] === 'up' ? -1 : 1);

            if ($targetIndex < 0 || $targetIndex >= count($courseIds)) {
                return;
            }

            [$courseIds[$currentIndex], $courseIds[$targetIndex]] = [
                $courseIds[$targetIndex],
                $courseIds[$currentIndex],
            ];

            // Rewrite contiguous positions to repair any gaps or duplicate
            // order values left by older operations.
            foreach ($courseIds as $order => $courseId) {
                DB::table('learning_path_courses')
                    ->where('learning_path_id', $learningPath->id)
                    ->where('course_id', $courseId)
                    ->update(['order' => $order, 'updated_at' => now()]);
            }
        });

        return back()->with('success', 'Course reordered successfully!');
    }

    /**
     * Remove course from learning path
     */
    public function removeCourse(Request $request, LearningPath $learningPath, Course $course)
    {
        $this->authorize('update', $learningPath);
        abort_unless($course->tenant_id === $learningPath->tenant_id, 404);

        $learningPath->removeCourse($course);

        return back()->with('success', 'Course removed from learning path!');
    }

    /**
     * Publish learning path
     */
    public function publish(LearningPath $learningPath)
    {
        \Log::info('Publish method called for learning path: '.$learningPath->id.' by user: '.auth()->id());

        $this->authorize('update', $learningPath);

        if ($learningPath->courses()->count() === 0) {
            \Log::warning('Cannot publish path without courses: '.$learningPath->id);

            return back()->with('error', 'Cannot publish path without courses!');
        }

        \Log::info('Publishing learning path: '.$learningPath->id);
        $learningPath->publish();

        \Log::info('Learning path published successfully: '.$learningPath->id);

        return back()->with('success', 'Learning Path published!');
    }

    /**
     * Unpublish learning path
     */
    public function unpublish(LearningPath $learningPath)
    {
        $this->authorize('update', $learningPath);

        $learningPath->unpublish();

        return back()->with('success', 'Learning Path unpublished!');
    }
}
