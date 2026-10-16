<?php

namespace App\Http\Controllers;

use App\Models\UserGamification;
use App\Models\PointTransaction;
use App\Models\Badge;
use Illuminate\Http\Request;

class StudentGamificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $gamification = UserGamification::firstOrCreate(
            ['user_id' => $user->id],
            ['total_xp' => 0, 'level' => 1, 'current_streak' => 0, 'longest_streak' => 0]
        );

        $badges = $user->badges()->withPivot('earned_at')->latest('earned_at')->get();

        $allBadges = Badge::where('tenant_id', $user->tenant_id)
            ->where('active', true)
            ->get()
            ->map(function ($badge) use ($user) {
                $earned = $user->badges()->where('badge_id', $badge->id)->first();

                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'slug' => $badge->slug,
                    'description' => $badge->description,
                    'image_url' => $badge->image_url,
                    'criteria_type' => $badge->criteria_type,
                    'criteria_value' => $badge->criteria_value,
                    'is_earned' => (bool) $earned,
                    'earned_at' => $earned?->pivot?->earned_at,
                ];
            });

        $recentPoints = PointTransaction::where('user_id', $user->id)
            ->with('course')
            ->latest()
            ->take(20)
            ->get();

        $totalPoints = (int) PointTransaction::where('user_id', $user->id)->sum('points');

        // Level math
        $currentLevel = max(1, (int) $gamification->level);
        $currentLevelXp = $currentLevel > 1 ? (pow($currentLevel - 1, 2) * 100) : 0;
        $nextLevelXp = (pow($currentLevel, 2) * 100);
        $levelSpan = max(1, $nextLevelXp - $currentLevelXp);
        $progressInLevel = min(100, max(0, round((($gamification->total_xp - $currentLevelXp) / $levelSpan) * 100)));
        $xpRemaining = max(0, $nextLevelXp - $gamification->total_xp);

        // Platform rank
        $userRank = UserGamification::whereHas('user', fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->where('total_xp', '>', $gamification->total_xp)
            ->count() + 1;

        $streakMilestones = [7, 14, 30, 60, 100];

        return inertia('Student/Gamification/Index', [
            'gamification' => $gamification,
            'badges' => $badges,
            'allBadges' => $allBadges,
            'recentPoints' => $recentPoints,
            'totalPoints' => $totalPoints ?: (int) $gamification->total_xp,
            'currentLevelXp' => (int) $currentLevelXp,
            'nextLevelXp' => (int) $nextLevelXp,
            'xpRemaining' => (int) $xpRemaining,
            'progressInLevel' => (int) $progressInLevel,
            'userRank' => (int) $userRank,
            'streakMilestones' => $streakMilestones,
        ]);
    }
}
