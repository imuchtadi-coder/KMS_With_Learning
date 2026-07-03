<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Traits\HasTenantStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentAssignmentController extends Controller
{
    use HasTenantStorage;

    public function show(Assignment $assignment)
    {
        $user = auth()->user();

        $enrolled = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$enrolled) abort(403, 'You are not enrolled in this course.');

        $assignment->load(['section', 'course']);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        return Inertia::render('Student/Assignments/Show', [
            'assignment' => $assignment,
            'submission' => $submission,
        ]);
    }

    public function submitForm(Assignment $assignment)
    {
        $user = auth()->user();

        $enrolled = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$enrolled) abort(403);

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return redirect()->route('student.assignments.show', $assignment)
                ->with('info', 'You have already submitted this assignment.');
        }

        if ($assignment->deadline && now()->isAfter($assignment->deadline)) {
            return redirect()->route('student.assignments.show', $assignment)
                ->with('error', 'The deadline for this assignment has passed.');
        }

        $assignment->load('course');

        return Inertia::render('Student/Assignments/Submit', [
            'assignment' => $assignment,
        ]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $user = auth()->user();

        $enrolled = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$enrolled) abort(403);

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return redirect()->route('student.assignments.show', $assignment)
                ->with('error', 'You have already submitted this assignment.');
        }

        if ($assignment->deadline && now()->isAfter($assignment->deadline)) {
            return redirect()->route('student.assignments.show', $assignment)
                ->with('error', 'The deadline for this assignment has passed.');
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:' . ($assignment->max_file_size ? $assignment->max_file_size * 1024 : 10240)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $path = $request->file('file')->store(
            $this->tenantPath('assignments/' . $assignment->id, $assignment->course->tenant_id)
        );

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'notes' => $validated['notes'],
            'submitted_at' => now(),
        ]);

        return redirect()->route('student.assignments.show', $assignment)
            ->with('success', 'Assignment submitted successfully!');
    }
}
