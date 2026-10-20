<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleAttachment;
use App\Models\ArticleReview;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\KnowledgeSpace;
use App\Models\Skill;
use App\Models\SkillEndorsement;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KmsUatEndToEndSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@tenant-a.kitsuluh.test')->first() ?? User::first();
        if (!$admin) return;

        $tenantId = $admin->tenant_id;
        $instructor = User::where('email', 'instructor@tenant-a.kitsuluh.test')->first() ?? $admin;
        $student1 = User::where('email', 'student1@tenant-a.kitsuluh.test')->first() ?? $admin;
        $student2 = User::where('email', 'student2@tenant-a.kitsuluh.test')->first() ?? $admin;

        // 1. KNOWLEDGE SPACES (Confluence-style)
        $spaceOps = KnowledgeSpace::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'operasional-sop-lapangan'],
            [
                'name' => 'Operasional & SOP Lapangan',
                'description' => 'Repositori Standar Operasional Prosedur, instruksi kerja, dan panduan teknis.',
                'icon' => 'mdi-file-document-multiple-outline',
                'color' => '#059669',
                'visibility' => 'internal',
                'created_by' => $admin->id,
                'manager_id' => $instructor->id,
            ]
        );

        $spaceIt = KnowledgeSpace::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'teknologi-infrastruktur-it'],
            [
                'name' => 'Teknologi & Infrastruktur IT',
                'description' => 'Dokumentasi arsitektur sistem, failover server, dan panduan IT security.',
                'icon' => 'mdi-server-network',
                'color' => '#2563EB',
                'visibility' => 'internal',
                'created_by' => $admin->id,
                'manager_id' => $admin->id,
            ]
        );

        $spaceHr = KnowledgeSpace::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'people-organization-policy'],
            [
                'name' => 'People & Organization Policy',
                'description' => 'Regulasi SDM, pedoman kepatuhan, dan etika kerja korporat.',
                'icon' => 'mdi-account-group-outline',
                'color' => '#7C3AED',
                'visibility' => 'internal',
                'created_by' => $admin->id,
                'manager_id' => $admin->id,
            ]
        );

        // 2. SOP & NESTED HIERARCHY
        // Parent SOP
        $parentSop = Article::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'sop-keselamatan-k3-lapangan-2026'],
            [
                'space_id' => $spaceOps->id,
                'parent_id' => null,
                'order' => 1,
                'title' => 'SOP Induk: Keselamatan Kerja & K3 Operasional',
                'content' => '<h1>SOP Induk K3</h1><p>Pedoman umum kepatuhan keselamatan kerja di seluruh unit operasional enterprise.</p>',
                'status' => 'published',
                'version' => '2.0',
                'author_id' => $instructor->id,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subDays(10),
                'review_cycle_days' => 180,
                'next_review_at' => now()->addDays(170), // Fresh/Healthy
                'published_at' => now()->subDays(10),
            ]
        );

        // Sub SOP (Child)
        $childSop = Article::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'instruksi-penanganan-limbah-b3'],
            [
                'space_id' => $spaceOps->id,
                'parent_id' => $parentSop->id,
                'order' => 1,
                'title' => 'Instruksi Kerja: Penanganan Cepat Tumpahan Bahan B3',
                'content' => '<p>Langkah isolasi area tumpahan bahan kimia berbahaya dan penggunaan spill kit.</p>',
                'status' => 'published',
                'version' => '1.1',
                'author_id' => $instructor->id,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subDays(5),
                'review_cycle_days' => 90,
                'next_review_at' => now()->addDays(85), // Healthy
                'published_at' => now()->subDays(5),
            ]
        );

        // Expired SOP (Needs Urgent Review in Analytics)
        $expiredSop = Article::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'kebijakan-cuti-operasional-2025'],
            [
                'space_id' => $spaceHr->id,
                'parent_id' => null,
                'order' => 1,
                'title' => 'Kebijakan Pengajuan Cuti & Lembur Periode 2025',
                'content' => '<p>Ketentuan pengajuan cuti tahunan dan kompensasi lembur lapangan lama.</p>',
                'status' => 'published',
                'version' => '1.0',
                'author_id' => $admin->id,
                'review_cycle_days' => 180,
                'next_review_at' => now()->subDays(15), // EXPIRED (Kadaluarsa 15 hari)
                'published_at' => now()->subMonths(7),
            ]
        );

        // Expiring Soon SOP (< 14 days)
        $expiringSoonSop = Article::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'sop-rotasi-shift-dan-absensi'],
            [
                'space_id' => $spaceHr->id,
                'parent_id' => null,
                'order' => 2,
                'title' => 'SOP Rotasi Shift Kerja & Validasi Geotagging',
                'content' => '<p>Aturan jam pergantian shift pagi-malam serta mekanisme toleransi keterlambatan.</p>',
                'status' => 'published',
                'version' => '1.2',
                'author_id' => $admin->id,
                'review_cycle_days' => 90,
                'next_review_at' => now()->addDays(5), // EXPIRING SOON in 5 days
                'published_at' => now()->subMonths(3),
            ]
        );

        // Pending Review Article
        $pendingSop = Article::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'pedoman-pemanfaatan-ai-enterprise'],
            [
                'space_id' => $spaceIt->id,
                'parent_id' => null,
                'order' => 2,
                'title' => 'Pedoman Kepatuhan Pemanfaatan AI Generatif di Lingkungan Kerja',
                'content' => '<p>Draf tata tertib larangan memasukkan data rahasia/PII ke dalam LLM publik.</p>',
                'status' => 'pending_review',
                'version' => '1.0',
                'author_id' => $student1->id,
                'review_cycle_days' => 90,
            ]
        );

        // SOP with Searchable Deep Attachment
        $itSop = Article::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'sop-manajemen-klaster-failover-database'],
            [
                'space_id' => $spaceIt->id,
                'parent_id' => null,
                'order' => 1,
                'title' => 'SOP Pemulihan Bencana & Failover Klaster Database',
                'content' => '<p>Protokol failover database utama ketika node primary mengalami crash.</p>',
                'status' => 'published',
                'version' => '2.5',
                'author_id' => $admin->id,
                'review_cycle_days' => 365,
                'next_review_at' => now()->addDays(300),
                'published_at' => now()->subMonth(),
            ]
        );

        // Create attachment with Deep Searchable Text
        $attachment = ArticleAttachment::updateOrCreate(
            ['article_id' => $itSop->id, 'title' => 'Runbook-Database-Recovery-2026.pdf'],
            [
                'tenant_id' => $tenantId,
                'file_path' => 'articles/attachments/dummy-runbook.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 2048576,
                'extracted_text' => "KLASTER_FAILOVER_POSTGRESQL_HOT_STANDBY panduan switchover darurat pg_auto_failover vip-switchover-command node-secondary-jakarta.",
            ]
        );

        // Update search_vector via PostgreSQL trigger / manual update for safety
        DB::statement("UPDATE article_attachments SET search_vector = to_tsvector('indonesian', coalesce(extracted_text, '') || ' ' || coalesce(title, '')) WHERE id = ?", [$attachment->id]);

        // Review Audit Log
        ArticleReview::firstOrCreate(
            ['article_id' => $parentSop->id, 'reviewer_id' => $admin->id, 'version' => '2.0'],
            [
                'status' => 'approved',
                'notes' => 'Disetujui untuk implementasi Q1 2026. Kepatuhan audit K3 terpenuhi.',
                'created_at' => now()->subDays(10),
            ]
        );

        // 3. SME SKILLS & ENDORSEMENTS
        $skill1 = Skill::firstOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'postgresql-ha-clustering'],
            ['name' => 'PostgreSQL High Availability & Clustering', 'category' => 'Infrastruktur IT']
        );
        $skill2 = Skill::firstOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'investigasi-insiden-k3'],
            ['name' => 'Investigasi & Audit Insiden K3', 'category' => 'K3 & Kepatuhan']
        );
        $skill3 = Skill::firstOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'manajemen-perubahan-organisasi'],
            ['name' => 'Manajemen Perubahan & Komunikasi SDM', 'category' => 'Human Resources']
        );

        // Assign User Skills
        $userSkillAdmin = UserSkill::updateOrCreate(
            ['user_id' => $admin->id, 'skill_id' => $skill1->id],
            [
                'proficiency_level' => 'expert',
                'endorsements_count' => 8,
                'is_verified' => true,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]
        );

        $userSkillInstructor = UserSkill::updateOrCreate(
            ['user_id' => $instructor->id, 'skill_id' => $skill2->id],
            [
                'proficiency_level' => 'expert',
                'endorsements_count' => 12,
                'is_verified' => true,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]
        );

        $userSkillStudent = UserSkill::updateOrCreate(
            ['user_id' => $student1->id, 'skill_id' => $skill3->id],
            [
                'proficiency_level' => 'intermediate',
                'endorsements_count' => 2,
                'is_verified' => false,
            ]
        );

        // Seed peer endorsements
        SkillEndorsement::firstOrCreate(
            ['user_skill_id' => $userSkillAdmin->id, 'endorsed_by' => $instructor->id],
            ['notes' => 'Sangat menguasai konfigurasi pg_auto_failover dan recovery drill.']
        );
        SkillEndorsement::firstOrCreate(
            ['user_skill_id' => $userSkillInstructor->id, 'endorsed_by' => $admin->id],
            ['notes' => 'Sertifikasi K3 Umum lengkap dan berpengalaman memimpin investigasi.']
        );

        // 4. PROBLEM SOLVING HUB (Discussions & Solved Q&A)
        // Solved Thread
        $solvedDiscussion = Discussion::updateOrCreate(
            ['tenant_id' => $tenantId, 'title' => 'Bagaimana mengatasi error 504 Gateway Timeout pada load balancer Nginx?'],
            [
                'created_by' => $student1->id,
                'content' => 'Ketika menjalankan query reporting data bulanan, koneksi selalu putus dengan kode 504 Gateway Timeout setelah tepat 60 detik.',
                'is_resolved' => true,
                'is_pinned' => true,
            ]
        );

        $solutionReply = DiscussionReply::updateOrCreate(
            ['discussion_id' => $solvedDiscussion->id, 'created_by' => $admin->id],
            [
                'content' => 'Solusinya adalah memperpanjang timeout proxy pada file konfigurasi Nginx. Ubah: proxy_read_timeout 300s; dan proxy_connect_timeout 300s; lalu reload nginx dengan: nginx -s reload.',
                'is_solution' => true,
                'verified_at' => now(),
                'verified_by' => $student1->id,
            ]
        );

        $solvedDiscussion->update(['accepted_reply_id' => $solutionReply->id]);

        // Unresolved High-Activity Thread (Simulates CONTENT GAP)
        $gapDiscussion = Discussion::updateOrCreate(
            ['tenant_id' => $tenantId, 'title' => 'Kendala kalibrasi sensor tekanan hidrostatik pada tangki penampungan utama'],
            [
                'created_by' => $student2->id,
                'content' => 'Sensor sering menunjukkan nilai fluktuatif hingga selisih 1.5 bar saat suhu tangki naik di atas 38 derajat Celcius. Apakah ada SOP kalibrasinya?',
                'is_resolved' => false,
                'is_pinned' => false,
            ]
        );

        // 3 replies without marked solution (Content Gap)
        DiscussionReply::firstOrCreate(
            ['discussion_id' => $gapDiscussion->id, 'created_by' => $student1->id],
            ['content' => 'Kami di unit 2 juga mengalami kendala yang sama kemarin siang.']
        );
        DiscussionReply::firstOrCreate(
            ['discussion_id' => $gapDiscussion->id, 'created_by' => $instructor->id],
            ['content' => 'Bisa dicek seal karet pada transmitter sensor, kemungkinan ada kebocoran mikro.']
        );
        DiscussionReply::firstOrCreate(
            ['discussion_id' => $gapDiscussion->id, 'created_by' => $admin->id],
            ['content' => 'Belum ada SOP resmi untuk kalibrasi unit baru ini. Perlu dibuatkan panduan standarnya.']
        );
    }
}
