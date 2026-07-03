<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Illuminate\Http\Request;

class StudentLeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $service = app(GamificationService::class);

        $courseId = $request->input('course_id');
        $period = $request->input('period', 'all');

        $leaderboard = $service->getLeaderboard($courseId, $period);

        return inertia('Student/Leaderboard/Index', compact('leaderboard', 'courseId', 'period'));
    }
}
