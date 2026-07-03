<?php

namespace Database\Seeders;

use App\Models\XpConfiguration;
use App\Models\LevelConfiguration;
use App\Models\GamificationSetting;
use Illuminate\Database\Seeder;

class GamificationConfigSeeder extends Seeder
{
    public function run(): void
    {
        $xpDefaults = [
            'lesson_completed' => 10,
            'quiz_passed' => 30,
            'quiz_perfect' => 50,
            'course_completed' => 100,
            'streak_bonus' => 20,
        ];

        foreach ($xpDefaults as $action => $points) {
            XpConfiguration::firstOrCreate(
                ['action' => $action],
                ['points' => $points]
            );
        }

        $levels = [
            ['level' => 1, 'xp_required' => 0, 'title' => 'Beginner'],
            ['level' => 2, 'xp_required' => 100, 'title' => 'Bronze'],
            ['level' => 3, 'xp_required' => 400, 'title' => 'Silver'],
            ['level' => 4, 'xp_required' => 900, 'title' => 'Gold'],
            ['level' => 5, 'xp_required' => 1600, 'title' => 'Platinum'],
            ['level' => 6, 'xp_required' => 2500, 'title' => 'Diamond'],
            ['level' => 7, 'xp_required' => 3600, 'title' => 'Master'],
            ['level' => 8, 'xp_required' => 4900, 'title' => 'Grandmaster'],
            ['level' => 9, 'xp_required' => 6400, 'title' => 'Elite'],
            ['level' => 10, 'xp_required' => 8100, 'title' => 'Legend'],
        ];

        foreach ($levels as $level) {
            LevelConfiguration::firstOrCreate(
                ['level' => $level['level']],
                $level
            );
        }

        $settings = [
            'streak_milestones' => json_encode([7, 30, 60, 365]),
            'level_formula_base' => '100',
        ];

        foreach ($settings as $key => $value) {
            GamificationSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
