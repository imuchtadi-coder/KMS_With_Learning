<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create default tenant if not exists
        // $defaultTenant = Tenant::firstOrCreate(
        //     ['id' => 'default-tenant'],
        //     ['data' => []]
        // );

        // // Create default domain for tenant if not exists
        // Domain::firstOrCreate(
        //     ['domain' => 'default.lmskit.test'],
        //     ['tenant_id' => $defaultTenant->id]
        // );

        // Create superadmin user (no tenant assigned)
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@krakatau-it.co.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('@lM4brur1'),
                'email_verified_at' => now(),
                'tenant_id' => null, // Superadmin tidak diikat ke tenant tertentu
            ]
        );
        $superadmin->assignRole('superadmin');

        // // Create tenant admin user
        // $tenantAdmin = User::firstOrCreate(
        //     ['email' => 'admin@tenant1.lmskit.test'],
        //     [
        //         'name' => 'Tenant Admin',
        //         'password' => Hash::make('password123'),
        //         'email_verified_at' => now(),
        //         'tenant_id' => $defaultTenant->id,
        //     ]
        // );
        // $tenantAdmin->assignRole('tenant_admin');

        // // Create instructor user
        // $instructor = User::firstOrCreate(
        //     ['email' => 'instructor@tenant1.lmskit.test'],
        //     [
        //         'name' => 'Instructor User',
        //         'password' => Hash::make('password123'),
        //         'email_verified_at' => now(),
        //         'tenant_id' => $defaultTenant->id,
        //     ]
        // );
        // $instructor->assignRole('instructor');

        // // Create student users
        // for ($i = 1; $i <= 5; $i++) {
        //     $student = User::firstOrCreate(
        //         ['email' => "student{$i}@tenant1.lmskit.test"],
        //         [
        //             'name' => "Student User {$i}",
        //             'password' => Hash::make('password123'),
        //             'email_verified_at' => now(),
        //             'tenant_id' => $defaultTenant->id,
        //         ]
        //     );
        //     $student->assignRole('student');
        // }
    }
}
