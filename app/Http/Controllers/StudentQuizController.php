<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Grade;
use App\Models\GradebookItem;
use App\Models\Enrollment;
use App\Notifications\QuizGraded;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class StudentQuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        $user = auth()->user();
        if (!$quiz->published) abort(404);

        $enrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->exists();
        if (!$enrolled) abort(403, 'You are not enrolled in this course.');

        if ($quiz->is_exam) {
            $now = now();
            if ($quiz->start_date && $now->lt($quiz->start_date)) {
                abort(403, 'Exam has not started yet.');
            }
            if ($quiz->end_date && $now->gt($quiz->end_date)) {
                abort(403, 'Exam has ended.');
            }
        }

        $quiz->loadCount('questions');

        $attemptsCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->count();

        $bestAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('score')
            ->first();

        return inertia('Student/Quizzes/Show', compact('quiz', 'attemptsCount', 'bestAttempt'));
    }

    public function start(Quiz $quiz)
    {
        $user = auth()->user();
        if (!$quiz->published) abort(404);

        $enrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->exists();
        if (!$enrolled) abort(403);

        if ($quiz->is_exam) {
            $now = now();
            if ($quiz->start_date && $now->lt($quiz->start_date)) {
                return redirect()->route('student.quizzes.show', $quiz)
                    ->with('error', 'Exam has not started yet.');
            }
            if ($quiz->end_date && $now->gt($quiz->end_date)) {
                return redirect()->route('student.quizzes.show', $quiz)
                    ->with('error', 'Exam has ended.');
            }
        }

        $attemptsCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->count();

        if ($attemptsCount >= $quiz->max_attempts) {
            return redirect()->route('student.quizzes.show', $quiz)
                ->with('error', 'You have reached the maximum number of attempts.');
        }

        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('student.quizzes.take', $existingAttempt);
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('student.quizzes.take', $attempt);
    }

    public function take(QuizAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.quizzes.result', $attempt);
        }

        $attempt->load(['quiz.questions', 'quiz.section']);

        if ($attempt->quiz->time_limit) {
            $elapsed = now()->diffInMinutes($attempt->started_at);
            if ($elapsed >= $attempt->quiz->time_limit) {
                $this->autoSubmit($attempt);
                return redirect()->route('student.quizzes.result', $attempt);
            }
        }

        $remaining = null;
        if ($attempt->quiz->time_limit) {
            $remaining = ($attempt->quiz->time_limit * 60) - now()->diffInSeconds($attempt->started_at);
            if ($remaining < 0) $remaining = 0;
        }

        $preventCopyPaste = $attempt->quiz->prevent_copy_paste;
        $randomizeOptions = $attempt->quiz->randomize_options;

        return inertia('Student/Quizzes/Take', compact('attempt', 'remaining', 'preventCopyPaste', 'randomizeOptions'));
    }

    public function submit(Request $request, QuizAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.quizzes.result', $attempt);
        }

        $quiz = $attempt->quiz;

        if ($quiz->time_limit) {
            $elapsed = now()->diffInSeconds($attempt->started_at);
            if ($elapsed > $quiz->time_limit * 60) {
                return $this->autoSubmit($attempt);
            }
        }

        $answers = $request->input('answers', []);

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($quiz->questions as $question) {
            $answerText = $answers[$question->id] ?? null;
            $isCorrect = null;
            $pointsEarned = 0;

            if ($question->question_type !== 'essay') {
                $isCorrect = strtolower((string) $answerText) === strtolower((string) $question->correct_answer);
                $pointsEarned = $isCorrect ? $question->points : 0;
            }

            QuizAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_text' => is_array($answerText) ? json_encode($answerText) : $answerText,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);

            $totalPoints += $question->points;
            $earnedPoints += $pointsEarned;
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        $tabSwitches = min((int) $request->input('tab_switches', 0), 999);

        $attempt->update([
            'score' => $score,
            'total_points' => $totalPoints,
            'finished_at' => now(),
            'status' => 'completed',
            'tab_switch_count' => $tabSwitches,
        ]);

        $percentage = $score;

        Grade::updateOrCreate(
            ['user_id' => $user->id, 'gradebook_item_id' => $this->getGradebookItem($quiz)->id],
            [
                'course_id' => $quiz->course_id,
                'score' => $earnedPoints,
                'percentage' => $percentage,
                'letter_grade' => $this->calculateLetterGrade($percentage),
            ]
        );

        // Only notify if no essay questions (fully auto-graded)
        if ($quiz->questions()->where('question_type', 'essay')->doesntExist()) {
            $user->notify(new QuizGraded($attempt));
        }

        // Gamification
        if ($percentage >= $quiz->passing_grade) {
            app(GamificationService::class)->awardPoints($user, $percentage == 100 ? 'quiz_perfect' : 'quiz_passed', $quiz->course_id);
            app(GamificationService::class)->checkBadges($user);
        }

        return redirect()->route('student.quizzes.result', $attempt);
    }

    public function result(QuizAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);

        $attempt->load(['quiz.section', 'answers.question']);

        return inertia('Student/Quizzes/Result', compact('attempt'));
    }

    private function autoSubmit(QuizAttempt $attempt)
    {
        $quiz = $attempt->quiz;
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
        }

        $attempt->update([
            'score' => 0,
            'total_points' => $totalPoints,
            'finished_at' => now(),
            'status' => 'timeout',
        ]);

        return redirect()->route('student.quizzes.result', $attempt);
    }

    private function getGradebookItem(Quiz $quiz)
    {
        return GradebookItem::firstOrCreate(
            ['course_id' => $quiz->course_id, 'type' => 'quiz', 'item_id' => $quiz->id],
            ['title' => $quiz->title, 'weight' => 1.00, 'max_score' => $quiz->questions->sum('points')]
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
}
