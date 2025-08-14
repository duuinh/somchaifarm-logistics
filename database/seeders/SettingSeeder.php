<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'default_service_fee',
                'value' => '300',
                'type' => 'number',
                'description' => 'Default service fee (ค่าผสมอาหาร) amount in baht',
            ],
            [
                'key' => 'company_name',
                'value' => 'บริษัท สมชายฟาร์ม จำกัด',
                'type' => 'string',
                'description' => 'Company name for documents',
            ],
            [
                'key' => 'company_address',
                'value' => '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110',
                'type' => 'string',
                'description' => 'Company address for documents',
            ],
            [
                'key' => 'company_phone',
                'value' => '02-123-4567',
                'type' => 'string',
                'description' => 'Company phone number',
            ],
            [
                'key' => 'company_tax_id',
                'value' => '0105500000000',
                'type' => 'string',
                'description' => 'Company Tax ID Number',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
