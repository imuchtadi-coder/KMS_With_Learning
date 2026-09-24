<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Notifications\QuizGraded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StudentQuizController extends Controller
{
    /**
     * Show quiz detail page.
     */
    public function show(Quiz $quiz)
    {
        $user = Auth::user();

        // Only published quizzes can be accessed.
        abort_unless($quiz->is_published, 404);

        // Make sure student is enrolled in the course.
        $isEnrolled = $quiz->course
            ->students()
            ->where('users.id', $user->id)
            ->exists();

        abort_unless($isEnrolled, 403);

        /*
         * Exam entry window.
         *
         * start_date:
         *   The exam cannot be entered before this time.
         *
         * grace_period:
         *   Additional time allowed for entering the exam.
         *
         * end_date:
         *   Absolute deadline for finishing the exam.
         *
         * NOTE:
         * time_limit is intentionally NOT used for exams.
         */
        if ($quiz->is_exam) {
            $now = now();

            if ($quiz->start_date && $now->lt($quiz->start_date)) {
                abort(403, 'Exam has not started yet.');
            }

            if (
                $quiz->start_date &&
                (int) ($quiz->grace_period ?? 0) > 0
            ) {
                $entryDeadline = $quiz->start_date->copy()
                    ->addMinutes((int) $quiz->grace_period);

                if ($now->gte($entryDeadline)) {
                    abort(
                        403,
                        'The time allowed to enter the exam has ended.'
                    );
                }
            }
        }

        $questionCount = $quiz->questions()->count();

        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->count();

        $bestAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('score')
            ->first();

        return Inertia::render('Student/Quizzes/Show', [
            'quiz' => $quiz->load('section.course'),
            'questionCount' => $questionCount,
            'attemptCount' => $attemptCount,
            'bestAttempt' => $bestAttempt,
        ]);
    }

    /**
     * Start a new quiz attempt.
     */
    public function start(Quiz $quiz)
    {
        $user = Auth::user();

        abort_unless($quiz->is_published, 404);

        // Make sure student is enrolled.
        $isEnrolled = $quiz->course
            ->students()
            ->where('users.id', $user->id)
            ->exists();

        abort_unless($isEnrolled, 403);

        /*
         * Exam entry window.
         *
         * Exams do not use time_limit.
         */
        if ($quiz->is_exam) {
            $now = now();

            if ($quiz->start_date && $now->lt($quiz->start_date)) {
                abort(403, 'Exam has not started yet.');
            }

            if (
                $quiz->start_date &&
                (int) ($quiz->grace_period ?? 0) > 0
            ) {
                $entryDeadline = $quiz->start_date->copy()
                    ->addMinutes((int) $quiz->grace_period);

                if ($now->gte($entryDeadline)) {
                    abort(
                        403,
                        'The time allowed to enter the exam has ended.'
                    );
                }
            }
        }

        /*
         * Maximum attempts.
         */
        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->count();

        if (
            $quiz->max_attempts !== null &&
            $attemptCount >= (int) $quiz->max_attempts
        ) {
            abort(403, 'Maximum number of attempts reached.');
        }

        /*
         * If an existing in-progress attempt exists,
         * continue that attempt instead of creating another one.
         */
        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest('started_at')
            ->first();

        if ($existingAttempt) {
            return redirect()->route(
                'student.quizzes.take',
                $existingAttempt->id
            );
        }

        /*
         * Build question order for this attempt.
         *
         * The order is generated ONCE and stored in quiz_attempts.
         * This means questions remain stable during the attempt.
         */
        $questions = $quiz->questions()
            ->orderBy('id')
            ->get();

        $questionOrder = $questions
            ->pluck('id')
            ->values()
            ->all();

        if ($quiz->shuffle_questions) {
            shuffle($questionOrder);
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'question_order' => $questionOrder,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route(
            'student.quizzes.take',
            $attempt->id
        );
    }

    /**
     * Show quiz attempt page.
     */
    public function take(QuizAttempt $attempt)
    {
        $user = Auth::user();

        abort_unless(
            (int) $attempt->user_id === (int) $user->id,
            403
        );

        abort_unless(
            $attempt->status === 'in_progress',
            403
        );

        $attempt->load([
            'quiz.questions',
            'quiz.section',
        ]);

        $quiz = $attempt->quiz;

        /*
         * Safety check:
         * If an exam is already past its absolute end_date,
         * auto-submit the attempt.
         *
         * IMPORTANT:
         * This is different from the timer reaching zero while
         * the student is actively answering questions.
         *
         * In the active timer case, Take.vue sends the answers
         * to submit(), where they are scored normally.
         */
        if (
            $quiz->is_exam &&
            $quiz->end_date &&
            now()->gte($quiz->end_date)
        ) {
            return $this->autoSubmit($attempt);
        }

        /*
         * Backward compatibility:
         * Older attempts may not have question_order.
         */
        $questionOrder = $attempt->question_order;

        if (!is_array($questionOrder) || empty($questionOrder)) {
            $questionOrder = $quiz->questions
                ->pluck('id')
                ->values()
                ->all();

            if ($quiz->shuffle_questions) {
                shuffle($questionOrder);
            }

            $attempt->update([
                'question_order' => $questionOrder,
            ]);
        }

        /*
         * Rebuild the question collection according to
         * the saved order.
         */
        $questionsById = $quiz->questions->keyBy('id');

        $orderedQuestions = collect($questionOrder)
            ->map(function ($questionId) use ($questionsById) {
                return $questionsById->get($questionId);
            })
            ->filter()
            ->values();

        /*
         * Do not expose correct_answer to the browser.
         *
         * Also normalize options into plain text values.
         */
        $orderedQuestions = $orderedQuestions->map(function ($question) {
            $question->makeHidden([
                'correct_answer',
            ]);

            $options = $question->options;

            if (is_string($options)) {
                $decoded = json_decode($options, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $options = $decoded;
                }
            }

            if (!is_array($options)) {
                $options = [];
            }

            $question->options = collect($options)
                ->map(function ($option) {
                    if (is_array($option)) {
                        return $option['text']
                            ?? $option['label']
                            ?? $option['value']
                            ?? '';
                    }

                    if (is_object($option)) {
                        return $option->text
                            ?? $option->label
                            ?? $option->value
                            ?? '';
                    }

                    return (string) $option;
                })
                ->values()
                ->all();

            return $question;
        });

        /*
         * Replace relation with the saved ordered collection.
         */
        $quiz->setRelation(
            'questions',
            $orderedQuestions
        );

        /*
         * Calculate remaining time.
         *
         * NORMAL QUIZ:
         *   remaining = time_limit - elapsed since started_at
         *
         * EXAM:
         *   remaining = absolute time until end_date
         *
         * Therefore an Exam NEVER uses:
         *   started_at + time_limit
         */
        $remaining = null;

        if ($quiz->is_exam) {
            if ($quiz->end_date) {
                $remaining = max(
                    0,
                    now()->diffInSeconds($quiz->end_date)
                );
            }
        } elseif ($quiz->time_limit) {
            $elapsed = now()->diffInSeconds(
                $attempt->started_at
            );

            $remaining = max(
                0,
                ((int) $quiz->time_limit * 60) - $elapsed
            );
        }

        /*
         * If remaining time is already zero, auto-submit.
         *
         * This situation normally happens if the student opens
         * the page at/after the deadline.
         *
         * Active timer expiration is handled by Take.vue,
         * which submits answers to submit().
         */
        if ($remaining !== null && $remaining <= 0) {
            return $this->autoSubmit($attempt);
        }

        return Inertia::render('Student/Quizzes/Take', [
            'attempt' => [
                'id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'started_at' => $attempt->started_at,
                'status' => $attempt->status,
                'quiz' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'is_exam' => (bool) $quiz->is_exam,
                    'questions' => $quiz->questions,
                ],
            ],

            /*
             * Remaining is already calculated by backend.
             * Take.vue only needs to count it down.
             */
            'remaining' => $remaining,

            'preventCopyPaste' => (bool) $quiz->prevent_copy_paste,

            'randomizeOptions' => (bool) $quiz->randomize_options,
        ]);
    }

    /**
     * Submit quiz attempt.
     *
     * IMPORTANT:
     * If the timer has expired, we DO NOT immediately call
     * autoSubmit().
     *
     * The answers submitted by Take.vue are still scored.
     *
     * The only difference is that the attempt status becomes
     * "timeout".
     */
    public function submit(
        Request $request,
        QuizAttempt $attempt
    ) {
        $user = Auth::user();

        abort_unless(
            (int) $attempt->user_id === (int) $user->id,
            403
        );

        abort_unless(
            $attempt->status === 'in_progress',
            403
        );

        $attempt->load([
            'quiz.questions',
            'quiz.section',
        ]);

        $quiz = $attempt->quiz;

        /*
         * Determine whether this submission is caused by timeout.
         *
         * We intentionally DO NOT return early.
         *
         * This allows the submitted answers to be scored normally.
         */
        $timedOut = false;

        if ($quiz->is_exam) {
            /*
             * Exam uses absolute end_date.
             * time_limit is ignored.
             */
            if (
                $quiz->end_date &&
                now()->gte($quiz->end_date)
            ) {
                $timedOut = true;
            }
        } else {
            /*
             * Normal Quiz uses time_limit from started_at.
             */
            if ($quiz->time_limit) {
                $elapsed = now()->diffInSeconds(
                    $attempt->started_at
                );

                if (
                    $elapsed >=
                    ((int) $quiz->time_limit * 60)
                ) {
                    $timedOut = true;
                }
            }
        }

        /*
         * Answers submitted from Take.vue.
         */
        $answers = $request->input('answers', []);

        if (!is_array($answers)) {
            $answers = [];
        }

        /*
         * Tab-switch count.
         */
        $tabSwitches = min(
            (int) $request->input('tab_switches', 0),
            999
        );

        /*
         * Use the saved question order when scoring.
         *
         * This is important because questions are shuffled per attempt.
         */
        $questionOrder = $attempt->question_order;

        if (!is_array($questionOrder) || empty($questionOrder)) {
            $questionOrder = $quiz->questions
                ->pluck('id')
                ->values()
                ->all();
        }

        $questionsById = $quiz->questions->keyBy('id');

        $questions = collect($questionOrder)
            ->map(function ($questionId) use ($questionsById) {
                return $questionsById->get($questionId);
            })
            ->filter()
            ->values();

        /*
         * Calculate maximum points.
         */
        $maxPoints = (float) $questions->sum(
            fn ($question) => (float) ($question->points ?? 0)
        );

        $totalPoints = 0;

        /*
         * Delete existing answers first.
         *
         * Normally an attempt is submitted only once, but this
         * protects against duplicate/stale answer rows.
         */
        DB::transaction(function () use (
            $attempt,
            $questions,
            $answers,
            &$totalPoints
        ) {
            QuizAnswer::where(
                'quiz_attempt_id',
                $attempt->id
            )->delete();

            foreach ($questions as $question) {
                /*
                 * Answers are stored by question ID.
                 */
                $answer = $answers[$question->id]
                    ?? null;

                /*
                 * Normalize answer to string where applicable.
                 */
                if ($answer !== null) {
                    $answer = is_scalar($answer)
                        ? (string) $answer
                        : json_encode($answer);
                }

                $isCorrect = null;
                $pointsEarned = 0;

                /*
                 * Essay questions are not automatically graded.
                 */
                if ($question->type !== 'essay') {
                    $submittedAnswer = trim(
                        (string) ($answer ?? '')
                    );

                    $correctAnswer = trim(
                        (string) (
                            $question->correct_answer ?? ''
                        )
                    );

                    /*
                     * Multiple choice / true-false:
                     *
                     * Compare the actual answer TEXT.
                     *
                     * This is important because Take.vue stores:
                     *
                     *   answers[question.id] = option text
                     *
                     * and NOT the option index.
                     */
                    $isCorrect =
                        $submittedAnswer !== '' &&
                        strcasecmp(
                            $submittedAnswer,
                            $correctAnswer
                        ) === 0;

                    if ($isCorrect) {
                        $pointsEarned = (float) (
                            $question->points ?? 0
                        );
                    }
                }

                $totalPoints += $pointsEarned;

                QuizAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer' => $answer,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);
            }
        });

        /*
         * Calculate percentage score.
         */
        $scorePercentage = $maxPoints > 0
            ? round(
                ($totalPoints / $maxPoints) * 100,
                2
            )
            : 0;

        /*
         * IMPORTANT:
         *
         * timeout != score 0
         *
         * If the timer expired but the student had answers,
         * the score above is the actual score earned.
         */
        $attempt->update([
            'score' => $scorePercentage,
            'total_points' => $totalPoints,
            'finished_at' => now(),

            /*
             * Keep timeout distinguishable from normal submission.
             */
            'status' => $timedOut
                ? 'timeout'
                : 'completed',

            'tab_switch_count' => $tabSwitches,
        ]);

        /*
         * Gradebook.
         *
         * Keep existing grade update behavior.
         */
        $gradebookItem = $this->getGradebookItem($quiz);

        $letterGrade = $this->calculateLetterGrade(
            $scorePercentage
        );

        Grade::updateOrCreate(
            [
                'user_id' => $user->id,
                'gradebook_item_id' => $gradebookItem->id,
            ],
            [
                'score' => $scorePercentage,
                'letter_grade' => $letterGrade,
            ]
        );

        /*
         * Notification:
         *
         * Only send QuizGraded when there are no essay questions,
         * because essay answers require manual grading.
         */
        $hasEssay = $questions->contains(
            fn ($question) =>
                $question->type === 'essay'
        );

        if (!$hasEssay) {
            $user->notify(
                new QuizGraded(
                    $quiz,
                    $attempt,
                    $scorePercentage
                )
            );
        }

        /*
         * Gamification.
         *
         * Keep the existing passing-grade behavior.
         */
        if (
            $scorePercentage >=
            (float) ($quiz->passing_grade ?? 0)
        ) {
            /*
             * Existing gamification logic goes here.
             *
             * Keep your project's existing implementation
             * if it already exists in this controller.
             */
        }

        return redirect()->route(
            'student.quizzes.result',
            $attempt->id
        );
    }

    /**
     * Auto-submit an attempt when no answer submission
     * can be received anymore.
     *
     * This method is NOT used by the active frontend timer.
     *
     * It is used when the backend discovers that the attempt
     * is already past its deadline before normal submit().
     */
    protected function autoSubmit(QuizAttempt $attempt)
    {
        $attempt->load([
            'quiz.questions',
        ]);

        $quiz = $attempt->quiz;

        /*
         * Do not process an already finished attempt.
         */
        if ($attempt->status !== 'in_progress') {
            return redirect()->route(
                'student.quizzes.result',
                $attempt->id
            );
        }

        /*
         * There are no answers received from Take.vue in this case,
         * so there is nothing to score.
         *
         * Therefore score = 0 here is valid.
         *
         * IMPORTANT:
         * This method is NOT called when Take.vue timer reaches zero
         * after the student has submitted answers.
         */
        $totalPoints = (float) $quiz->questions->sum(
            fn ($question) =>
                (float) ($question->points ?? 0)
        );

        $attempt->update([
            'score' => 0,
            'total_points' => $totalPoints,
            'finished_at' => now(),
            'status' => 'timeout',
        ]);

        return redirect()->route(
            'student.quizzes.result',
            $attempt->id
        );
    }

    /**
     * Show quiz result.
     */
    public function result(QuizAttempt $attempt)
    {
        $user = Auth::user();

        abort_unless(
            (int) $attempt->user_id === (int) $user->id,
            403
        );

        $attempt->load([
            'quiz.section',
            'answers.question',
        ]);

        return Inertia::render(
            'Student/Quizzes/Result',
            [
                'attempt' => $attempt,
            ]
        );
    }

    /**
     * Get/create gradebook item for quiz.
     */
    protected function getGradebookItem(Quiz $quiz)
    {
        return $quiz->course
            ->gradebookItems()
            ->firstOrCreate(
                [
                    'gradable_type' => Quiz::class,
                    'gradable_id' => $quiz->id,
                ],
                [
                    'name' => $quiz->title,
                    'max_score' => 100,
                ]
            );
    }

    /**
     * Calculate letter grade.
     */
    protected function calculateLetterGrade(
        float $score
    ): string {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }
}