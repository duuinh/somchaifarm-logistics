<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryNoteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_delivery_note()
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'customer_type' => 'regular',
        ]);

        $deliveryNote = DeliveryNote::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'delivery_date' => now(),
            'total_weight' => 1000.00,
            'total_amount' => 5000.00,
            'notes' => 'Test delivery note',
        ]);

        $this->assertDatabaseHas('delivery_notes', [
            'client_id' => $client->id,
            'created_by' => $user->id,
        ]);
        $this->assertEquals(1000.00, $deliveryNote->total_weight);
        $this->assertEquals(5000.00, $deliveryNote->total_amount);
    }

    /** @test */
    public function it_belongs_to_a_client()
    {
        $client = Client::create([
            'name' => 'Test Client',
            'customer_type' => 'regular',
        ]);
        $user = User::factory()->create();

        $deliveryNote = DeliveryNote::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'delivery_date' => now(),
        ]);

        $this->assertInstanceOf(Client::class, $deliveryNote->client);
        $this->assertEquals($client->id, $deliveryNote->client->id);
    }

    /** @test */
    public function it_belongs_to_a_creator_user()
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'customer_type' => 'regular',
        ]);

        $deliveryNote = DeliveryNote::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'delivery_date' => now(),
        ]);

        $this->assertInstanceOf(User::class, $deliveryNote->creator);
        $this->assertEquals($user->id, $deliveryNote->creator->id);
    }

    /** @test */
    public function it_can_have_optional_driver_and_vehicle()
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'customer_type' => 'regular',
        ]);

        $deliveryNote = DeliveryNote::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'delivery_date' => now(),
            'driver_id' => null,
            'vehicle_id' => null,
        ]);

        $this->assertNull($deliveryNote->driver_id);
        $this->assertNull($deliveryNote->vehicle_id);
    }

    /** @test */
    public function it_has_many_items()
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'customer_type' => 'regular',
        ]);

        $deliveryNote = DeliveryNote::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'delivery_date' => now(),
        ]);

        $item1 = Item::create([
            'name' => 'Item 1',
            'item_type' => 'material',
            'kg_per_bag_conversion' => 25.00,
        ]);

        $item2 = Item::create([
            'name' => 'Item 2',
            'item_type' => 'material',
            'kg_per_bag_conversion' => 25.00,
        ]);

        DeliveryNoteItem::create([
            'delivery_note_id' => $deliveryNote->id,
            'item_id' => $item1->id,
            'quantity' => 100,
            'unit_type' => 'kg',
            'price_per_unit' => 10.00,
            'total_price' => 1000.00,
        ]);

        DeliveryNoteItem::create([
            'delivery_note_id' => $deliveryNote->id,
            'item_id' => $item2->id,
            'quantity' => 4,
            'unit_type' => 'bags',
            'price_per_unit' => 250.00,
            'total_price' => 1000.00,
        ]);

        $this->assertCount(2, $deliveryNote->items);
        $this->assertInstanceOf(DeliveryNoteItem::class, $deliveryNote->items->first());
    }

}
