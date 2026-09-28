<?php

namespace App\Services;

use App\Models\LearningPath;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class LearningPathRecommendationService
{
    /**
     * Get recommended paths for a student
     *
     * @param User $student
     * @param int $limit
     * @return Collection
     */
    public function getRecommendedPaths(User $student, $limit = 3)
    {
        $tenantId = $student->tenant_id;

        // Get all published paths not yet enrolled
        $availablePaths = LearningPath::where('tenant_id', $tenantId)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereDoesntHave('enrollments', function (Builder $q) use ($student) {
                $q->where('user_id', $student->id);
            })
            ->get();

        if ($availablePaths->isEmpty()) {
            return collect([]);
        }

        // Score each path
        $scoredPaths = $availablePaths->map(function ($path) use ($student) {
            return [
                'path' => $path,
                'score' => $this->scorePathForStudent($path, $student),
            ];
        });

        // Sort by score and get top recommendations
        return $scoredPaths
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('path')
            ->values();
    }

    /**
     * Score a path for a specific student
     *
     * @param LearningPath $path
     * @param User $student
     * @return float
     */
    private function scorePathForStudent(LearningPath $path, User $student)
    {
        $score = 0;

        // Factor 1: Skills match based on completed courses
        $score += $this->getSkillMatchScore($path, $student) * 30;

        // Factor 2: Difficulty progression
        $score += $this->getDifficultyProgressionScore($path, $student) * 25;

        // Factor 3: Popularity/High engagement
        $score += $this->getEngagementScore($path) * 20;

        // Factor 4: Learning style alignment
        $score += $this->getLearningStyleScore($path, $student) * 15;

        // Factor 5: Time availability
        $score += $this->getTimeAvailabilityScore($path, $student) * 10;

        return $score;
    }

    /**
     * Get skill match score (0-100)
     * Suggests paths that build on skills from completed courses
     */
    private function getSkillMatchScore(LearningPath $path, User $student)
    {
        $studentCompletedCourses = $student->completedCourses()->pluck('id')->toArray();

        if (empty($studentCompletedCourses)) {
            return 50; // Neutral score for beginners
        }

        $pathCourses = $path->courses()->pluck('course_id')->toArray();

        // Calculate overlap with completed courses
        $matchedCourses = count(array_intersect($studentCompletedCourses, $pathCourses));
        $totalPathCourses = count($pathCourses);

        // Paths with some but not all matching courses get higher score
        if ($matchedCourses > 0) {
            return min(100, 50 + ($matchedCourses / $totalPathCourses) * 50);
        }

        // Check for complementary skills
        return 30;
    }

    /**
     * Get difficulty progression score (0-100)
     * Suggests paths with difficulty slightly higher than completed paths
     */
    private function getDifficultyProgressionScore(LearningPath $path, User $student)
    {
        // Get average difficulty of student's completed paths
        $completedPaths = $student->learningPathEnrollments()
            ->whereNotNull('completed_at')
            ->with('learningPath')
            ->get()
            ->pluck('learningPath');

        if ($completedPaths->isEmpty()) {
            return 60; // Neutral for first path
        }

        $avgCompletedDifficulty = $completedPaths->avg(function ($path) {
            return $this->calculatePathDifficulty($path);
        });

        $pathDifficulty = $this->calculatePathDifficulty($path);

        // Ideal progression is +1 difficulty level
        $difficultyGap = abs($pathDifficulty - ($avgCompletedDifficulty + 1));

        return max(0, 100 - ($difficultyGap * 20));
    }

    /**
     * Calculate path difficulty score (1-5)
     */
    private function calculatePathDifficulty(LearningPath $path)
    {
        $courses = $path->courses;

        if ($courses->isEmpty()) {
            return 2; // Default medium
        }

        // Average course difficulty
        $avgDifficulty = $courses->avg(function ($course) {
            // Extract level from course (assumes difficulty_level or similar)
            return $course->difficulty_level ?? 2;
        });

        return round($avgDifficulty, 1);
    }

    /**
     * Get engagement score based on enrollment trends (0-100)
     */
    private function getEngagementScore(LearningPath $path)
    {
        $totalEnrollments = $path->enrollments()->count();

        if ($totalEnrollments === 0) {
            return 40; // Give some benefit to new paths
        }

        $completedEnrollments = $path->enrollments()->whereNotNull('completed_at')->count();
        $completionRate = ($completedEnrollments / $totalEnrollments) * 100;

        // Higher completion rate = higher engagement score
        return min(100, $completionRate + 20);
    }

    /**
     * Get learning style alignment score (0-100)
     */
    private function getLearningStyleScore(LearningPath $path, User $student)
    {
        // Check path content types
        $courses = $path->courses;

        if ($courses->isEmpty()) {
            return 50;
        }

        // Analyze student's past learning preferences
        $studentCompletedCourses = $student->completedCourses()->get();

        if ($studentCompletedCourses->isEmpty()) {
            return 50; // Neutral for new students
        }

        // Count content type preferences (e.g., video-heavy, project-based)
        $scoreCount = 0;
        $scoreSum = 0;

        foreach ($courses as $course) {
            foreach ($studentCompletedCourses as $completed) {
                // Similarity based on matching attributes
                if ($course->instructor_id === $completed->instructor_id) {
                    $scoreSum += 20; // Same instructor preference
                }
                // Could add more attributes like format, language, etc.
            }
            $scoreCount++;
        }

        return $scoreCount > 0 ? min(100, $scoreSum / $scoreCount) : 50;
    }

    /**
     * Get time availability score (0-100)
     * Suggests paths with duration matching student's typical learning pace
     */
    private function getTimeAvailabilityScore(LearningPath $path, User $student)
    {
        $totalDuration = $path->courses()->count() * 4;

        // Get student's average learning duration
        $completedPaths = $student->learningPathEnrollments()
            ->whereNotNull('completed_at')
            ->with('learningPath.courses')
            ->get();

        if ($completedPaths->isEmpty()) {
            // Default: suggest paths of 20-60 hours for beginners
            if ($totalDuration >= 20 && $totalDuration <= 60) {
                return 80;
            }
            return 50;
        }

        // Calculate average path duration
        $avgDuration = $completedPaths->avg(function ($enrollment) {
            return $enrollment->learningPath->courses()->count() * 4;
        });

        // Suggest paths within ±50% of their average
        $lowerBound = $avgDuration * 0.5;
        $upperBound = $avgDuration * 1.5;

        if ($totalDuration >= $lowerBound && $totalDuration <= $upperBound) {
            return 90;
        }

        // Penalize too short or too long paths
        if ($totalDuration < $lowerBound) {
            return 40;
        }

        return 30;
    }

    /**
     * Get personalized recommendations for dashboard
     */
    public function getDashboardRecommendations(User $student, $limit = 3)
    {
        $recommendations = $this->getRecommendedPaths($student, $limit);

        if ($recommendations->isEmpty()) {
            // If no recommendations, get recently published paths
            $recommendations = LearningPath::where('tenant_id', $student->tenant_id)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereDoesntHave('enrollments', function (Builder $q) use ($student) {
                    $q->where('user_id', $student->id);
                })
                ->latest('created_at')
                ->limit($limit)
                ->get();
        }

        return $recommendations->map(function ($path) {
            return [
                'path' => $path,
                'reason' => $this->getRecommendationReason($path),
                'enrollment_count' => $path->enrollments()->count(),
                'completion_rate' => $this->getPathCompletionRate($path),
            ];
        });
    }

    /**
     * Get human-readable reason for recommendation
     */
    private function getRecommendationReason(LearningPath $path)
    {
        $completionRate = $this->getPathCompletionRate($path);
        $enrollmentCount = $path->enrollments()->count();

        if ($completionRate >= 80) {
            return 'High completion rate (' . round($completionRate) . '%)';
        }

        if ($enrollmentCount >= 50) {
            return 'Popular path (' . $enrollmentCount . ' students)';
        }

        return 'Personalized recommendation';
    }

    /**
     * Helper: Get path completion rate
     */
    private function getPathCompletionRate(LearningPath $path)
    {
        $total = $path->enrollments()->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $path->enrollments()->whereNotNull('completed_at')->count();
        return ($completed / $total) * 100;
    }
}
