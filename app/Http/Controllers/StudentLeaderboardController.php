<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Illuminate\Http\Request;

class StudentLeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $service = app(GamificationService::class);

        $courseId = $request->input('course_id');
        $period = $request->input('period', 'all');

        $leaderboard = $service->getLeaderboard($courseId ? (int) $courseId : null, $period, 50);

        // Get user gamification data
        $userGamification = \App\Models\UserGamification::firstOrCreate(
            ['user_id' => $user->id],
            ['total_xp' => 0, 'level' => 1, 'current_streak' => 0, 'longest_streak' => 0]
        );

        // Find user's rank in current leaderboard
        $userRank = null;
        foreach ($leaderboard as $index => $item) {
            if (($item['user_id'] ?? null) === $user->id) {
                $userRank = $index + 1;
                break;
            }
        }

        // Available published courses in this tenant
        $courses = \App\Models\Course::where('published', true)
            ->where('tenant_id', $user->tenant_id)
            ->orderBy('title')
            ->select('id', 'title')
            ->get();

        return inertia('Student/Leaderboard/Index', [
            'leaderboard' => $leaderboard,
            'courseId' => $courseId ? (int) $courseId : null,
            'period' => $period,
            'userStats' => [
                'rank' => $userRank,
                'total_xp' => (int) $userGamification->total_xp,
                'level' => (int) $userGamification->level,
                'current_streak' => (int) $userGamification->current_streak,
                'longest_streak' => (int) $userGamification->longest_streak,
            ],
            'courses' => $courses,
        ]);
    }
}
