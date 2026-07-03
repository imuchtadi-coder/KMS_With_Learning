<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions
        $permissions = [
            'create course',
            'edit course',
            'delete course',
            'publish course',
            'upload video',
            'view course',
            'enroll course',
            'view analytics',
            'manage users',
            'manage certificates',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $tenantAdminRole = Role::firstOrCreate(['name' => 'tenant_admin']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        // Assign permissions to roles
        $superAdminRole->syncPermissions($permissions);

        $tenantAdminRole->syncPermissions([
            'create course',
            'edit course',
            'delete course',
            'publish course',
            'upload video',
            'view course',
            'view analytics',
            'manage users',
            'manage certificates',
        ]);

        $instructorRole->syncPermissions([
            'create course',
            'edit course',
            'upload video',
            'view course',
            'view analytics',
            'manage certificates',
        ]);

        $studentRole->syncPermissions([
            'view course',
            'enroll course',
        ]);
    }
}
