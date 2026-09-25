<?php

namespace Database\Seeders;

use App\Models\WhatsappCustomerService;
use Illuminate\Database\Seeder;

class WhatsappCustomerServiceSeeder extends Seeder
{
    public function run(): void
    {
        WhatsappCustomerService::updateOrCreate(
            [
                'id' => 1,
            ],
            [
                'name' => 'Customer Service KIT',
                'phone_number' => '6281110555605',
                'is_active' => true,
            ]
        );
    }
}