<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'นายสมชาย ใจดี',
                'phone' => '081-234-5678',
                'id_card_number' => '1234567890123',
            ],
            [
                'name' => 'นายสมศักดิ์ รักงาน',
                'phone' => '082-345-6789',
                'id_card_number' => '2345678901234',
            ],
            [
                'name' => 'นายวิชัย มานะ',
                'phone' => '083-456-7890',
                'id_card_number' => '3456789012345',
            ],
        ];

        foreach ($drivers as $driverData) {
            Driver::create($driverData);
        }
    }
}