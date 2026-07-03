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

        $recentPoints = PointTransaction::where('user_id', $user->id)
            ->with('course')
            ->latest()
            ->take(20)
            ->get();

        $totalPoints = PointTransaction::where('user_id', $user->id)->sum('points');

        $nextLevelXp = (pow($gamification->level, 2) * 100);
        $currentLevelXp = $gamification->level > 1 ? (pow($gamification->level - 1, 2) * 100) : 0;
        $progressInLevel = $nextLevelXp - $currentLevelXp > 0
            ? (($gamification->total_xp - $currentLevelXp) / ($nextLevelXp - $currentLevelXp)) * 100
            : 0;

        return inertia('Student/Gamification/Index', compact(
            'gamification', 'badges', 'recentPoints', 'totalPoints', 'nextLevelXp', 'progressInLevel'
        ));
    }
}
