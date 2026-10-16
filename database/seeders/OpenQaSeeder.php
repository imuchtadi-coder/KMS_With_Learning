<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class OpenQaSeeder extends Seeder
{
    public function run(): void
    {
        $asker = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->first() ?? User::first();
        $expert = User::whereHas('roles', fn($q) => $q->whereIn('name', ['instructor', 'tenant_admin']))->first() ?? User::latest()->first();

        if (!$asker || !$expert) return;

        $tenantId = $asker->tenant_id ?? $expert->tenant_id;
        $category = Category::where('tenant_id', $tenantId)->first();

        // 1. Create Open General Discussion (No course_id)
        $discussion = Discussion::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'title' => 'Tanya: Mengapa daun cabai keriting menggulung ke atas dan apa solusinya?',
            ],
            [
                'course_id' => null, // General Q&A
                'lesson_id' => null,
                'category_id' => $category?->id,
                'content' => "Halo rekan-rekan dan bapak/ibu penyuluh,\n\nPada tanaman cabai rawit usia 35 HST di lahan kami, banyak daun muda menggulung ke atas (cupping) dan kaku disertai warna kekuningan. Apakah ini disebabkan oleh Thrips, Tungau, atau virus Gemini? Apa langkah penanganan praktis yang direkomendasikan?",
                'created_by' => $asker->id,
                'is_pinned' => false,
                'is_locked' => false,
                'is_resolved' => true,
            ]
        );

        // 2. Create Solution Reply by Expert
        $solutionReply = DiscussionReply::firstOrCreate(
            [
                'discussion_id' => $discussion->id,
                'created_by' => $expert->id,
            ],
            [
                'content' => "Berdasarkan gejala daun menggulung ke ATAS (cupping upward) dan kaku, penyebab utamanya adalah serangan Thrips (Thrips parvispinus), bukan tungau (tungau biasanya menggulung ke bawah seperti sendok terbalik).\n\nLangkah penanganan yang disarankan:\n1. Sanitasi gulma inang di sekitar bedengan.\n2. Pasang perangkap lekat kuning (yellow sticky trap) 40 buah per hektar.\n3. Aplikasi bahan aktif abamektin atau spinetoram pada pagi/sore hari dengan nozel halus.\n4. Tambahkan pupuk mikro (Zn dan B) untuk mempercepat pemulihan tunas baru.",
                'is_solution' => true,
                'verified_at' => now(),
                'verified_by' => $asker->id,
            ]
        );

        $discussion->update([
            'is_resolved' => true,
            'accepted_reply_id' => $solutionReply->id,
        ]);
    }
}
