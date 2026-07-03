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
        $query = Discussion::with(['creator', 'course', 'latestReply.creator'])
            ->withCount('replies');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $discussions = $query->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(20);

        $course = null;
        if ($request->filled('course_id')) {
            $course = Course::find($request->course_id);
        }

        return Inertia::render('Discussions/Index', [
            'discussions' => $discussions,
            'course' => $course,
        ]);
    }

    public function create(Request $request)
    {
        $course = null;
        if ($request->filled('course_id')) {
            $course = Course::findOrFail($request->course_id);
        }

        $lesson = null;
        if ($request->filled('lesson_id')) {
            $lesson = Lesson::find($request->lesson_id);
        }

        return Inertia::render('Discussions/Create', [
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
        ]);

        $discussion = Discussion::create([
            'course_id' => $validated['course_id'],
            'lesson_id' => $validated['lesson_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('discussions.show', $discussion)
            ->with('success', 'Discussion created successfully.');
    }

    public function show(Discussion $discussion)
    {
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
        $this->authorize('pin', Discussion::class);

        $discussion->update(['is_pinned' => !$discussion->is_pinned]);

        return redirect()->back()->with('success', $discussion->is_pinned ? 'Discussion pinned.' : 'Discussion unpinned.');
    }

    public function lessonDiscussions(Lesson $lesson)
    {
        $course = $lesson->section->course;
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
}
