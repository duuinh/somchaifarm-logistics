<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Raw Materials
            [
                'name' => 'ข้าวโพด',
                'unit_type' => 'both',
                'regular_price_per_kg' => 12.50,
                'regular_price_per_bag' => 312.50,
                'credit_price_per_kg' => 13.00,
                'credit_price_per_bag' => 325.00,
                'kg_per_bag_conversion' => 25.00,
            ],
            [
                'name' => 'แป้งปลา',
                'unit_type' => 'both',
                'regular_price_per_kg' => 28.00,
                'regular_price_per_bag' => 700.00,
                'credit_price_per_kg' => 29.00,
                'credit_price_per_bag' => 725.00,
                'kg_per_bag_conversion' => 25.00,
            ],
            [
                'name' => 'แป้งกะทิ',
                'unit_type' => 'both',
                'regular_price_per_kg' => 15.50,
                'regular_price_per_bag' => 387.50,
                'credit_price_per_kg' => 16.00,
                'credit_price_per_bag' => 400.00,
                'kg_per_bag_conversion' => 25.00,
            ],
            [
                'name' => 'รำข้าว',
                'unit_type' => 'both',
                'regular_price_per_kg' => 8.50,
                'regular_price_per_bag' => 212.50,
                'credit_price_per_kg' => 9.00,
                'credit_price_per_bag' => 225.00,
                'kg_per_bag_conversion' => 25.00,
            ],
            [
                'name' => 'ปูนหอยเผา',
                'unit_type' => 'both',
                'regular_price_per_kg' => 3.50,
                'regular_price_per_bag' => 87.50,
                'credit_price_per_kg' => 4.00,
                'credit_price_per_bag' => 100.00,
                'kg_per_bag_conversion' => 25.00,
            ],
            [
                'name' => 'เกลือ',
                'unit_type' => 'both',
                'regular_price_per_kg' => 5.00,
                'regular_price_per_bag' => 125.00,
                'credit_price_per_kg' => 5.50,
                'credit_price_per_bag' => 137.50,
                'kg_per_bag_conversion' => 25.00,
            ],
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }
    }
}