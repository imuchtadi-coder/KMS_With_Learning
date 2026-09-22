<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Discussion;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiscussionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $query = Discussion::with(['creator', 'course', 'latestReply.creator'])
            ->whereHas('course', fn ($query) => $query->where('tenant_id', $tenantId))
            ->withCount('replies');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $discussions = $query->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(20);

        $course = null;
        if ($request->filled('course_id')) {
            $course = Course::where('tenant_id', $tenantId)->find($request->course_id);
        }

        return Inertia::render('Discussions/Index', [
            'discussions' => $discussions,
            'course' => $course,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Discussion::class);

        $courses = $this->accessibleCourses($request)
            ->with([
                'sections.lessons',
            ])
            ->orderBy('title')
            ->get();

        $course = null;

        if ($request->filled('course_id')) {
            $course = $courses->firstWhere(
                'id',
                (int) $request->course_id
            );

            abort_unless($course, 404);
        }

        $lesson = null;

        if ($request->filled('lesson_id')) {
            abort_unless($course, 404);

            $lesson = Lesson::whereHas('section', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->findOrFail($request->lesson_id);
        }

        return Inertia::render('Discussions/Create', [
            'courses' => $courses,
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Discussion::class);

        $validated = $request->validate([
            'course_id' => ['required', 'integer'],
            'lesson_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $course = $this->accessibleCourses($request)
            ->findOrFail($validated['course_id']);

        $lessonId = $validated['lesson_id'] ?? null;

        if ($lessonId !== null) {
            Lesson::whereHas('section', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->findOrFail($lessonId);
        }

        $discussion = Discussion::create([
            'course_id' => $course->id,
            'lesson_id' => $lessonId,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('discussions.show', $discussion)
            ->with('success', 'Discussion created successfully.');
    }

    public function show(Discussion $discussion)
    {
        $this->authorize('view', $discussion);
        $discussion->load(['creator', 'course', 'replies.creator']);
        $course = $discussion->course;

        return Inertia::render('Discussions/Show', [
            'discussion' => $discussion,
            'course' => $course,
        ]);
    }

    public function edit(Discussion $discussion)
    {
        $this->authorize('update', $discussion);
        $course = $discussion->course;

        return Inertia::render('Discussions/Edit', [
            'discussion' => $discussion,
            'course' => $course,
        ]);
    }

    public function update(Request $request, Discussion $discussion)
    {
        $this->authorize('update', $discussion);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $discussion->update($validated);

        return redirect()->route('discussions.show', $discussion)
            ->with('success', 'Discussion updated.');
    }

    public function destroy(Discussion $discussion)
    {
        $this->authorize('delete', $discussion);

        $courseId = $discussion->course_id;
        $discussion->delete();

        return redirect()->route('discussions.index', ['course_id' => $courseId])
            ->with('success', 'Discussion deleted.');
    }

    public function pin(Discussion $discussion)
    {
        $this->authorize('pin', $discussion);

        $discussion->update(['is_pinned' => ! $discussion->is_pinned]);

        return redirect()->back()->with('success', $discussion->is_pinned ? 'Discussion pinned.' : 'Discussion unpinned.');
    }

    public function lessonDiscussions(Lesson $lesson)
    {
        $course = $lesson->section->course;
        abort_unless($course->tenant_id === auth()->user()->tenant_id, 404);

        $discussions = Discussion::where('course_id', $course->id)
            ->where('lesson_id', $lesson->id)
            ->with(['creator', 'replies', 'latestReply.creator'])
            ->withCount('replies')
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(20);

        return Inertia::render('Discussions/Index', [
            'course' => $course,
            'discussions' => $discussions,
            'lesson' => $lesson,
        ]);
    }

    private function accessibleCourses(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('instructor')) {
            return Course::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('created_by', $user->id);
        }

        if ($user->hasRole('student')) {
            return Course::query()
                ->where('tenant_id', $user->tenant_id)
                ->whereHas('enrollments', function ($query) use ($user) {
                    $query
                        ->where('user_id', $user->id)
                        ->where('status', 'active');
                });
        }

        abort(403);
    }
}
