<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Grade;
use App\Models\GradebookItem;
use App\Notifications\AssignmentGraded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InstructorAssignmentController extends Controller
{
    public function index(Course $course)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $assignments = Assignment::where('course_id', $course->id)
            ->with(['section', 'creator'])
            ->withCount('submissions')
            ->latest()
            ->paginate(10);

        return Inertia::render('Instructor/Assignments/Index', compact('course', 'assignments'));
    }

    public function create(Course $course)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $course->load('sections');

        return Inertia::render('Instructor/Assignments/Create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'section_id' => ['required', Rule::exists('sections', 'id')->where('course_id', $course->id)],
            'file_types' => ['nullable', 'string'],
            'max_file_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'deadline' => ['nullable', 'date', 'after:now'],
            'passing_grade' => ['required', 'integer', 'min:0', 'max:100'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'section_id' => $validated['section_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'instructions' => $validated['instructions'],
            'file_types' => $validated['file_types'],
            'max_file_size' => $validated['max_file_size'],
            'deadline' => $validated['deadline'],
            'passing_grade' => $validated['passing_grade'],
            'max_score' => $validated['max_score'],
            'created_by' => $user->id,
        ]);

        GradebookItem::create([
            'course_id' => $course->id,
            'title' => $assignment->title,
            'type' => 'assignment',
            'item_id' => $assignment->id,
            'weight' => 1.00,
            'max_score' => $assignment->max_score,
        ]);

        return redirect()->route('instructor.assignments.index', $course)
            ->with('success', 'Assignment created successfully!');
    }

    public function show(Course $course, Assignment $assignment)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        $assignment->load(['section', 'submissions.user', 'submissions.grader']);

        return Inertia::render('Instructor/Assignments/Show', compact('course', 'assignment'));
    }

    public function edit(Course $course, Assignment $assignment)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        $course->load('sections');

        return Inertia::render('Instructor/Assignments/Edit', compact('course', 'assignment'));
    }

    public function update(Request $request, Course $course, Assignment $assignment)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'section_id' => ['required', Rule::exists('sections', 'id')->where('course_id', $course->id)],
            'file_types' => ['nullable', 'string'],
            'max_file_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'passing_grade' => ['required', 'integer', 'min:0', 'max:100'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $assignment->update($validated);

        GradebookItem::updateOrCreate(
            ['course_id' => $course->id, 'type' => 'assignment', 'item_id' => $assignment->id],
            ['title' => $assignment->title, 'weight' => 1.00, 'max_score' => $assignment->max_score]
        );

        return redirect()->route('instructor.assignments.index', $course)
            ->with('success', 'Assignment updated successfully!');
    }

    public function destroy(Course $course, Assignment $assignment)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        GradebookItem::where('course_id', $course->id)
            ->where('type', 'assignment')
            ->where('item_id', $assignment->id)
            ->delete();

        foreach ($assignment->submissions as $submission) {
            if ($submission->file_path) {
                Storage::disk('local')->delete($submission->file_path);
            }
        }

        $assignment->delete();

        return redirect()->route('instructor.assignments.index', $course)
            ->with('success', 'Assignment deleted successfully!');
    }

    public function downloadSubmission(Course $course, Assignment $assignment, AssignmentSubmission $submission)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }
        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        if (! $submission->file_path || ! Storage::disk('local')->exists($submission->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('local')->download($submission->file_path);
    }

    public function gradeForm(Course $course, Assignment $assignment, AssignmentSubmission $submission)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }
        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        $submission->load('user');

        return Inertia::render('Instructor/Assignments/Grade', compact('course', 'assignment', 'submission'));
    }

    public function grade(Request $request, Course $course, Assignment $assignment, AssignmentSubmission $submission)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }
        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        $validated = $request->validate([
            'grade' => ['required', 'integer', 'min:0', 'max:'.$assignment->max_score],
            'grader_notes' => ['nullable', 'string'],
        ]);

        $submission->update([
            'grade' => $validated['grade'],
            'grader_notes' => $validated['grader_notes'],
            'graded_at' => now(),
            'graded_by' => $user->id,
        ]);

        $percentage = $assignment->max_score > 0
            ? round(($validated['grade'] / $assignment->max_score) * 100, 2)
            : 0;

        Grade::updateOrCreate(
            ['user_id' => $submission->user_id, 'gradebook_item_id' => $this->getGradebookItem($assignment)->id],
            [
                'course_id' => $course->id,
                'score' => $validated['grade'],
                'percentage' => $percentage,
                'letter_grade' => $this->calculateLetterGrade($percentage),
            ]
        );

        $submission->user->notify(new AssignmentGraded($submission));

        return redirect()->route('instructor.assignments.show', [$course, $assignment])
            ->with('success', 'Submission graded successfully!');
    }

    private function getGradebookItem(Assignment $assignment)
    {
        return GradebookItem::firstOrCreate(
            ['course_id' => $assignment->course_id, 'type' => 'assignment', 'item_id' => $assignment->id],
            ['title' => $assignment->title, 'weight' => 1.00, 'max_score' => $assignment->max_score]
        );
    }

    private function calculateLetterGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'E',
        };
    }

    public function previewSubmission( Course $course, Assignment $assignment, AssignmentSubmission $submission ): BinaryFileResponse 
    {
        $user = auth()->user();

        if (
            ! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)
        ) {
            abort(403);
        }

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        if (
            ! $submission->file_path ||
            ! Storage::disk('local')->exists($submission->file_path)
        ) {
            abort(404, 'File not found.');
        }

        $path = Storage::disk('local')->path($submission->file_path);

        return new BinaryFileResponse(
            $path,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="submission.pdf"',
                'Content-Length' => filesize($path),
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
