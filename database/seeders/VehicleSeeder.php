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
                'license_plate' => 'บธ-4575',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถกระบะ',
                'load_capacity' => 4.50,
                'gps_device_id' => 46397,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#FF0000', // Red
            ],
            [
                'license_plate' => 'บธ-9515',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถกระบะ',
                'load_capacity' => 4.50,
                'gps_device_id' => 62855,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#0000FF', // Blue
            ],
            [
                'license_plate' => 'บบ-3677',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถกระบะ',
                'load_capacity' => 4.00,
                'gps_device_id' => 312767,
                'gps_provider' => 'siamgps',
                'is_active' => true,
                'color' => '#00AA00', // Green
            ],
            // รถ 6 ล้อ
            [
                'license_plate' => '80-7554',
                'province' => 'พัทลุง',
                'vehicle_type' => '6 ล้อ',
                'load_capacity' => 9.00,
                'gps_device_id' => 26833,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#FF6600', // Orange
            ],
            // รถ 10 ล้อ
            [
                'license_plate' => '80-7895',
                'province' => 'พัทลุง',
                'vehicle_type' => '10 ล้อดั้ม',
                'load_capacity' => 12.00,
                'gps_device_id' => 26843,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#9900FF', // Purple
            ],
            [
                'license_plate' => '80-5757',
                'province' => 'พัทลุง',
                'vehicle_type' => '10 ล้อดั้ม',
                'load_capacity' => 15.00,
                'gps_device_id' => 26852,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#FF00FF', // Magenta
            ],
            [
                'license_plate' => '80-2843',
                'province' => 'พัทลุง',
                'vehicle_type' => '10 ล้อดั้ม',
                'load_capacity' => 15.00,
                'gps_device_id' => 26829,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#00AAAA', // Cyan
            ],
            // รถพ่วง
            [
                'license_plate' => '80-7224/25',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถพ่วง',
                'load_capacity' => 50.00,
                'gps_device_id' => 26830,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#AA5500', // Brown
            ],
            [
                'license_plate' => '80-7556/58',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถพ่วง',
                'load_capacity' => 50.00,
                'gps_device_id' => 26838,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#FF3399', // Pink
            ],
            [
                'license_plate' => '81-1039/40',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถพ่วง',
                'load_capacity' => 50.00,
                'gps_device_id' => 46222,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#666666', // Gray
            ],
            [
                'license_plate' => '81-1041/42',
                'province' => 'พัทลุง',
                'vehicle_type' => 'รถพ่วง',
                'load_capacity' => 50.00,
                'gps_device_id' => 68221,
                'gps_provider' => 'andaman',
                'is_active' => true,
                'color' => '#008B8B', // Dark Cyan
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::create($vehicleData);
        }
    }
}