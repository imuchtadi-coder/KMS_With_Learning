<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DemoController extends Controller
{
    /**
     * Instant 1-Click Login to Demo Account
     */
    public function quickLogin(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:superadmin,tenant_admin,instructor,student'],
        ]);

        $role = $validated['role'];

        if ($role === 'superadmin') {
            $superadmin = User::role('superadmin')->first();
            if (! $superadmin) {
                $superadmin = User::create([
                    'name' => 'Super Admin',
                    'email' => 'superadmin@krakatau-it.co.id',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'tenant_id' => null,
                ]);
                $superadmin->assignRole('superadmin');
            }

            Auth::login($superadmin);
            $request->session()->regenerate();
            $request->session()->put('is_demo_session', true);
            $request->session()->put('demo_role', 'superadmin');

            return redirect()->route('admin.subscription-plans.index')->with(
                'success',
                'Berhasil masuk sebagai Super Admin.'
            );
        }

        // Find demo user in tenant-a first, or any tenant
        $demoUser = User::whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        })
        ->where(function ($q) {
            $q->where('tenant_id', 'tenant-a')
              ->orWhereNotNull('tenant_id');
        })
        ->first();

        if (! $demoUser) {
            // Ensure tenant-a exists for fallback
            $tenant = Tenant::firstOrCreate(
                ['id' => 'tenant-a'],
                ['name' => 'Tenant Demo - Universitas Teknologi', 'status' => 'active']
            );

            $email = "demo_{$role}@tenant-a.kitsuluh.test";
            $demoUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Demo ' . ucfirst(str_replace('_', ' ', $role)),
                    'password' => Hash::make('password123'),
                    'tenant_id' => $tenant->id,
                    'email_verified_at' => now(),
                ]
            );
            $demoUser->syncRoles([$role]);
        }

        // Make sure user's tenant is active so they are not blocked
        if ($demoUser->tenant && $demoUser->tenant->status !== 'active') {
            $demoUser->tenant->update(['status' => 'active']);
        }

        Auth::login($demoUser);
        $request->session()->regenerate();
        $request->session()->put('is_demo_session', true);
        $request->session()->put('demo_role', $role);

        $roleNames = [
            'superadmin' => 'Super Admin Platform',
            'tenant_admin' => 'Admin Institusi (Tenant)',
            'instructor' => 'Instruktur / Pengajar',
            'student' => 'Peserta / Siswa',
        ];
        $roleTitle = $roleNames[$role] ?? $role;

        $targetRoute = match ($role) {
            'superadmin' => route('admin.subscription-plans.index'),
            'tenant_admin' => route('admin.dashboard'),
            'instructor' => route('instructor.dashboard'),
            'student' => route('student.dashboard'),
            default => route('dashboard'),
        };

        return redirect($targetRoute)->with(
            'success',
            "Berhasil masuk ke mode Demo sebagai {$roleTitle}."
        );
    }
}
