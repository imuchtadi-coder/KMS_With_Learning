<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use App\Models\LearningPathEnrollment;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class LearningPathAnalyticsController extends Controller
{
    /**
     * Show analytics dashboard for tenant admin
     */
    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        // Fetch analytics data
        $analytics = [
            'overview' => $this->getOverviewMetrics($tenantId),
            'topPaths' => $this->getTopPerformingPaths($tenantId),
            'pathCompletionRates' => $this->getPathCompletionRates($tenantId),
            'enrollmentTrends' => $this->getEnrollmentTrends($tenantId),
            'studentProgressDistribution' => $this->getStudentProgressDistribution($tenantId),
            'coursePerformance' => $this->getCoursePerformanceMetrics($tenantId),
        ];

        return Inertia::render('Admin/LearningPaths/Analytics/Dashboard', compact('analytics'));
    }

    /**
     * Get overview metrics
     */
    private function getOverviewMetrics($tenantId)
    {
        return [
            'total_paths' => LearningPath::where('tenant_id', $tenantId)->count(),
            'total_enrollments' => LearningPathEnrollment::whereHas('learningPath', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->count(),
            'completed_enrollments' => LearningPathEnrollment::whereHas('learningPath', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->whereNotNull('completed_at')->count(),
            'active_enrollments' => LearningPathEnrollment::whereHas('learningPath', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->whereNull('completed_at')->count(),
            'average_completion_rate' => $this->calculateAverageCompletionRate($tenantId),
            'average_enrollment_duration' => $this->calculateAverageEnrollmentDuration($tenantId),
        ];
    }

    /**
     * Get top performing learning paths
     */
    private function getTopPerformingPaths($tenantId, $limit = 5)
    {
        return LearningPath::where('tenant_id', $tenantId)
            ->withCount(['enrollments'])
            ->withCount(['enrollments as completed_count' => function ($q) {
                $q->whereNotNull('completed_at');
            }])
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get()
            ->map(function ($path) {
                return [
                    'id' => $path->id,
                    'title' => $path->title,
                    'enrollments' => $path->enrollments_count,
                    'completed' => $path->completed_count,
                    'completion_rate' => $path->enrollments_count > 0
                        ? round(($path->completed_count / $path->enrollments_count) * 100, 2)
                        : 0,
                ];
            });
    }

    /**
     * Get completion rates for all paths
     */
    private function getPathCompletionRates($tenantId)
    {
        $paths = LearningPath::where('tenant_id', $tenantId)->get();

        return $paths->map(function ($path) {
            $total = $path->enrollments()->count();
            $completed = $path->enrollments()->whereNotNull('completed_at')->count();

            return [
                'path_id' => $path->id,
                'title' => $path->title,
                'total_enrollments' => $total,
                'completed_enrollments' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                'in_progress' => $total - $completed,
            ];
        })->sortByDesc('completion_rate');
    }

    /**
     * Get enrollment trends over time
     */
    private function getEnrollmentTrends($tenantId, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);

        $enrollments = LearningPathEnrollment::whereHas('learningPath', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
            ->where('started_at', '>=', $startDate)
            ->selectRaw('DATE(started_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupBy(DB::raw('DATE(started_at)'))
            ->orderBy('date')
            ->get();

        $completions = LearningPathEnrollment::whereHas('learningPath', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
            ->where('completed_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->selectRaw('DATE(completed_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupBy(DB::raw('DATE(completed_at)'))
            ->orderBy('date')
            ->get();

        return [
            'enrollments' => $enrollments->toArray(),
            'completions' => $completions->toArray(),
            'period_days' => $days,
        ];
    }

    /**
     * Get distribution of student progress across all paths
     */
    private function getStudentProgressDistribution($tenantId)
    {
        $enrollments = LearningPathEnrollment::whereHas('learningPath', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->with('progress')->get();

        $distribution = [
            'not_started' => 0,
            'in_progress_0_25' => 0,
            'in_progress_25_50' => 0,
            'in_progress_50_75' => 0,
            'in_progress_75_100' => 0,
            'completed' => 0,
        ];

        foreach ($enrollments as $enrollment) {
            if ($enrollment->completed_at) {
                $distribution['completed']++;
            } else {
                $progress = $enrollment->getProgress();
                if ($progress == 0) {
                    $distribution['not_started']++;
                } elseif ($progress <= 25) {
                    $distribution['in_progress_0_25']++;
                } elseif ($progress <= 50) {
                    $distribution['in_progress_25_50']++;
                } elseif ($progress <= 75) {
                    $distribution['in_progress_50_75']++;
                } else {
                    $distribution['in_progress_75_100']++;
                }
            }
        }

        return $distribution;
    }

    /**
     * Get course performance within paths
     */
    private function getCoursePerformanceMetrics($tenantId)
    {
        $courses = Course::where('tenant_id', $tenantId)
            ->whereHas('learningPaths')
            ->withCount('learningPaths')
            ->get();

        return $courses->map(function ($course) {
            $totalProgress = DB::table('learning_path_progress')
                ->where('course_id', $course->id)
                ->count();
            $completedProgress = DB::table('learning_path_progress')
                ->where('course_id', $course->id)
                ->whereNotNull('completed_at')
                ->count();

            return [
                'course_id' => $course->id,
                'title' => $course->title,
                'times_in_paths' => $course->learning_paths_count,
                'total_progress_entries' => $totalProgress,
                'completed_entries' => $completedProgress,
                'completion_rate' => $totalProgress > 0 ? round(($completedProgress / $totalProgress) * 100, 2) : 0,
            ];
        })->sortByDesc('completion_rate');
    }

    /**
     * Helper: Calculate average completion rate
     */
    private function calculateAverageCompletionRate($tenantId)
    {
        $paths = LearningPath::where('tenant_id', $tenantId)->get();
        if ($paths->isEmpty()) {
            return 0;
        }

        $rates = $paths->map(function ($path) {
            $total = $path->enrollments()->count();
            $completed = $path->enrollments()->whereNotNull('completed_at')->count();
            return $total > 0 ? ($completed / $total) * 100 : 0;
        });

        return round($rates->avg(), 2);
    }

    /**
     * Helper: Calculate average enrollment duration
     */
    private function calculateAverageEnrollmentDuration($tenantId)
    {
        $completedEnrollments = LearningPathEnrollment::whereHas('learningPath', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
            ->whereNotNull('completed_at')
            ->get();

        if ($completedEnrollments->isEmpty()) {
            return 0;
        }

        $totalDays = $completedEnrollments->sum(function ($enrollment) {
            return $enrollment->completed_at->diffInDays($enrollment->started_at);
        });

        return round($totalDays / $completedEnrollments->count(), 1);
    }

    /**
     * Get analytics for a specific path
     */
    public function pathAnalytics($pathId)
    {
        $tenantId = auth()->user()->tenant_id;
        $path = LearningPath::where('tenant_id', $tenantId)->findOrFail($pathId);

        $analytics = [
            'path' => $path,
            'overview' => [
                'total_enrollments' => $path->enrollments()->count(),
                'completed' => $path->enrollments()->whereNotNull('completed_at')->count(),
                'in_progress' => $path->enrollments()->whereNull('completed_at')->count(),
                'completion_rate' => $this->getPathCompletionRate($path),
            ],
            'courseMetrics' => $this->getPathCourseMetrics($path),
            'studentProgress' => $path->enrollments()
                ->with('user', 'progress')
                ->paginate(20),
        ];

        return Inertia::render('Admin/LearningPaths/Analytics/PathDetails', compact('analytics'));
    }

    /**
     * Helper: Get specific path completion rate
     */
    private function getPathCompletionRate($path)
    {
        $total = $path->enrollments()->count();
        $completed = $path->enrollments()->whereNotNull('completed_at')->count();
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    /**
     * Helper: Get course metrics for a path
     */
    private function getPathCourseMetrics($path)
    {
        return $path->courses()->orderBy('pivot_order')->get()->map(function ($course) {
            $totalProgress = DB::table('learning_path_progress')
                ->where('learning_path_id', $path->id)
                ->where('course_id', $course->id)
                ->count();
            $completedProgress = DB::table('learning_path_progress')
                ->where('learning_path_id', $path->id)
                ->where('course_id', $course->id)
                ->whereNotNull('completed_at')
                ->count();

            return [
                'course_id' => $course->id,
                'title' => $course->title,
                'order' => $course->pivot->order,
                'total_students_reached' => $totalProgress,
                'total_students_completed' => $completedProgress,
                'completion_rate' => $totalProgress > 0 ? round(($completedProgress / $totalProgress) * 100, 2) : 0,
            ];
        });
    }
}
