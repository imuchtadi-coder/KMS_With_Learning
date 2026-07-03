<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LiveClass;
use App\Http\Requests\StoreLiveClassRequest;
use App\Http\Requests\UpdateLiveClassRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorLiveClassController extends Controller
{
    public function index(Course $course)
    {
        $this->authorize('update', $course);

        $liveClasses = LiveClass::where('course_id', $course->id)
            ->with('creator')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return Inertia::render('Instructor/LiveClasses/Index', compact('course', 'liveClasses'));
    }

    public function create(Course $course)
    {
        $this->authorize('update', $course);

        $course->load('sections');

        return Inertia::render('Instructor/LiveClasses/Create', compact('course'));
    }

    public function store(StoreLiveClassRequest $request, Course $course)
    {
        $this->authorize('update', $course);

        $user = auth()->user();
        $validated = $request->validated();

        $liveClass = LiveClass::create([
            'tenant_id' => $user->tenant_id,
            'course_id' => $course->id,
            'section_id' => $validated['section_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'provider' => $validated['provider'],
            'meeting_url' => $validated['meeting_url'],
            'meeting_id' => $validated['meeting_id'],
            'password' => $validated['password'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'recording_url' => $validated['recording_url'],
            'recurring' => $validated['recurring'] ?? false,
            'status' => 'scheduled',
            'created_by' => $user->id,
        ]);

        return redirect()->route('instructor.live-classes.index', $course)
            ->with('success', 'Live class created successfully!');
    }

    public function edit(Course $course, LiveClass $liveClass)
    {
        $this->authorize('update', $course);

        if ($liveClass->course_id !== $course->id) abort(404);

        $course->load('sections');

        return Inertia::render('Instructor/LiveClasses/Edit', compact('course', 'liveClass'));
    }

    public function update(UpdateLiveClassRequest $request, Course $course, LiveClass $liveClass)
    {
        $this->authorize('update', $course);

        if ($liveClass->course_id !== $course->id) abort(404);

        $validated = $request->validated();

        $liveClass->update($validated);

        return redirect()->route('instructor.live-classes.index', $course)
            ->with('success', 'Live class updated successfully!');
    }

    public function destroy(Course $course, LiveClass $liveClass)
    {
        $this->authorize('update', $course);

        if ($liveClass->course_id !== $course->id) abort(404);

        $liveClass->delete();

        return redirect()->route('instructor.live-classes.index', $course)
            ->with('success', 'Live class deleted successfully!');
    }

    public function startMeeting(Course $course, LiveClass $liveClass)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($liveClass->course_id !== $course->id) abort(404);

        $liveClass->update(['status' => 'ongoing']);

        return redirect()->route('instructor.live-classes.index', $course)
            ->with('success', 'Meeting started!');
    }

    public function endMeeting(Course $course, LiveClass $liveClass)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($liveClass->course_id !== $course->id) abort(404);

        $liveClass->update(['status' => 'ended']);

        return redirect()->route('instructor.live-classes.index', $course)
            ->with('success', 'Meeting ended!');
    }

    public function join(LiveClass $liveClass)
    {
        $liveClass->load('course');

        return view('student.live-classes.join', compact('liveClass'));
    }
}
