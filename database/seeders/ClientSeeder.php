<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'ฟาร์มไก่สุขภาพดี',
                'address' => '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110',
                'phone' => '02-123-4567',
            ],
            [
                'name' => 'ฟาร์มหมูบางปะกอก',
                'address' => '456 ถนนพหลโยธิน แขวงจตุจักร เขตจตุจักร กรุงเทพฯ 10900',
                'phone' => '02-234-5678',
            ],
            [
                'name' => 'โรงงานอาหารสัตว์เจริญ',
                'address' => '789 ถนนรัชดาภิเษก แขวงดินแดง เขตดินแดง กรุงเทพฯ 10400',
                'phone' => '02-345-6789',
            ],
            [
                'name' => 'ฟาร์มปลาน้ำจืด',
                'address' => '321 หมู่ 5 ตำบลบางเสาธง อำเภอบางเสาธง จังหวัดสมุทรปราการ 10570',
                'phone' => '02-456-7890',
            ],
            [
                'name' => 'สหกรณ์ปศุสัตว์',
                'address' => '654 ถนนเพชรบุรี แขวงมักกะสัน เขตราชเทวี กรุงเทพฯ 10400',
                'phone' => '02-567-8901',
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }
    }
}