<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::whereHas('roles', fn($q) => $q->whereIn('name', ['instructor', 'tenant_admin']))->first() ?? User::first();
        if (!$author) return;

        $category = Category::where('tenant_id', $author->tenant_id)->first() ?? Category::first();
        $tags = Tag::where('tenant_id', $author->tenant_id)->limit(3)->get();
        if ($tags->isEmpty()) {
            $tags = collect([
                Tag::firstOrCreate(['tenant_id' => $author->tenant_id, 'name' => 'SOP', 'slug' => 'sop']),
                Tag::firstOrCreate(['tenant_id' => $author->tenant_id, 'name' => 'Panduan Lapangan', 'slug' => 'panduan-lapangan']),
            ]);
        }

        $article1 = Article::firstOrCreate(
            [
                'tenant_id' => $author->tenant_id,
                'slug' => 'sop-penanganan-hama-dan-penyakit-tanaman-padi',
            ],
            [
                'title' => 'SOP Penanganan Hama dan Penyakit Tanaman Padi Terpadu',
                'excerpt' => 'Panduan operasional standar penanganan dan pencegahan hama wereng batang coklat serta penyakit blas pada masa vegetatif dan generatif.',
                'content' => '<h2>1. Latar Belakang & Tujuan</h2><p>Pengendalian Hama Terpadu (PHT) merupakan pendekatan ramah lingkungan yang memprioritaskan pemantauan berkala dan pemanfaatan musuh alami sebelum intervensi kimiawi.</p><h3>2. Langkah Operasional Pengendalian</h3><ul><li><strong>Pengamatan Mingguan:</strong> Lakukan sampling pada 20 rumpun acak per petak sawah.</li><li><strong>Ambang Ekonomi:</strong> Jika ditemukan wereng &gt; 5 ekor per rumpun pada fase vegetatif, lakukan tindakan pengendalian.</li><li><strong>Penggunaan Agens Hayati:</strong> Semprotkan <em>Beauveria bassiana</em> pada sore hari (kelembaban tinggi).</li></ul><blockquote>Penting: Selalu gunakan APD lengkap saat melakukan aplikasi fungisida atau insektisida lapangan.</blockquote>',
                'category_id' => $category?->id,
                'author_id' => $author->id,
                'status' => 'published',
                'views_count' => 42,
                'published_at' => now(),
            ]
        );

        if ($tags->isNotEmpty()) {
            $article1->tags()->sync($tags->pluck('id'));
        }

        $article2 = Article::firstOrCreate(
            [
                'tenant_id' => $author->tenant_id,
                'slug' => 'panduan-kalibrasi-alat-semprot-sprayer',
            ],
            [
                'title' => 'Panduan Praktis Kalibrasi Alat Semprot (Knapsack Sprayer)',
                'excerpt' => 'Langkah-langkah kalibrasi volume semprot untuk memastikan dosis pestisida tepat sasaran dan efisien.',
                'content' => '<h2>Mengapa Kalibrasi Diperlukan?</h2><p>Ketepatan dosis pestisida sangat bergantung pada kecepatan jalan aplikator, tekanan pompa, dan debit nozzle sprayer.</p><h3>Langkah-Langkah Kalibrasi:</h3><ol><li>Isi tangki sprayer dengan air bersih 5 liter.</li><li>Tentukan jarak uji coba (misal: 10 meter).</li><li>Semprotkan air dengan kecepatan jalan konstan dan catat waktu yang dibutuhkan.</li><li>Ukur sisa air di tangki untuk menghitung debit nozzle per menit.</li></ol>',
                'category_id' => $category?->id,
                'author_id' => $author->id,
                'status' => 'published',
                'views_count' => 18,
                'published_at' => now(),
            ]
        );

        if ($tags->isNotEmpty()) {
            $article2->tags()->sync($tags->pluck('id'));
        }
    }
}
