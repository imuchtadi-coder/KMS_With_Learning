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

        // Authorization
        if (
            !(
                $user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id
            ) &&
            !$user->hasRole('tenant_admin')
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => [
                'required',
                'in:multiple_choice,true_false,essay',
            ],

            'question_text' => [
                'required',
                'string',
            ],

            'points' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'options' => [
                'nullable',
                'array',
            ],

            'options.*.text' => [
                'required_unless:type,essay',
                'string',
            ],

            'options.*.is_correct' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalize options
        |--------------------------------------------------------------------------
        */

        $options = null;

        if ($validated['type'] !== 'essay') {
            $options = collect($validated['options'] ?? [])
                ->map(function ($option) {
                    return [
                        'text' => $option['text'],
                        'is_correct' => (bool) ($option['is_correct'] ?? false),
                    ];
                })
                ->values()
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | Multiple Choice validation
            |--------------------------------------------------------------------------
            */

            if (
                $validated['type'] === 'multiple_choice' &&
                count($options) < 2
            ) {
                return back()
                    ->withErrors([
                        'options' => 'Multiple choice must have at least 2 options.',
                    ])
                    ->withInput();
            }

            /*
            |--------------------------------------------------------------------------
            | Correct answer validation
            |--------------------------------------------------------------------------
            */

            $correctOption = collect($options)
                ->firstWhere('is_correct', true);

            if (!$correctOption) {
                return back()
                    ->withErrors([
                        'options' => 'Please select a correct answer.',
                    ])
                    ->withInput();
            }

            $correctAnswer = $correctOption['text'];
        } else {
            $correctAnswer = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Question
        |--------------------------------------------------------------------------
        */

        $question = Question::create([
            'tenant_id' => $user->tenant_id,
            'course_id' => $course->id,
            'created_by' => $user->id,

            'question_type' => $validated['type'],
            'question_text' => $validated['question_text'],

            'options' => $options,

            'correct_answer' => $correctAnswer,

            'points' => $validated['points'],

            'difficulty_level' => 'medium',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('instructor.questions.index', ['course' => $course->id])
            ->with('success', 'Question created successfully!');
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

        // Authorization
        if (
            !(
                $user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id
            ) &&
            !$user->hasRole('tenant_admin')
        ) {
            abort(403);
        }

        // Pastikan question milik course
        if ($question->course_id !== $course->id) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'question_type' => [
                'required',
                'in:multiple_choice,true_false,essay',
            ],

            'question_text' => [
                'required',
                'string',
            ],

            'options' => [
                'nullable',
                'array',
            ],

            'options.*.text' => [
                'required_if:question_type,multiple_choice',
                'string',
            ],

            'options.*.is_correct' => [
                'nullable',
                'boolean',
            ],

            'correct_answer' => [
                'nullable',
                'string',
            ],

            'points' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'difficulty_level' => [
                'required',
                'in:easy,medium,hard',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalize Options
        |--------------------------------------------------------------------------
        */

        $options = null;
        $correctAnswer = null;

        if ($validated['question_type'] === 'multiple_choice') {

            $options = collect($validated['options'] ?? [])
                ->map(function ($option) {
                    return [
                        'text' => $option['text'],
                        'is_correct' => (bool) ($option['is_correct'] ?? false),
                    ];
                })
                ->values()
                ->toArray();

            // Multiple Choice minimal 2 options
            if (count($options) < 2) {
                return back()
                    ->withErrors([
                        'options' => 'Multiple choice must have at least 2 options.',
                    ])
                    ->withInput();
            }

            // Cari correct option
            $correctOption = collect($options)
                ->firstWhere('is_correct', true);

            if (!$correctOption) {
                return back()
                    ->withErrors([
                        'options' => 'Please select a correct answer.',
                    ])
                    ->withInput();
            }

            $correctAnswer = $correctOption['text'];

        } elseif ($validated['question_type'] === 'true_false') {

            // True/False mengambil correct_answer langsung dari request
            $correctAnswer = $validated['correct_answer'] ?? null;

            if (!in_array($correctAnswer, ['True', 'False'], true)) {
                return back()
                    ->withErrors([
                        'correct_answer' => 'Correct answer must be True or False.',
                    ])
                    ->withInput();
            }

            $options = [
                [
                    'text' => 'True',
                    'is_correct' => $correctAnswer === 'True',
                ],
                [
                    'text' => 'False',
                    'is_correct' => $correctAnswer === 'False',
                ],
            ];

        } else {

            // Essay
            $options = null;
            $correctAnswer = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Update Question
        |--------------------------------------------------------------------------
        */

        $question->update([
            'question_type' => $validated['question_type'],
            'question_text' => $validated['question_text'],
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'points' => $validated['points'],
            'difficulty_level' => $validated['difficulty_level'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('instructor.questions.index', $course)
            ->with('success', 'Question updated!');
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
