<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\Driver;
use App\Models\Item;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryNoteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected Item $item;
    protected Driver $driver;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->client = Client::create([
            'name' => 'Test Client',
            'customer_type' => 'regular',
        ]);
        $this->item = Item::create([
            'name' => 'Test Item',
            'item_type' => 'material',
            'unit_type' => 'both',
            'regular_price_per_kg' => 10.00,
            'regular_price_per_bag' => 250.00,
            'credit_price_per_kg' => 11.00,
            'credit_price_per_bag' => 275.00,
            'kg_per_bag_conversion' => 25.00,
        ]);
        $this->driver = Driver::create([
            'name' => 'Test Driver',
            'phone' => '081-234-5678',
        ]);
        $this->vehicle = Vehicle::create([
            'license_plate' => 'ABC-123',
            'province' => 'Bangkok',
        ]);
    }

    /** @test */
    public function authenticated_user_can_view_delivery_notes_list()
    {
        DeliveryNote::create([
            'client_id' => $this->client->id,
            'created_by' => $this->user->id,
            'delivery_date' => now(),
            'total_weight' => 100,
            'total_amount' => 1000,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/delivery-notes');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('DeliveryNotes/Index')
            ->has('deliveryNotes.data', 1)
        );
    }

    /** @test */
    public function can_create_delivery_note_with_items()
    {
        $deliveryNoteData = [
            'client_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'vehicle_id' => $this->vehicle->id,
            'delivery_date' => now()->format('Y-m-d'),
            'notes' => 'Test notes',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 100,
                    'unit_type' => 'kg',
                    'price_per_unit' => 10.00,
                    'total_price' => 1000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post('/delivery-notes', $deliveryNoteData);

        $response->assertRedirect('/delivery-notes');
        
        $this->assertDatabaseHas('delivery_notes', [
            'client_id' => $this->client->id,
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function can_access_print_view()
    {
        $deliveryNote = DeliveryNote::create([
            'client_id' => $this->client->id,
            'created_by' => $this->user->id,
            'delivery_date' => now(),
            'total_weight' => 100,
            'total_amount' => 1000,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/delivery-notes/{$deliveryNote->id}/print");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('DeliveryNotes/Print')
            ->has('deliveryNote')
        );
    }
}