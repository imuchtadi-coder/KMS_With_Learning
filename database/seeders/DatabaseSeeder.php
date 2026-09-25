<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
        ]);

        $this->call([
            CleanAndSeedDatabaseSeeder::class,
        ]);

        $this->call([
            CourseSeeder::class,
        ]);

        $this->call([
            GamificationConfigSeeder::class,
        ]);

        $this->call([
            WhatsappCustomerServiceSeeder::class,
        ]);
    }
}
