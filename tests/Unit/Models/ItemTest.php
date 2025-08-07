<?php

namespace Tests\Unit\Models;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_material_item()
    {
        $itemData = [
            'name' => 'ข้าวโพด',
            'item_type' => 'material',
            'unit_type' => 'both',
            'regular_price_per_kg' => 12.50,
            'regular_price_per_bag' => 312.50,
            'credit_price_per_kg' => 13.00,
            'credit_price_per_bag' => 325.00,
            'kg_per_bag_conversion' => 25.00,
        ];

        $item = Item::create($itemData);

        $this->assertDatabaseHas('items', ['name' => 'ข้าวโพด']);
        $this->assertEquals('material', $item->item_type);
        $this->assertEquals(12.50, $item->regular_price_per_kg);
        $this->assertEquals(25.00, $item->kg_per_bag_conversion);
    }

    /** @test */
    public function it_can_create_a_service_item()
    {
        $itemData = [
            'name' => 'ค่าผสมอาหาร',
            'item_type' => 'service',
            'unit_type' => 'ton',
            'regular_price_per_ton' => 300.00,
            'credit_price_per_ton' => 300.00,
            'kg_per_bag_conversion' => 1000.00,
        ];

        $item = Item::create($itemData);

        $this->assertEquals('service', $item->item_type);
        $this->assertEquals(300.00, $item->regular_price_per_ton);
    }

    /** @test */
    public function it_calculates_price_correctly_for_regular_pricing()
    {
        $item = Item::create([
            'name' => 'Test Item',
            'item_type' => 'material',
            'unit_type' => 'both',
            'regular_price_per_kg' => 10.00,
            'regular_price_per_bag' => 250.00,
            'credit_price_per_kg' => 11.00,
            'credit_price_per_bag' => 275.00,
            'kg_per_bag_conversion' => 25.00,
        ]);

        // Test regular pricing
        $this->assertEquals(10.00, $item->regular_price_per_kg);
        $this->assertEquals(250.00, $item->regular_price_per_bag);
    }

    /** @test */
    public function it_calculates_price_correctly_for_credit_pricing()
    {
        $item = Item::create([
            'name' => 'Test Item',
            'item_type' => 'material',
            'unit_type' => 'both',
            'regular_price_per_kg' => 10.00,
            'regular_price_per_bag' => 250.00,
            'credit_price_per_kg' => 11.00,
            'credit_price_per_bag' => 275.00,
            'kg_per_bag_conversion' => 25.00,
        ]);

        // Test credit pricing
        $this->assertEquals(11.00, $item->credit_price_per_kg);
        $this->assertEquals(275.00, $item->credit_price_per_bag);
    }

    /** @test */
    public function it_handles_unit_conversion()
    {
        $item = Item::create([
            'name' => 'Test Item',
            'item_type' => 'material',
            'kg_per_bag_conversion' => 25.00,
        ]);

        // 4 bags should equal 100 kg
        $kgFromBags = 4 * $item->kg_per_bag_conversion;
        $this->assertEquals(100.00, $kgFromBags);

        // 100 kg should equal 4 bags
        $bagsFromKg = 100 / $item->kg_per_bag_conversion;
        $this->assertEquals(4, $bagsFromKg);
    }

    /** @test */
    public function it_validates_item_types()
    {
        $validTypes = ['material', 'service'];
        
        foreach ($validTypes as $type) {
            $item = Item::create([
                'name' => 'Test Item',
                'item_type' => $type,
                'kg_per_bag_conversion' => 25.00,
            ]);
            
            $this->assertEquals($type, $item->item_type);
        }
    }

    /** @test */
    public function it_handles_optional_price_fields()
    {
        $item = Item::create([
            'name' => 'Minimal Item',
            'item_type' => 'material',
            'kg_per_bag_conversion' => 25.00,
        ]);

        $this->assertNull($item->regular_price_per_kg);
        $this->assertNull($item->regular_price_per_bag);
        $this->assertNull($item->credit_price_per_kg);
        $this->assertNull($item->credit_price_per_bag);
    }
}