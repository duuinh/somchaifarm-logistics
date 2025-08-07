<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'ผู้ดูแลระบบ',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Create operator user
        User::factory()->create([
            'name' => 'พนักงานออกบิล',
            'email' => 'operator@example.com',
            'role' => 'operator',
        ]);

        // Seed sample data
        $this->call([
            ClientSeeder::class,
            ItemSeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
        ]);
    }
}
