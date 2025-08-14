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
                'value' => 'บริษัท สมชายฟาร์มพัทลุง จำกัด (สำนักงานใหญ่)',
                'type' => 'string',
                'description' => 'Company name for documents',
            ],
            [
                'key' => 'company_address',
                'value' => 'เลขที่ 149 ม.7 ต.โตนดด้วน อ.ควนขนุน จ.พัทลุง',
                'type' => 'string',
                'description' => 'Company address for documents',
            ],
            [
                'key' => 'company_phone',
                'value' => '082-4939429, 087-0599884',
                'type' => 'string',
                'description' => 'Company phone number',
            ],
            [
                'key' => 'company_tax_id',
                'value' => '0935561000171',
                'type' => 'string',
                'description' => 'Company Tax ID Number',
            ],
            [
                'key' => 'company_fax',
                'value' => '074-681482',
                'type' => 'string',
                'description' => 'Company fax number',
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
