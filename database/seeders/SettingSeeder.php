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
                'value' => '0',
                'type' => 'number',
                'description' => 'Default service fee (ค่าผสม) amount in baht',
            ],
            [
                'key' => 'company_name',
                'value' => 'บริษัท ตัวอย่าง จำกัด',
                'type' => 'string',
                'description' => 'Company name for documents',
            ],
            [
                'key' => 'company_address',
                'value' => '123 ถนนตัวอย่าง แขวงตัวอย่าง เขตตัวอย่าง กรุงเทพฯ 10100',
                'type' => 'string',
                'description' => 'Company address for documents',
            ],
            [
                'key' => 'company_phone',
                'value' => '02-000-0000',
                'type' => 'string',
                'description' => 'Company phone number',
            ],
            [
                'key' => 'default_kg_per_bag',
                'value' => '25',
                'type' => 'number',
                'description' => 'Default kg per bag conversion',
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
