<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\KnowledgeSpace;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Database\Seeder;

class KmsEnterpriseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) return;

        $tenantId = $admin->tenant_id;

        // 1. Seed Knowledge Spaces (Confluence-style Spaces)
        $spaces = [
            [
                'name' => 'Operasional & SOP Lapangan',
                'slug' => 'operasional-sop-lapangan',
                'description' => 'Pusat repositori Standar Operasional Prosedur, instruksi kerja, dan panduan teknis lapangan.',
                'icon' => 'mdi-file-document-multiple-outline',
                'color' => '#059669', // Emerald
                'visibility' => 'internal',
                'created_by' => $admin->id,
                'manager_id' => $admin->id,
            ],
            [
                'name' => 'Teknologi & Infrastruktur IT',
                'slug' => 'teknologi-infrastruktur-it',
                'description' => 'Dokumentasi arsitektur sistem, troubleshooting aplikasi internal, dan panduan keamanan siber.',
                'icon' => 'mdi-server-network',
                'color' => '#2563EB', // Blue
                'visibility' => 'internal',
                'created_by' => $admin->id,
                'manager_id' => $admin->id,
            ],
            [
                'name' => 'People & Organization Policy',
                'slug' => 'people-organization-policy',
                'description' => 'Regulasi SDM, kebijakan kehadiran, benefit karyawan, dan pedoman kepatuhan etika perusahaan.',
                'icon' => 'mdi-account-group-outline',
                'color' => '#7C3AED', // Purple
                'visibility' => 'internal',
                'created_by' => $admin->id,
                'manager_id' => $admin->id,
            ],
        ];

        $spaceModels = [];
        foreach ($spaces as $spaceData) {
            $spaceModels[$spaceData['slug']] = KnowledgeSpace::firstOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $spaceData['slug']],
                $spaceData
            );
        }

        // Link existing articles without space to the first space
        Article::whereNull('space_id')->update([
            'space_id' => $spaceModels['operasional-sop-lapangan']->id,
            'review_cycle_days' => 180,
            'next_review_at' => now()->addDays(180),
        ]);

        // 2. Seed Skills Catalog
        $skills = [
            ['name' => 'Agronomi & Budidaya Padi', 'slug' => 'agronomi-budidaya-padi', 'category' => 'Agronomi'],
            ['name' => 'Pengendalian Hama & Penyakit (PHT)', 'slug' => 'pht-wereng-blas', 'category' => 'Agronomi'],
            ['name' => 'Kalibrasi & Perawatan Alat Semprot', 'slug' => 'kalibrasi-alat-semprot', 'category' => 'Mekanisasi'],
            ['name' => 'Troubleshooting Jaringan & VPN', 'slug' => 'troubleshooting-jaringan', 'category' => 'Teknologi IT'],
            ['name' => 'PostgreSQL Database Administration', 'slug' => 'postgresql-admin', 'category' => 'Teknologi IT'],
            ['name' => 'SOP K3 & Keselamatan Lapangan', 'slug' => 'sop-k3-keselamatan', 'category' => 'K3 & Regulasi'],
        ];

        foreach ($skills as $skillData) {
            $skill = Skill::firstOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $skillData['slug']],
                $skillData
            );

            // Assign first skill to admin as an Expert
            UserSkill::firstOrCreate(
                ['user_id' => $admin->id, 'skill_id' => $skill->id],
                [
                    'proficiency_level' => 'expert',
                    'endorsements_count' => 5,
                    'is_verified' => true,
                    'verified_by' => $admin->id,
                    'verified_at' => now(),
                ]
            );
        }
    }
}
