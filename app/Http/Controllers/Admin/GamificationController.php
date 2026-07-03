<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\XpConfiguration;
use App\Models\LevelConfiguration;
use App\Models\GamificationSetting;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class GamificationController extends Controller
{
    public function index(GamificationService $gamification)
    {
        $user = auth()->user();

        return Inertia::render('Admin/Gamification/Index', [
            'xpConfigs' => $gamification->getAllXpConfigs($user->tenant_id),
            'levelConfigs' => $gamification->getAllLevelConfigs($user->tenant_id),
            'streakMilestones' => $gamification->getStreakMilestones($user->tenant_id),
        ]);
    }

    public function updateXp(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'configs' => ['required', 'array'],
            'configs.*.action' => ['required', 'string', 'in:lesson_completed,quiz_passed,quiz_perfect,course_completed,streak_bonus'],
            'configs.*.points' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        foreach ($validated['configs'] as $config) {
            XpConfiguration::updateOrCreate(
                [
                    'tenant_id' => $user->tenant_id,
                    'action' => $config['action'],
                ],
                ['points' => $config['points']]
            );
        }

        Cache::forget("xp_config_{$user->tenant_id}");

        return back()->with('success', 'XP configurations updated successfully!');
    }

    public function resetXp(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:lesson_completed,quiz_passed,quiz_perfect,course_completed,streak_bonus'],
        ]);

        XpConfiguration::where('tenant_id', $user->tenant_id)
            ->where('action', $validated['action'])
            ->delete();

        Cache::forget("xp_config_{$user->tenant_id}");

        return back()->with('success', 'XP configuration reset to default!');
    }

    public function updateLevels(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'levels' => ['required', 'array'],
            'levels.*.level' => ['required', 'integer', 'min:1'],
            'levels.*.xp_required' => ['required', 'integer', 'min:0'],
            'levels.*.title' => ['nullable', 'string', 'max:255'],
        ]);

        $existingLevels = LevelConfiguration::where('tenant_id', $user->tenant_id)
            ->pluck('id', 'level');

        $submittedLevels = collect($validated['levels']);

        foreach ($submittedLevels as $level) {
            LevelConfiguration::updateOrCreate(
                [
                    'tenant_id' => $user->tenant_id,
                    'level' => $level['level'],
                ],
                [
                    'xp_required' => $level['xp_required'],
                    'title' => $level['title'] ?? null,
                ]
            );
        }

        LevelConfiguration::where('tenant_id', $user->tenant_id)
            ->whereNotIn('level', $submittedLevels->pluck('level'))
            ->delete();

        return back()->with('success', 'Level configurations updated successfully!');
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'streak_milestones' => ['required', 'array'],
            'streak_milestones.*' => ['required', 'integer', 'min:1'],
        ]);

        GamificationSetting::updateOrCreate(
            ['tenant_id' => $user->tenant_id, 'key' => 'streak_milestones'],
            ['value' => json_encode($validated['streak_milestones'])]
        );

        return back()->with('success', 'Streak milestones updated successfully!');
    }

    public function resetSettings(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'key' => ['required', 'string', 'in:streak_milestones,level_formula_base'],
        ]);

        GamificationSetting::where('tenant_id', $user->tenant_id)
            ->where('key', $validated['key'])
            ->delete();

        return back()->with('success', 'Setting reset to default!');
    }
}
