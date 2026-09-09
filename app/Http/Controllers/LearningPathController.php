<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LearningPath;
use Illuminate\Http\Request;
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

        return redirect()->route('admin.learning-paths.edit', $path)
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

        // Get next order
        $nextOrder = $learningPath->courses()->count();

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

        $direction = $request->input('direction', 'down');

        // Check whether the course exists in this learning path
        $courseInPath = $learningPath->courses()
            ->where('courses.id', $course->id)
            ->first();

        if (! $courseInPath) {
            return back()->with('error', 'Course not found in this path!');
        }

        // Get current order from pivot table
        $currentOrder = $courseInPath->pivot->order;

        // Get maximum order from pivot table
        $maxOrder = $learningPath->courses()
            ->max('learning_path_courses.order');

        if ($direction === 'up' && $currentOrder > 0) {

            // Find course immediately above
            $swapCourse = $learningPath->courses()
                ->wherePivot('order', $currentOrder - 1)
                ->first();

            if ($swapCourse) {
                // Swap orders
                $learningPath->courses()->updateExistingPivot(
                    $swapCourse->id,
                    ['order' => $currentOrder]
                );

                $learningPath->courses()->updateExistingPivot(
                    $course->id,
                    ['order' => $currentOrder - 1]
                );
            }

        } elseif ($direction === 'down' && $currentOrder < $maxOrder) {

            // Find course immediately below
            $swapCourse = $learningPath->courses()
                ->wherePivot('order', $currentOrder + 1)
                ->first();

            if ($swapCourse) {
                // Swap orders
                $learningPath->courses()->updateExistingPivot(
                    $swapCourse->id,
                    ['order' => $currentOrder]
                );

                $learningPath->courses()->updateExistingPivot(
                    $course->id,
                    ['order' => $currentOrder + 1]
                );
            }
        }

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
