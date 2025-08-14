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
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'ผู้ดูแลระบบ',
                'role' => 'admin',
                'password' => bcrypt('password'),
            ]
        );

        // Create operator user
        User::firstOrCreate(
            ['email' => 'operator@example.com'],
            [
                'name' => 'พนักงานออกบิล',
                'role' => 'operator',
                'password' => bcrypt('password'),
            ]
        );

        // Seed sample data
        $this->call([
            ClientSeeder::class,
            ItemSeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
        ]);
    }
}
