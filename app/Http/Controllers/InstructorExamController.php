<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorExamController extends Controller
{
    public function monitor(Course $course, Quiz $quiz)
    {
        $this->authorize('update', $course);

        if ($quiz->course_id !== $course->id || !$quiz->is_exam) abort(404);

        $quiz->load(['questions', 'section']);

        $sessions = QuizAttempt::where('quiz_id', $quiz->id)
            ->with('user')
            ->latest()
            ->get();

        return Inertia::render('Instructor/Exams/Monitor', compact('course', 'quiz', 'sessions'));
    }

    public function sessions(Course $course, Quiz $quiz)
    {
        $this->authorize('update', $course);

        if ($quiz->course_id !== $course->id || !$quiz->is_exam) abort(404);

        $sessions = QuizAttempt::where('quiz_id', $quiz->id)
            ->with('user')
            ->latest()
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'user_name' => $a->user->name,
                'user_email' => $a->user->email,
                'status' => $a->status,
                'score' => $a->score,
                'tab_switches' => $a->tab_switch_count,
                'ip_address' => $a->ip_address,
                'started_at' => $a->started_at?->format('Y-m-d H:i:s'),
                'finished_at' => $a->finished_at?->format('Y-m-d H:i:s'),
            ]);

        return response()->json($sessions);
    }

    public function forceSubmit(Course $course, Quiz $quiz, QuizAttempt $attempt)
    {
        $this->authorize('update', $course);

        if ($quiz->course_id !== $course->id || $attempt->quiz_id !== $quiz->id) abort(404);
        if ($attempt->status !== 'in_progress') abort(400);

        $attempt->update([
            'finished_at' => now(),
            'status' => 'timeout',
        ]);

        return redirect()->route('instructor.exams.monitor', [$course, $quiz])
            ->with('success', 'Exam session terminated for ' . $attempt->user->name);
    }
}
