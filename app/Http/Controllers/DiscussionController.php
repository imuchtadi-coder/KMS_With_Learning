<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
        $user = $request->user();

        $query = Discussion::with(['creator', 'course', 'category', 'latestReply.creator', 'acceptedReply.creator'])
            ->withCount('replies');

        if (!$user->hasRole('superadmin')) {
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereHas('course', fn ($c) => $c->where('tenant_id', $tenantId));
            });
        }

        // Scope by type (all, general, course)
        if ($request->input('type') === 'general') {
            $query->whereNull('course_id');
        } elseif ($request->input('type') === 'course') {
            $query->whereNotNull('course_id');
        }

        // Course filter
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Resolution status filter
        if ($request->input('status') === 'resolved') {
            $query->where('is_resolved', true);
        } elseif ($request->input('status') === 'unresolved') {
            $query->where('is_resolved', false);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(content) LIKE ?', ["%{$search}%"]);
            });
        }

        $discussions = $query->orderBy('is_pinned', 'desc')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $course = null;
        if ($request->filled('course_id')) {
            $course = Course::query();
            if (!$user->hasRole('superadmin')) {
                $course->where('tenant_id', $tenantId);
            }
            $course = $course->find($request->course_id);
        }

        $baseCountQuery = Discussion::query();
        if (!$user->hasRole('superadmin')) {
            $baseCountQuery->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereHas('course', fn ($c) => $c->where('tenant_id', $tenantId));
            });
        }
        if ($request->filled('course_id')) {
            $baseCountQuery->where('course_id', $request->course_id);
        }

        $stats = [
            'total' => (clone $baseCountQuery)->count(),
            'resolved' => (clone $baseCountQuery)->where('is_resolved', true)->count(),
            'unresolved' => (clone $baseCountQuery)->where('is_resolved', false)->count(),
        ];

        $categories = Category::query();
        if (!$user->hasRole('superadmin')) {
            $categories->where('tenant_id', $tenantId);
        }
        $categories = $categories->orderBy('name')->get();

        return Inertia::render('Discussions/Index', [
            'discussions' => $discussions,
            'course' => $course,
            'categories' => $categories,
            'filters' => $request->only(['type', 'status', 'category_id', 'course_id', 'search']),
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Discussion::class);

        $tenantId = $request->user()->tenant_id;
        $user = $request->user();

        $courses = $this->accessibleCourses($request)
            ->with(['sections.lessons'])
            ->orderBy('title')
            ->get();

        $categories = Category::query();
        if (!$user->hasRole('superadmin')) {
            $categories->where('tenant_id', $tenantId);
        }
        $categories = $categories->orderBy('name')->get();

        $course = null;
        if ($request->filled('course_id')) {
            $course = $courses->firstWhere('id', (int) $request->course_id);
        }

        $lesson = null;
        if ($request->filled('lesson_id') && $course) {
            $lesson = Lesson::whereHas('section', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->find($request->lesson_id);
        }

        return Inertia::render('Discussions/Create', [
            'courses' => $courses,
            'course' => $course,
            'lesson' => $lesson,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Discussion::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_general' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'course_id' => ['nullable', 'integer'],
            'lesson_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $isGeneral = !empty($validated['is_general']) || empty($validated['course_id']);

        $courseId = null;
        $lessonId = null;
        $tenantId = $user->tenant_id;

        if (!$isGeneral && !empty($validated['course_id'])) {
            $course = $this->accessibleCourses($request)->findOrFail($validated['course_id']);
            $courseId = $course->id;
            $tenantId = $course->tenant_id;

            if (!empty($validated['lesson_id'])) {
                $lesson = Lesson::whereHas('section', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })->findOrFail($validated['lesson_id']);
                $lessonId = $lesson->id;
            }
        }

        $discussion = Discussion::create([
            'tenant_id' => $tenantId,
            'course_id' => $courseId,
            'lesson_id' => $lessonId,
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('discussions.show', $discussion)
            ->with('success', 'Diskusi / Pertanyaan berhasil dibuat.');
    }

    public function show(Discussion $discussion)
    {
        $this->authorize('view', $discussion);

        $discussion->load([
            'creator',
            'course',
            'lesson',
            'category',
            'replies' => fn($q) => $q->orderBy('is_solution', 'desc')->oldest(),
            'replies.creator',
            'replies.verifiedBy',
            'acceptedReply.creator',
        ]);

        $user = auth()->user();
        $canVerifySolution = $user->can('verifySolution', $discussion);
        $canPin = $user->can('pin', $discussion);
        $canEdit = $user->can('update', $discussion);
        $canDelete = $user->can('delete', $discussion);

        return Inertia::render('Discussions/Show', [
            'discussion' => $discussion,
            'course' => $discussion->course,
            'canVerifySolution' => $canVerifySolution,
            'canPin' => $canPin,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
        ]);
    }

    public function edit(Discussion $discussion)
    {
        $this->authorize('update', $discussion);

        $tenantId = auth()->user()->tenant_id;
        $categories = Category::where('tenant_id', $tenantId)->orderBy('name')->get();

        return Inertia::render('Discussions/Edit', [
            'discussion' => $discussion,
            'course' => $discussion->course,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Discussion $discussion)
    {
        $this->authorize('update', $discussion);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        $discussion->update($validated);

        return redirect()->route('discussions.show', $discussion)
            ->with('success', 'Diskusi berhasil diperbarui.');
    }

    public function destroy(Discussion $discussion)
    {
        $this->authorize('delete', $discussion);

        $courseId = $discussion->course_id;
        $discussion->delete();

        return redirect()->route('discussions.index', $courseId ? ['course_id' => $courseId] : [])
            ->with('success', 'Diskusi berhasil dihapus.');
    }

    public function pin(Discussion $discussion)
    {
        $this->authorize('pin', $discussion);

        $discussion->update(['is_pinned' => ! $discussion->is_pinned]);

        return redirect()->back()->with('success', $discussion->is_pinned ? 'Diskusi disematkan.' : 'Sematkan diskusi dicabut.');
    }

    public function lessonDiscussions(Lesson $lesson)
    {
        $course = $lesson->section->course;
        abort_unless($course->tenant_id === auth()->user()->tenant_id, 404);

        $discussions = Discussion::where('course_id', $course->id)
            ->where('lesson_id', $lesson->id)
            ->with(['creator', 'replies', 'latestReply.creator', 'category'])
            ->withCount('replies')
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(20);

        return Inertia::render('Discussions/Index', [
            'course' => $course,
            'discussions' => $discussions,
            'lesson' => $lesson,
            'categories' => Category::where('tenant_id', $course->tenant_id)->get(),
        ]);
    }

    private function accessibleCourses(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole(['superadmin', 'tenant_admin'])) {
            $query = Course::query();
            if (!$user->hasRole('superadmin')) {
                $query->where('tenant_id', $user->tenant_id);
            }
            return $query;
        }

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

        return Course::query()->whereRaw('1 = 0');
    }
}
