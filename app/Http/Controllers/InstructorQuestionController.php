<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorQuestionController extends Controller
{
    public function index(Course $course)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $questions = Question::where('course_id', $course->id)->with('creator')->latest()->paginate(20);

        return Inertia::render('Instructor/Questions/Index', compact('course', 'questions'));
    }

    public function create(Course $course)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        return Inertia::render('Instructor/Questions/Create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $validated = $request->validate([
            'question_type' => ['required', 'in:multiple_choice,true_false,essay'],
            'question_text' => ['required', 'string'],
            'options' => ['nullable', 'array'], 'options.*' => ['required_if:question_type,multiple_choice', 'string'],
            'correct_answer' => ['required_unless:question_type,essay', 'string', 'nullable'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
        ]);

        if ($validated['question_type'] === 'multiple_choice') $validated['options'] = $request->input('options');

        Question::create([
            'tenant_id' => $user->tenant_id,
            'course_id' => $course->id,
            'created_by' => $user->id,
            'question_type' => $validated['question_type'],
            'question_text' => $validated['question_text'],
            'options' => $validated['question_type'] === 'multiple_choice' ? $validated['options'] : null,
            'correct_answer' => $validated['correct_answer'] ?? null,
            'points' => $validated['points'],
            'difficulty_level' => $validated['difficulty_level'],
        ]);

        return redirect()->route('instructor.questions.index', $course)->with('success', 'Question created!');
    }

    public function edit(Course $course, Question $question)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);
        if ($question->course_id !== $course->id) abort(404);

        return Inertia::render('Instructor/Questions/Edit', compact('course', 'question'));
    }

    public function update(Request $request, Course $course, Question $question)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);
        if ($question->course_id !== $course->id) abort(404);

        $validated = $request->validate([
            'question_type' => ['required', 'in:multiple_choice,true_false,essay'],
            'question_text' => ['required', 'string'],
            'options' => ['nullable', 'array'], 'options.*' => ['required_if:question_type,multiple_choice', 'string'],
            'correct_answer' => ['required_unless:question_type,essay', 'string', 'nullable'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
        ]);

        $question->update([
            'question_type' => $validated['question_type'],
            'question_text' => $validated['question_text'],
            'options' => $validated['question_type'] === 'multiple_choice' ? $request->input('options') : null,
            'correct_answer' => $validated['correct_answer'] ?? null,
            'points' => $validated['points'],
            'difficulty_level' => $validated['difficulty_level'],
        ]);

        return redirect()->route('instructor.questions.index', $course)->with('success', 'Question updated!');
    }

    public function destroy(Course $course, Question $question)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);
        if ($question->course_id !== $course->id) abort(404);

        $question->delete();

        return redirect()->route('instructor.questions.index', $course)->with('success', 'Question deleted!');
    }
}
