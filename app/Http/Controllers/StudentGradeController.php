<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradebookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class StudentGradeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->with('course')
            ->get();

        $grades = $enrollments->map(function ($enrollment) use ($user) {
            $gradebookItems = GradebookItem::where('course_id', $enrollment->course_id)->get();
            $userGrades = Grade::where('user_id', $user->id)
                ->where('course_id', $enrollment->course_id)
                ->get()
                ->keyBy('gradebook_item_id');

            $totalPercentage = 0;
            $totalWeight = 0;

            foreach ($gradebookItems as $item) {
                $grade = $userGrades->get($item->id);
                if ($grade && $grade->percentage !== null) {
                    $totalPercentage += $grade->percentage * $item->weight;
                    $totalWeight += $item->weight;
                }
            }

            $finalPercentage = $totalWeight > 0 ? round($totalPercentage / $totalWeight, 2) : null;

            $enrollment->gradebook_items = $gradebookItems;
            $enrollment->user_grades = $userGrades;
            $enrollment->final_percentage = $finalPercentage;
            $enrollment->final_grade = $finalPercentage ? $this->calculateLetterGrade($finalPercentage) : '-';

            return $enrollment;
        });

        return Inertia::render('Student/Grades/Index', [
            'grades' => $grades,
        ]);
    }

    public function course(Course $course)
    {
        $user = auth()->user();

        $enrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();
        if (!$enrolled) abort(403, 'You are not enrolled in this course.');

        $gradebookItems = GradebookItem::where('course_id', $course->id)->get();
        $grades = Grade::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('gradebook_item_id');

        // Calculate per-item class averages
        $classAverages = [];
        $enrolledIds = Enrollment::where('course_id', $course->id)->pluck('user_id');
        foreach ($gradebookItems as $item) {
            $itemGrades = Grade::where('gradebook_item_id', $item->id)
                ->whereIn('user_id', $enrolledIds)
                ->pluck('percentage')
                ->filter();
            $classAverages[$item->id] = $itemGrades->count() > 0 ? round($itemGrades->avg(), 2) : null;
        }

        // Calculate student ranking
        $allStudents = Enrollment::where('course_id', $course->id)->with('user')->get();
        $rankings = [];
        foreach ($allStudents as $enrollment) {
            $studentGrades = Grade::where('user_id', $enrollment->user_id)
                ->where('course_id', $course->id)
                ->get();
            $totalPct = 0;
            $totalW = 0;
            foreach ($gradebookItems as $item) {
                $g = $studentGrades->firstWhere('gradebook_item_id', $item->id);
                if ($g && $g->percentage !== null) {
                    $totalPct += $g->percentage * $item->weight;
                    $totalW += $item->weight;
                }
            }
            if ($totalW > 0) {
                $rankings[] = ['user_id' => $enrollment->user_id, 'pct' => round($totalPct / $totalW, 2)];
            }
        }
        usort($rankings, fn($a, $b) => $b['pct'] <=> $a['pct']);

        $studentRank = null;
        $totalRanked = count($rankings);
        foreach ($rankings as $i => $r) {
            if ($r['user_id'] === $user->id) {
                $studentRank = $i + 1;
                break;
            }
        }

        $totalPercentage = 0;
        $totalWeight = 0;
        foreach ($gradebookItems as $item) {
            $grade = $grades->get($item->id);
            if ($grade && $grade->percentage !== null) {
                $totalPercentage += $grade->percentage * $item->weight;
                $totalWeight += $item->weight;
            }
        }
        $finalPercentage = $totalWeight > 0 ? round($totalPercentage / $totalWeight, 2) : null;

        return Inertia::render('Student/Grades/Course', [
            'course' => $course,
            'gradebookItems' => $gradebookItems,
            'grades' => $grades,
            'finalPercentage' => $finalPercentage,
            'classAverages' => $classAverages,
            'studentRank' => $studentRank,
            'totalRanked' => $totalRanked,
        ]);
    }

    public function transcript(Course $course)
    {
        $user = auth()->user();

        $enrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();
        if (!$enrolled) abort(403);

        $gradebookItems = GradebookItem::where('course_id', $course->id)->get();
        $grades = Grade::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('gradebook_item_id');

        $totalPercentage = 0;
        $totalWeight = 0;
        foreach ($gradebookItems as $item) {
            $grade = $grades->get($item->id);
            if ($grade && $grade->percentage !== null) {
                $totalPercentage += $grade->percentage * $item->weight;
                $totalWeight += $item->weight;
            }
        }
        $finalPercentage = $totalWeight > 0 ? round($totalPercentage / $totalWeight, 2) : null;
        $finalGrade = $finalPercentage ? $this->calculateLetterGrade($finalPercentage) : '-';

        $pdf = Pdf::loadView('student.grades.transcript-pdf', compact(
            'course', 'gradebookItems', 'grades', 'finalPercentage', 'finalGrade', 'user'
        ));

        return $pdf->download('transcript-' . Str::slug($course->title) . '-' . now()->format('Y-m-d') . '.pdf');
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
