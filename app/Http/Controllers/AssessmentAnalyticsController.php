<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\Assignment;
use App\Models\GradebookItem;
use App\Models\Grade;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AssessmentAnalyticsController extends Controller
{
    public function tenant()
    {
        $user = auth()->user();
        if (!$user->hasRole(['superadmin', 'tenant_admin'])) {
            abort(403);
        }

        $tenantId = $user->tenant_id;
        $courses = Course::where('tenant_id', $tenantId)->get();
        $courseIds = $courses->pluck('id');

        $totalCourses = $courses->count();
        $publishedCourses = $courses->where('published', true)->count();
        $totalEnrollments = Enrollment::whereIn('course_id', $courseIds)->count();
        $totalStudents = User::where('tenant_id', $tenantId)->role('student')->count();
        $totalInstructors = User::where('tenant_id', $tenantId)->role('instructor')->count();
        $totalQuizzes = Quiz::whereIn('course_id', $courseIds)->count();
        $totalAssignments = Assignment::whereIn('course_id', $courseIds)->count();

        // Per-course analytics
        $courseAnalytics = $courses->map(function ($course) {
            $items = GradebookItem::where('course_id', $course->id)->get();
            $enrollments = Enrollment::where('course_id', $course->id)->count();

            if ($items->count() > 0 && $enrollments > 0) {
                $allGrades = Grade::where('course_id', $course->id)->get();
                $avgPct = $allGrades->whereNotNull('percentage')->avg('percentage');
                $passCount = 0;
                $totalWithGrades = 0;
                $studentIds = Enrollment::where('course_id', $course->id)->pluck('user_id');
                foreach ($studentIds as $sid) {
                    $studentGrades = $allGrades->where('user_id', $sid);
                    $totalPct = 0;
                    $totalW = 0;
                    foreach ($items as $item) {
                        $g = $studentGrades->firstWhere('gradebook_item_id', $item->id);
                        if ($g && $g->percentage !== null) {
                            $totalPct += $g->percentage * $item->weight;
                            $totalW += $item->weight;
                        }
                    }
                    if ($totalW > 0) {
                        $totalWithGrades++;
                        if (($totalPct / $totalW) >= 70) $passCount++;
                    }
                }
                $passRate = $totalWithGrades > 0 ? round(($passCount / $totalWithGrades) * 100, 2) : null;
            } else {
                $avgPct = null;
                $passRate = null;
            }

            return [
                'id' => $course->id,
                'title' => $course->title,
                'published' => $course->published,
                'enrollments' => $enrollments,
                'instructor' => $course->instructor?->name ?? 'N/A',
                'avg_score' => $avgPct !== null ? round($avgPct, 2) : null,
                'pass_rate' => $passRate,
                'quiz_count' => Quiz::where('course_id', $course->id)->count(),
                'assignment_count' => Assignment::where('course_id', $course->id)->count(),
            ];
        });

        return Inertia::render('Admin/Analytics/Assessment', compact(
            'totalCourses', 'publishedCourses', 'totalEnrollments', 'totalStudents',
            'totalInstructors', 'totalQuizzes', 'totalAssignments', 'courseAnalytics'
        ));
    }

    public function instructorDashboard()
    {
        $user = auth()->user();
        if (!$user->hasRole('instructor')) abort(403);

        $courses = Course::where('tenant_id', $user->tenant_id)
            ->where('created_by', $user->id)
            ->with(['enrollments', 'gradebookItems.grades'])
            ->get();

        $totalCourses = $courses->count();
        $totalEnrollments = $courses->sum(fn($c) => $c->enrollments->count());
        $totalQuizzes = Quiz::whereIn('course_id', $courses->pluck('id'))->count();
        $totalAssignments = Assignment::whereIn('course_id', $courses->pluck('id'))->count();

        $overallAvgScore = null;
        $overallPassRate = null;
        $allScores = [];
        $studentCourseMap = [];

        foreach ($courses as $course) {
            $items = $course->gradebookItems;
            foreach ($course->enrollments as $enrollment) {
                $totalPct = 0;
                $totalW = 0;
                foreach ($items as $item) {
                    $grade = $item->grades->firstWhere('user_id', $enrollment->user_id);
                    if ($grade && $grade->percentage !== null) {
                        $totalPct += $grade->percentage * $item->weight;
                        $totalW += $item->weight;
                    }
                }
                if ($totalW > 0) {
                    $finalPct = $totalPct / $totalW;
                    $allScores[] = $finalPct;
                    $studentCourseMap[] = ['course_id' => $course->id, 'pct' => $finalPct];
                }
            }
        }

        if (count($allScores) > 0) {
            $overallAvgScore = round(array_sum($allScores) / count($allScores), 2);
            $passCount = count(array_filter($allScores, fn($s) => $s >= 70));
            $overallPassRate = round(($passCount / count($allScores)) * 100, 2);
        }

        // Per-course breakdown
        $coursePerformance = $courses->map(function ($course) {
            $items = $course->gradebookItems;
            $enrollments = $course->enrollments;
            $scores = [];
            foreach ($enrollments as $enrollment) {
                $totalPct = 0;
                $totalW = 0;
                foreach ($items as $item) {
                    $grade = $item->grades->firstWhere('user_id', $enrollment->user_id);
                    if ($grade && $grade->percentage !== null) {
                        $totalPct += $grade->percentage * $item->weight;
                        $totalW += $item->weight;
                    }
                }
                if ($totalW > 0) $scores[] = $totalPct / $totalW;
            }
            return [
                'id' => $course->id,
                'title' => $course->title,
                'published' => $course->published,
                'enrollments' => $enrollments->count(),
                'quiz_count' => Quiz::where('course_id', $course->id)->count(),
                'assignment_count' => Assignment::where('course_id', $course->id)->count(),
                'avg_score' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : null,
                'pass_rate' => count($scores) > 0 ? round((count(array_filter($scores, fn($s) => $s >= 70)) / count($scores)) * 100, 2) : null,
            ];
        });

        return Inertia::render('Instructor/Analytics/Dashboard', compact(
            'totalCourses', 'totalEnrollments', 'totalQuizzes', 'totalAssignments',
            'overallAvgScore', 'overallPassRate', 'coursePerformance'
        ));
    }

    public function course(Course $course)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $gradebookItems = GradebookItem::where('course_id', $course->id)
            ->with('grades')
            ->get();

        $enrollments = Enrollment::where('course_id', $course->id)->with('user')->get();
        $studentIds = $enrollments->pluck('user_id');

        $quizzes = Quiz::where('course_id', $course->id)->with('attempts')->get();
        $assignments = Assignment::where('course_id', $course->id)->with('submissions')->get();

        $totalQuizzes = $quizzes->count();
        $totalAssignments = $assignments->count();
        $totalStudents = $enrollments->count();

        $overallAvgScore = $this->calculateOverallAvg($gradebookItems);
        $overallPassingRate = $this->calculatePassingRate($gradebookItems, $studentIds);

        $scoreDistribution = $this->calculateScoreDistribution($gradebookItems, $studentIds);

        $assessmentPerformance = $this->buildAssessmentPerformance($gradebookItems, $quizzes, $assignments);

        $studentRankings = $this->buildStudentRankings($gradebookItems, $enrollments);

        $completionRate = $this->calculateCompletionRate($quizzes, $assignments, $studentIds);

        return Inertia::render('Instructor/Analytics/Course', compact(
            'course',
            'totalQuizzes',
            'totalAssignments',
            'totalStudents',
            'overallAvgScore',
            'overallPassingRate',
            'scoreDistribution',
            'assessmentPerformance',
            'studentRankings',
            'completionRate'
        ));
    }

    private function calculateOverallAvg($gradebookItems): ?float
    {
        $scores = [];
        foreach ($gradebookItems as $item) {
            foreach ($item->grades as $grade) {
                if ($grade->percentage !== null) {
                    $scores[] = $grade->percentage;
                }
            }
        }
        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : null;
    }

    private function calculatePassingRate($gradebookItems, $studentIds): ?float
    {
        $passCount = 0;
        $totalCount = 0;

        foreach ($studentIds as $studentId) {
            $studentGrades = Grade::where('user_id', $studentId)
                ->whereIn('gradebook_item_id', $gradebookItems->pluck('id'))
                ->get();

            $totalPercentage = 0;
            $totalWeight = 0;

            foreach ($gradebookItems as $item) {
                $grade = $studentGrades->firstWhere('gradebook_item_id', $item->id);
                if ($grade && $grade->percentage !== null) {
                    $totalPercentage += $grade->percentage * $item->weight;
                    $totalWeight += $item->weight;
                }
            }

            if ($totalWeight > 0) {
                $totalCount++;
                $finalPercentage = $totalPercentage / $totalWeight;
                if ($finalPercentage >= 70) {
                    $passCount++;
                }
            }
        }

        return $totalCount > 0 ? round(($passCount / $totalCount) * 100, 2) : null;
    }

    private function calculateScoreDistribution($gradebookItems, $studentIds)
    {
        $buckets = ['0-49' => 0, '50-69' => 0, '70-89' => 0, '90-100' => 0];

        foreach ($studentIds as $studentId) {
            $studentGrades = Grade::where('user_id', $studentId)
                ->whereIn('gradebook_item_id', $gradebookItems->pluck('id'))
                ->get();

            $totalPercentage = 0;
            $totalWeight = 0;

            foreach ($gradebookItems as $item) {
                $grade = $studentGrades->firstWhere('gradebook_item_id', $item->id);
                if ($grade && $grade->percentage !== null) {
                    $totalPercentage += $grade->percentage * $item->weight;
                    $totalWeight += $item->weight;
                }
            }

            if ($totalWeight > 0) {
                $avg = $totalPercentage / $totalWeight;
                if ($avg < 50) $buckets['0-49']++;
                elseif ($avg < 70) $buckets['50-69']++;
                elseif ($avg < 90) $buckets['70-89']++;
                else $buckets['90-100']++;
            }
        }

        return $buckets;
    }

    private function buildAssessmentPerformance($gradebookItems, $quizzes, $assignments)
    {
        $performance = [];

        foreach ($gradebookItems as $item) {
            $scores = $item->grades->pluck('percentage')->filter();
            $avgScore = $scores->count() > 0 ? round($scores->avg(), 2) : null;
            $passCount = $scores->filter(fn($s) => $s >= 70)->count();
            $passingRate = $scores->count() > 0 ? round(($passCount / $scores->count()) * 100, 2) : null;

            $submissionCount = null;
            if ($item->type === 'quiz') {
                $quiz = $quizzes->firstWhere('id', $item->item_id);
                $submissionCount = $quiz ? $quiz->attempts->where('status', 'completed')->count() : 0;
            } elseif ($item->type === 'assignment') {
                $assignment = $assignments->firstWhere('id', $item->item_id);
                $submissionCount = $assignment ? $assignment->submissions->count() : 0;
            }

            $performance[] = [
                'title' => $item->title,
                'type' => $item->type,
                'avg_score' => $avgScore,
                'max_score' => $item->max_score,
                'passing_rate' => $passingRate,
                'submission_count' => $submissionCount,
                'student_count' => $scores->count(),
            ];
        }

        return $performance;
    }

    private function buildStudentRankings($gradebookItems, $enrollments)
    {
        $rankings = [];

        foreach ($enrollments as $enrollment) {
            $grades = Grade::where('user_id', $enrollment->user_id)
                ->whereIn('gradebook_item_id', $gradebookItems->pluck('id'))
                ->get();

            $totalPercentage = 0;
            $totalWeight = 0;

            foreach ($gradebookItems as $item) {
                $grade = $grades->firstWhere('gradebook_item_id', $item->id);
                if ($grade && $grade->percentage !== null) {
                    $totalPercentage += $grade->percentage * $item->weight;
                    $totalWeight += $item->weight;
                }
            }

            if ($totalWeight > 0) {
                $rankings[] = [
                    'user' => $enrollment->user,
                    'avg_percentage' => round($totalPercentage / $totalWeight, 2),
                    'grade_count' => $grades->count(),
                ];
            }
        }

        usort($rankings, fn($a, $b) => $b['avg_percentage'] <=> $a['avg_percentage']);

        return $rankings;
    }

    public function exportCsv(Course $course)
    {
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $gradebookItems = GradebookItem::where('course_id', $course->id)->with('grades')->get();
        $enrollments = Enrollment::where('course_id', $course->id)->with('user')->get();
        $studentIds = $enrollments->pluck('user_id');

        $callback = function () use ($gradebookItems, $enrollments, $studentIds) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['Student', 'Email', 'Item', 'Type', 'Score', 'Max Score', 'Percentage', 'Grade']);

            foreach ($enrollments as $enrollment) {
                foreach ($gradebookItems as $item) {
                    $grade = $item->grades->firstWhere('user_id', $enrollment->user_id);
                    fputcsv($handle, [
                        $enrollment->user->name,
                        $enrollment->user->email,
                        $item->title,
                        $item->type,
                        $grade ? $grade->score : '',
                        $item->max_score,
                        $grade ? $grade->percentage : '',
                        $grade ? $grade->letter_grade : '',
                    ]);
                }
            }

            fclose($handle);
        };

        $filename = 'assessment-analytics-' . Str::slug($course->title) . '-' . now()->format('Y-m-d') . '.csv';

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function calculateCompletionRate($quizzes, $assignments, $studentIds)
    {
        $totalItems = $quizzes->count() + $assignments->count();
        if ($totalItems === 0 || $studentIds->count() === 0) return null;

        $totalExpected = $totalItems * $studentIds->count();
        $totalCompleted = 0;

        foreach ($quizzes as $quiz) {
            $totalCompleted += $quiz->attempts()
                ->whereIn('user_id', $studentIds)
                ->where('status', 'completed')
                ->distinct('user_id')
                ->count('user_id');
        }

        foreach ($assignments as $assignment) {
            $totalCompleted += $assignment->submissions()
                ->whereIn('user_id', $studentIds)
                ->count();
        }

        return $totalExpected > 0 ? round(($totalCompleted / $totalExpected) * 100, 2) : 0;
    }
}
