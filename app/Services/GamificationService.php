<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\UserGamification;
use App\Models\PointTransaction;
use App\Models\XpConfiguration;
use App\Models\LevelConfiguration;
use App\Models\GamificationSetting;
use Illuminate\Support\Facades\Cache;

class GamificationService
{
    private const DEFAULT_XP = [
        'lesson_completed' => 10,
        'quiz_passed' => 30,
        'quiz_perfect' => 50,
        'course_completed' => 100,
        'streak_bonus' => 20,
    ];

    private const DEFAULT_STREAK_MILESTONES = [7, 30, 60, 365];

    private const DEFAULT_LEVEL_FORMULA_BASE = 100;

    public function awardPoints(User $user, string $action, ?int $courseId = null): void
    {
        $points = $this->getXpForAction($user->tenant_id, $action);

        if ($points <= 0) return;

        PointTransaction::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'course_id' => $courseId,
            'action' => $action,
            'points' => $points,
        ]);

        $gamification = UserGamification::firstOrCreate(
            ['user_id' => $user->id],
            ['total_xp' => 0, 'level' => 1, 'current_streak' => 0, 'longest_streak' => 0]
        );

        $gamification->increment('total_xp', $points);
        $gamification->level = $this->calculateLevel($user->tenant_id, $gamification->total_xp);
        $gamification->save();
    }

    public function updateStreak(User $user): void
    {
        $gamification = UserGamification::firstOrCreate(
            ['user_id' => $user->id],
            ['total_xp' => 0, 'level' => 1, 'current_streak' => 0, 'longest_streak' => 0]
        );

        $today = now()->startOfDay();
        $lastActivity = $gamification->last_activity_date;

        if (!$lastActivity) {
            $gamification->current_streak = 1;
            $gamification->longest_streak = 1;
        } elseif ($lastActivity->copy()->addDay()->startOfDay()->eq($today)) {
            $gamification->increment('current_streak');
            if ($gamification->current_streak > $gamification->longest_streak) {
                $gamification->longest_streak = $gamification->current_streak;
            }
            if (in_array($gamification->current_streak, $this->getStreakMilestones($user->tenant_id))) {
                $this->awardPoints($user, 'streak_bonus');
                $this->checkBadges($user);
            }
        } elseif (!$lastActivity->startOfDay()->eq($today)) {
            $gamification->current_streak = 1;
        }

        $gamification->last_activity_date = $today;
        $gamification->save();
    }

    public function checkBadges(User $user): void
    {
        $tenantBadges = Badge::where('tenant_id', $user->tenant_id)
            ->where('active', true)
            ->get();

        foreach ($tenantBadges as $badge) {
            if ($user->badges()->where('badge_id', $badge->id)->exists()) continue;

            $earned = match ($badge->criteria_type) {
                'lesson_complete' => $user->progresses()->where('completed', true)->count() >= $badge->criteria_value,
                'course_complete' => $user->certificates()->count() >= $badge->criteria_value,
                'quiz_perfect_score' => $user->quizAttempts()->where('score', 100)->count() >= $badge->criteria_value,
                'points_milestone' => UserGamification::where('user_id', $user->id)->value('total_xp') >= $badge->criteria_value,
                'consecutive_days' => UserGamification::where('user_id', $user->id)->value('current_streak') >= $badge->criteria_value,
                default => false,
            };

            if ($earned) {
                $user->badges()->attach($badge->id, ['earned_at' => now()]);
            }
        }
    }

    public function calculateLevel(?string $tenantId, int $totalXp): int
    {
        $levels = LevelConfiguration::where('tenant_id', $tenantId)
            ->orWhereNull('tenant_id')
            ->orderByDesc('level')
            ->get();

        if ($levels->isNotEmpty()) {
            foreach ($levels as $level) {
                if ($totalXp >= $level->xp_required) {
                    return $level->level;
                }
            }
            return 1;
        }

        $base = (int) GamificationSetting::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->where('key', 'level_formula_base')->value('value') ?: self::DEFAULT_LEVEL_FORMULA_BASE;

        return max(1, (int) floor(sqrt($totalXp / $base)) + 1);
    }

    public function getLeaderboard(?int $courseId = null, string $period = 'all', int $limit = 20): array
    {
        $query = UserGamification::with('user')
            ->whereHas('user', fn($q) => $q->where('tenant_id', auth()->user()->tenant_id));

        if ($courseId) {
            $query->whereHas('user.enrollments', fn($q) => $q->where('course_id', $courseId));
        }

        if ($period === 'weekly') {
            $query->whereHas('user.pointTransactions', fn($q) => $q->where('created_at', '>=', now()->subWeek()));
        } elseif ($period === 'monthly') {
            $query->whereHas('user.pointTransactions', fn($q) => $q->where('created_at', '>=', now()->subMonth()));
        }

        return $query->orderByDesc('total_xp')
            ->take($limit)
            ->get()
            ->toArray();
    }

    public function getXpForAction(?string $tenantId, string $action): int
    {
        static $cache = [];

        $cacheKey = $tenantId ?? '_global';

        if (!isset($cache[$cacheKey])) {
            $configs = Cache::remember("xp_config_{$cacheKey}", 3600, function () use ($tenantId) {
                return XpConfiguration::where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                })->pluck('points', 'action')->toArray();
            });
            $cache[$cacheKey] = $configs;
        }

        return $cache[$cacheKey][$action] ?? self::DEFAULT_XP[$action] ?? 0;
    }

    public function getStreakMilestones(?string $tenantId): array
    {
        $value = GamificationSetting::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->where('key', 'streak_milestones')->value('value');

        if ($value) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) return $decoded;
        }

        return self::DEFAULT_STREAK_MILESTONES;
    }

    public function getAllXpConfigs(?string $tenantId): array
    {
        $actions = array_keys(self::DEFAULT_XP);
        $configs = [];

        $dbConfigs = XpConfiguration::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->get()->keyBy('action');

        foreach ($actions as $action) {
            $configs[$action] = [
                'points' => $dbConfigs->has($action) ? $dbConfigs->get($action)->points : self::DEFAULT_XP[$action],
                'default' => self::DEFAULT_XP[$action],
                'from_db' => $dbConfigs->has($action),
            ];
        }

        return $configs;
    }

    public function getAllLevelConfigs(?string $tenantId): array
    {
        return LevelConfiguration::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->orderBy('level')->get()->toArray();
    }
}
