<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset data progress dan akun sandbox demo ke kondisi awal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pembersihan dan reset lingkungan demo sandbox...');

        // 1. Pastikan Tenant Demo (tenant-a) ada dan aktif
        $tenant = Tenant::firstOrCreate(
            ['id' => 'tenant-a'],
            ['name' => 'Tenant Demo - Universitas Teknologi', 'status' => 'active']
        );

        // 2. Reset / buat akun demo baku
        $demoAccounts = [
            [
                'email' => 'admin@tenant-a.kitsuluh.test',
                'name' => 'Demo Admin Institusi',
                'role' => 'tenant_admin',
            ],
            [
                'email' => 'instructor@tenant-a.kitsuluh.test',
                'name' => 'Demo Instruktur',
                'role' => 'instructor',
            ],
            [
                'email' => 'student1@tenant-a.kitsuluh.test',
                'name' => 'Demo Siswa (Student 1)',
                'role' => 'student',
            ],
        ];

        foreach ($demoAccounts as $acc) {
            $user = User::firstOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'password' => Hash::make('password123'),
                    'tenant_id' => $tenant->id,
                    'email_verified_at' => now(),
                ]
            );

            // Reset password ke default jika pernah diubah
            $user->update([
                'password' => Hash::make('password123'),
                'tenant_id' => $tenant->id,
            ]);

            $user->syncRoles([$acc['role']]);
        }

        // 3. Bersihkan sesi attempt kuis dari akun demo siswa jika ada yang corrupt
        $demoStudent = User::where('email', 'student1@tenant-a.kitsuluh.test')->first();
        if ($demoStudent) {
            DB::table('quiz_attempts')
                ->where('user_id', $demoStudent->id)
                ->where('created_at', '<', now()->subHours(24))
                ->delete();
        }

        $this->info('✓ Lingkungan demo sandbox berhasil di-reset ke kondisi standar.');
        return Command::SUCCESS;
    }
}
