<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\GradebookItem;
use App\Models\Grade;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Notifications\QuizGraded;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorQuizController extends Controller
{
    public function index(Course $course)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $quizzes = Quiz::where('course_id', $course->id)
            ->with(['section', 'questions', 'creator'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Instructor/Quizzes/Index', compact('course', 'quizzes'));
    }

    public function create(Course $course)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $course->load('sections');
        $questions = Question::where('course_id', $course->id)->get();

        return Inertia::render('Instructor/Quizzes/Create', compact('course', 'questions'));
    }

    public function store(Request $request, Course $course)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'section_id' => ['required', 'exists:sections,id'],
            'passing_grade' => ['required', 'integer', 'min:0', 'max:100'],
            'time_limit' => ['nullable', 'integer', 'min:1'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'shuffle_questions' => ['boolean'],
            'show_result' => ['boolean'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['exists:questions,id'],
            'is_exam' => ['boolean'],
            'start_date' => ['nullable', 'date', 'required_if:is_exam,1'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_if:is_exam,1'],
            'grace_period' => ['nullable', 'integer', 'min:0'],
            'randomize_options' => ['boolean'],
            'prevent_copy_paste' => ['boolean'],
            'require_webcam' => ['boolean'],
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'section_id' => $validated['section_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'passing_grade' => $validated['passing_grade'],
            'time_limit' => $validated['time_limit'],
            'max_attempts' => $validated['max_attempts'],
            'shuffle_questions' => $validated['shuffle_questions'] ?? false,
            'show_result' => $validated['show_result'] ?? true,
            'is_exam' => $validated['is_exam'] ?? false,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'grace_period' => $validated['grace_period'] ?? null,
            'randomize_options' => $validated['randomize_options'] ?? false,
            'prevent_copy_paste' => $validated['prevent_copy_paste'] ?? false,
            'require_webcam' => $validated['require_webcam'] ?? false,
            'created_by' => $user->id,
        ]);

        foreach ($validated['question_ids'] as $order => $questionId) {
            $quiz->questions()->attach($questionId, [
                'order' => $order + 1,
                'points' => Question::find($questionId)->points,
            ]);
        }

        GradebookItem::create([
            'course_id' => $course->id,
            'title' => $quiz->title,
            'type' => 'quiz',
            'item_id' => $quiz->id,
            'weight' => 1.00,
            'max_score' => $quiz->questions->sum('points'),
        ]);

        return redirect()->route('instructor.quizzes.index', $course)
            ->with('success', 'Quiz created successfully!');
    }

    public function show(Course $course, Quiz $quiz)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($quiz->course_id !== $course->id) abort(404);

        $quiz->load(['questions', 'section', 'attempts.user']);

        return Inertia::render('Instructor/Quizzes/Show', compact('course', 'quiz'));
    }

    public function edit(Course $course, Quiz $quiz)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($quiz->course_id !== $course->id) abort(404);

        $course->load('sections');
        $quiz->load('questions');
        $questions = Question::where('course_id', $course->id)->get();

        return Inertia::render('Instructor/Quizzes/Edit', compact('course', 'quiz', 'questions'));
    }

    public function update(Request $request, Course $course, Quiz $quiz)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($quiz->course_id !== $course->id) abort(404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'section_id' => ['required', 'exists:sections,id'],
            'passing_grade' => ['required', 'integer', 'min:0', 'max:100'],
            'time_limit' => ['nullable', 'integer', 'min:1'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'shuffle_questions' => ['boolean'],
            'show_result' => ['boolean'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['exists:questions,id'],
            'is_exam' => ['boolean'],
            'start_date' => ['nullable', 'date', 'required_if:is_exam,1'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_if:is_exam,1'],
            'grace_period' => ['nullable', 'integer', 'min:0'],
            'randomize_options' => ['boolean'],
            'prevent_copy_paste' => ['boolean'],
            'require_webcam' => ['boolean'],
        ]);

        $quiz->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'section_id' => $validated['section_id'],
            'passing_grade' => $validated['passing_grade'],
            'time_limit' => $validated['time_limit'],
            'max_attempts' => $validated['max_attempts'],
            'shuffle_questions' => $validated['shuffle_questions'] ?? false,
            'show_result' => $validated['show_result'] ?? true,
            'is_exam' => $validated['is_exam'] ?? false,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'grace_period' => $validated['grace_period'] ?? null,
            'randomize_options' => $validated['randomize_options'] ?? false,
            'prevent_copy_paste' => $validated['prevent_copy_paste'] ?? false,
            'require_webcam' => $validated['require_webcam'] ?? false,
        ]);

        $quiz->questions()->detach();
        foreach ($validated['question_ids'] as $order => $questionId) {
            $quiz->questions()->attach($questionId, [
                'order' => $order + 1,
                'points' => Question::find($questionId)->points,
            ]);
        }

        GradebookItem::updateOrCreate(
            ['course_id' => $course->id, 'type' => 'quiz', 'item_id' => $quiz->id],
            ['title' => $quiz->title, 'weight' => 1.00, 'max_score' => $quiz->questions->sum('points')]
        );

        return redirect()->route('instructor.quizzes.index', $course)
            ->with('success', 'Quiz updated successfully!');
    }

    public function destroy(Course $course, Quiz $quiz)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($quiz->course_id !== $course->id) abort(404);

        GradebookItem::where('course_id', $course->id)
            ->where('type', 'quiz')
            ->where('item_id', $quiz->id)
            ->delete();

        $quiz->delete();

        return redirect()->route('instructor.quizzes.index', $course)
            ->with('success', 'Quiz deleted successfully!');
    }

    public function publish(Course $course, Quiz $quiz)
    {
        $user = auth()->user();
        if (!($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($quiz->course_id !== $course->id) abort(404);

        $quiz->update(['published' => true]);

        return redirect()->route('instructor.quizzes.show', [$course, $quiz])
            ->with('success', 'Quiz published successfully!');
    }

    public function essayReview(Course $course, Quiz $quiz, QuizAttempt $attempt)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($quiz->course_id !== $course->id || $attempt->quiz_id !== $quiz->id) abort(404);

        $attempt->load(['quiz.questions', 'answers.question']);

        $essayAnswers = $attempt->answers->filter(fn($a) => $a->question->question_type === 'essay');
        $autoGraded = $attempt->answers->filter(fn($a) => $a->question->question_type !== 'essay');

        return Inertia::render('Instructor/Quizzes/EssayReview', compact('course', 'quiz', 'attempt', 'essayAnswers', 'autoGraded'));
    }

    public function gradeEssay(Request $request, Course $course, Quiz $quiz, QuizAttempt $attempt)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($quiz->course_id !== $course->id || $attempt->quiz_id !== $quiz->id) abort(404);

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.id' => ['required', 'exists:quiz_answers,id'],
            'answers.*.points_earned' => ['required', 'numeric', 'min:0'],
            'answers.*.grader_notes' => ['nullable', 'string'],
        ]);

        $totalPoints = 0;
        foreach ($validated['answers'] as $data) {
            $answer = QuizAnswer::findOrFail($data['id']);
            if ($answer->quiz_attempt_id !== $attempt->id) continue;

            $answer->update([
                'points_earned' => $data['points_earned'],
                'is_correct' => $data['points_earned'] > 0,
                'grader_notes' => $data['grader_notes'] ?? null,
            ]);
            $totalPoints += $data['points_earned'];
        }

        // Recalculate total score
        $allAnswers = $attempt->answers()->with('question')->get();
        $earnedPoints = $allAnswers->sum('points_earned');
        $totalPossible = $allAnswers->sum(fn($a) => $a->question->pivot?->points ?? $a->question->points);
        $score = $totalPossible > 0 ? round(($earnedPoints / $totalPossible) * 100, 2) : 0;

        $attempt->update([
            'score' => $score,
            'total_points' => $earnedPoints,
        ]);

        // Update grade
        $gradebookItem = GradebookItem::where('course_id', $course->id)
            ->where('type', 'quiz')
            ->where('item_id', $quiz->id)
            ->first();
        if ($gradebookItem) {
            Grade::updateOrCreate(
                ['user_id' => $attempt->user_id, 'gradebook_item_id' => $gradebookItem->id],
                [
                    'course_id' => $course->id,
                    'score' => $earnedPoints,
                    'percentage' => $score,
                    'letter_grade' => $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 70 ? 'C' : ($score >= 60 ? 'D' : 'E'))),
                ]
            );
        }

        $attempt->user->notify(new QuizGraded($attempt));

        // Gamification
        $targetUser = $attempt->user;
        if ($score >= $quiz->passing_grade) {
            app(GamificationService::class)->awardPoints($targetUser, $score == 100 ? 'quiz_perfect' : 'quiz_passed', $quiz->course_id);
            app(GamificationService::class)->checkBadges($targetUser);
        }

        return redirect()->route('instructor.quizzes.essay-review', [$course, $quiz, $attempt])
            ->with('success', 'Essay grades saved successfully!');
    }

    public function unpublish(Course $course, Quiz $quiz)
    {
        $user = auth()->user();
        if (!($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        if ($quiz->course_id !== $course->id) abort(404);

        $quiz->update(['published' => false]);

        return redirect()->route('instructor.quizzes.show', [$course, $quiz])
            ->with('success', 'Quiz unpublished successfully!');
    }
}
