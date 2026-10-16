<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Subscription Plans
        $plans = [
            [
                'slug' => 'starter',
                'name' => 'Starter',
                'price' => 299000,
                'billing_period' => 'bulan',
                'description' => 'Paket ideal untuk lembaga bimbingan belajar, komunitas mandiri, dan pelatihan skala awal.',
                'badge' => null,
                'max_users' => 100,
                'max_courses' => 5,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'Kapasitas hingga 100 Siswa / Peserta Aktif',
                    'Maksimal 5 Kursus & Modul Pelatihan',
                    '1 Akun Administrator Institusi',
                    'Subdomain Mandiri (.kitsuluh.test)',
                    'Bank Soal & Kuis Pilihan Ganda',
                    'Sertifikat Digital Otomatis (Format Standar)',
                    'Dukungan Komunitas & Dokumentasi Online',
                ],
            ],
            [
                'slug' => 'profesional',
                'name' => 'Profesional',
                'price' => 799000,
                'billing_period' => 'bulan',
                'description' => 'Solusi lengkap terpopuler untuk sekolah, akademi vokasi, dan lembaga diklat berkembang.',
                'badge' => 'Paling Populer',
                'max_users' => 500,
                'max_courses' => null,
                'is_active' => true,
                'sort_order' => 2,
                'features' => [
                    'Kapasitas hingga 500 Siswa / Peserta Aktif',
                    'Kursus & Silabus Modul Tanpa Batas (Unlimited)',
                    '3 Akun Administrator & Instruktur Lembaga',
                    'Subdomain Mandiri & Personalisasi Branding',
                    'Streaming Video HLS Adaptif Hemat Kuota',
                    'Gamification Engine (Poin XP, Badges & Leaderboard)',
                    'Learning Paths (Kurikulum Terstruktur Bertahap)',
                    'Ekspor Laporan Nilai & Rekap Nilai Siswa (CSV/Excel)',
                    'Dukungan Layanan WhatsApp Jam Kerja',
                ],
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'price' => 1999000,
                'billing_period' => 'bulan',
                'description' => 'Infrastruktur penuh dengan skalabilitas tinggi untuk universitas, instansi pemerintah, dan korporat.',
                'badge' => 'Bisnis & Kampus',
                'max_users' => null,
                'max_courses' => null,
                'is_active' => true,
                'sort_order' => 3,
                'features' => [
                    'Jumlah Siswa & Peserta Aktif Tanpa Batas (Unlimited)',
                    'Kursus, Modul & Materi Video Tanpa Batas',
                    'Multi Akun Administrator & Instruktur Tanpa Batas',
                    'White-Label Penuh (Logo, Warna & Domain Sendiri)',
                    'Streaming Video HLS Server Prioritas Kecepatan Tinggi',
                    'Live Class Virtual Terintegrasi',
                    'Audit Log Aktivitas & Analitik Kompetensi Mendalam',
                    'Integrasi Single Sign-On (SSO) & Backup Otomatis',
                    'Dedicated Account Manager & Prioritas CS 24/7',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        // 2. Payment Methods (e-Wallets & QRIS)
        $paymentMethods = [
            [
                'code' => 'qris',
                'name' => 'QRIS Instant (Semua e-Wallet & Bank)',
                'type' => 'qris',
                'account_number' => 'NMID: ID102030405060708',
                'account_name' => 'PT KITSULUH EDUKASI NUSANTARA',
                'instructions' => 'Scan kode QRIS menggunakan GoPay, OVO, DANA, ShopeePay, BCA Mobile, atau mobile banking apapun.',
                'qr_image' => '/img/qris-dummy.png',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'gopay',
                'name' => 'GoPay',
                'type' => 'e-wallet',
                'account_number' => '0812-3456-7890',
                'account_name' => 'KITsuluh Official (Muhtadi)',
                'instructions' => 'Buka aplikasi Gojek / GoPay > Pilih Bayar / Transfer > Masukkan nomor GoPay di atas > Masukkan nominal sesuai paket.',
                'qr_image' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'ovo',
                'name' => 'OVO',
                'type' => 'e-wallet',
                'account_number' => '0812-3456-7890',
                'account_name' => 'KITsuluh Official',
                'instructions' => 'Buka aplikasi OVO > Pilih menu Transfer > Pilih Sesama OVO > Masukkan nomor ponsel di atas > Masukkan nominal transfer.',
                'qr_image' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'dana',
                'name' => 'DANA',
                'type' => 'e-wallet',
                'account_number' => '0812-3456-7890',
                'account_name' => 'KITsuluh Official',
                'instructions' => 'Buka aplikasi DANA > Pilih Kirim > Kirim ke Nomor HP di atas > Pastikan nama akun penerima sesuai > Masukkan nominal tepat.',
                'qr_image' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($paymentMethods as $pm) {
            PaymentMethod::updateOrCreate(
                ['code' => $pm['code']],
                $pm
            );
        }
    }
}
