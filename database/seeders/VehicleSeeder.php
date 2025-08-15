<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            // รถกระบะ
            [
                'license_plate' => 'บธ4575',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถกระบะ',
                'load_capacity' => 4.50,
            ],
            [
                'license_plate' => 'บธ9515',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถกระบะ',
                'load_capacity' => 4.50,
            ],
            [
                'license_plate' => 'บบ3677',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถกระบะ',
                'load_capacity' => 4.00,
            ],
            // รถ 6 ล้อ
            [
                'license_plate' => '80-7554',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถ 6 ล้อ',
                'load_capacity' => 9.00,
            ],
            // รถ 10 ล้อ
            [
                'license_plate' => '80-7895',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถ 10 ล้อดั้ม',
                'load_capacity' => 12.00,
            ],
            [
                'license_plate' => '80-5757',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถ 10 ล้อดั้ม',
                'load_capacity' => 15.00,
            ],
            [
                'license_plate' => '80-2843',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถ 10 ล้อไม้ดั้ม',
                'load_capacity' => 15.00,
            ],
            // รถ 12 ล้อและรถพ่วง
            [
                'license_plate' => '80-7224 / 80-7225',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถพ่วง',
                'load_capacity' => 50.00,
            ],
            [
                'license_plate' => '80-7556 / 80-7558',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถพ่วง',
                'load_capacity' => 50.00,
            ],
            [
                'license_plate' => '81-1039 / 81-1040',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถพ่วง',
                'load_capacity' => 50.00,
            ],
            [
                'license_plate' => '81-1041 / 81-1042',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถพ่วง',
                'load_capacity' => 50.00,
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::create($vehicleData);
        }
    }
}