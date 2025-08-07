<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'license_plate' => 'กข-1234',
                'province' => 'กรุงเทพมหานคร',
                'vehicle_type' => 'รถบรรทุก 6 ล้อ',
                'load_capacity' => 5000.00,
            ],
            [
                'license_plate' => 'คง-5678',
                'province' => 'สมุทรปราการ',
                'vehicle_type' => 'รถบรรทุก 10 ล้อ',
                'load_capacity' => 8000.00,
            ],
            [
                'license_plate' => 'จฉ-9012',
                'province' => 'นนทบุรี',
                'vehicle_type' => 'รถกระบะ',
                'load_capacity' => 1000.00,
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::create($vehicleData);
        }
    }
}