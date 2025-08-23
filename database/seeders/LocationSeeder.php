<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Office location
        Location::create([
            'type' => 'office',
            'name' => 'สำนักงาน',
            'latitude' => 7.70496,
            'longitude' => 100.00464,
            'is_active' => true
        ]);

        // Pickup locations (rice mills)
        $pickupLocations = [
            ['name' => 'โรงสีทิพย์พานิช', 'latitude' => 7.830301, 'longitude' => 100.305199],
            ['name' => 'โรงสีจิตเจริญ', 'latitude' => 7.954374, 'longitude' => 100.214424],
            ['name' => 'โรงสีวาสนา', 'latitude' => 8.360839, 'longitude' => 100.144012],
            ['name' => 'โรงสีชูเชิด', 'latitude' => 7.857257, 'longitude' => 100.248482],
            ['name' => 'โรงสีวัฒนพรอินเตอร์โกลเด้น', 'latitude' => 7.950381, 'longitude' => 100.237488],
            ['name' => 'โรงสีโชคอำนวย', 'latitude' => 7.9543074, 'longitude' => 100.2137756]
        ];

        foreach ($pickupLocations as $location) {
            Location::create([
                'type' => 'pickup',
                'name' => $location['name'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'is_active' => true
            ]);
        }

        // Delivery locations (farms)
        $deliveryLocations = [
            ['name' => 'ละอองฟาร์ม', 'latitude' => 7.729165, 'longitude' => 99.956551],
            ['name' => 'ชวลิตรฟาร์ม', 'latitude' => 7.757777, 'longitude' => 99.894615],
            ['name' => 'สมยศ สยมพร', 'latitude' => 7.733764, 'longitude' => 100.078102],
            ['name' => 'พี่ถาวร', 'latitude' => 7.601144, 'longitude' => 99.995308],
            ['name' => 'มิตรภาพ (บิ๊ก โชคสุข)', 'latitude' => 7.609554, 'longitude' => 100.000046],
            ['name' => 'พี่ธีระ ป่าบอน', 'latitude' => 7.192664, 'longitude' => 100.215332],
            ['name' => 'อิสรา แพะ ปะเหลียน', 'latitude' => 7.170626, 'longitude' => 99.780846],
            ['name' => 'ลุงสิทธิ์', 'latitude' => 7.638457, 'longitude' => 99.991425],
            ['name' => 'วิสาหกิจนาขยาด', 'latitude' => 7.676242, 'longitude' => 99.966705],
            ['name' => 'สำราญ นาขยาด', 'latitude' => 7.669500, 'longitude' => 99.977710],
            ['name' => 'เบียร์ตะแพน', 'latitude' => 7.660380, 'longitude' => 99.912090],
            ['name' => 'พี่อมร: โค เขาเจียก', 'latitude' => 7.619116, 'longitude' => 100.046215],
            ['name' => 'สารวัตรวิทย์ ป่ายอม', 'latitude' => 7.825231, 'longitude' => 99.858521],
            ['name' => 'พี่กูน เลี้ยงหมู ลำพาย', 'latitude' => 7.619197, 'longitude' => 99.995644],
            ['name' => 'อุดมพร ป่ายอม', 'latitude' => 7.851799, 'longitude' => 99.941856],
            ['name' => 'ญาดา การเกษตร ฉลอง', 'latitude' => 7.617067, 'longitude' => 99.979172],
            ['name' => 'ปัณณวัชร์ ตรัง', 'latitude' => 7.384710, 'longitude' => 99.574310],
            ['name' => 'จุฑามาศฟาร์ม พี่ติ๋ม', 'latitude' => 7.116088, 'longitude' => 100.251747],
            ['name' => 'พ่อให้ฟาร์ม พี่ติ๋ม', 'latitude' => 7.233577, 'longitude' => 100.365112],
            ['name' => 'สิงห์เงินเจริญฟาร์ม พี่ติ๋ม', 'latitude' => 7.178449, 'longitude' => 100.319244],
            ['name' => 'อุดมศิลป์ฟาร์ม นายกบัตร', 'latitude' => 7.198642, 'longitude' => 100.321236],
            ['name' => 'ศรชัย', 'latitude' => 7.741341, 'longitude' => 99.913490],
            ['name' => 'พี่นุ้ย ประสิทธิ์ ยุพา ฟาร์ม', 'latitude' => 7.6961944, 'longitude' => 99.9683574],
            ['name' => 'เบทาโกร', 'latitude' => 7.1066048, 'longitude' => 100.3264869]
        ];

        foreach ($deliveryLocations as $location) {
            Location::create([
                'type' => 'delivery',
                'name' => $location['name'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'is_active' => true
            ]);
        }
    }
}
