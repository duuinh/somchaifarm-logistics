<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_view_clients_list()
    {
        Client::create(['name' => 'Client 1', 'customer_type' => 'regular']);
        Client::create(['name' => 'Client 2', 'customer_type' => 'credit']);

        $response = $this->actingAs($this->user)
            ->get('/clients');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Clients/Index')
            ->has('clients.data', 2)
        );
    }

    /** @test */
    public function unauthenticated_user_cannot_view_clients()
    {
        $response = $this->get('/clients');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_create_client()
    {
        $clientData = [
            'name' => 'New Test Client',
            'address' => '123 Test Street',
            'phone' => '02-123-4567',
            'customer_type' => 'regular',
        ];

        $response = $this->actingAs($this->user)
            ->post('/clients', $clientData);

        $response->assertRedirect('/clients');
        $this->assertDatabaseHas('clients', $clientData);
    }

    /** @test */
    public function client_creation_requires_name()
    {
        $clientData = [
            'address' => '123 Test Street',
            'phone' => '02-123-4567',
            'customer_type' => 'regular',
        ];

        $response = $this->actingAs($this->user)
            ->post('/clients', $clientData);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function client_creation_validates_customer_type()
    {
        $clientData = [
            'name' => 'Test Client',
            'customer_type' => 'invalid_type',
        ];

        $response = $this->actingAs($this->user)
            ->post('/clients', $clientData);

        $response->assertSessionHasErrors('customer_type');
    }

    /** @test */
    public function authenticated_user_can_update_client()
    {
        $client = Client::create([
            'name' => 'Original Name',
            'customer_type' => 'regular',
        ]);

        $updatedData = [
            'name' => 'Updated Name',
            'address' => 'New Address',
            'phone' => '02-999-8888',
            'customer_type' => 'credit',
        ];

        $response = $this->actingAs($this->user)
            ->put("/clients/{$client->id}", $updatedData);

        $response->assertRedirect('/clients');
        $this->assertDatabaseHas('clients', $updatedData);
    }

    /** @test */
    public function authenticated_user_can_delete_client_without_delivery_notes()
    {
        $client = Client::create([
            'name' => 'Client to Delete',
            'customer_type' => 'regular',
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/clients/{$client->id}");

        $response->assertRedirect('/clients');
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }


    /** @test */
    public function can_view_client_details()
    {
        $client = Client::create([
            'name' => 'Detail Client',
            'address' => '456 Detail Street',
            'phone' => '02-555-5555',
            'customer_type' => 'credit',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/clients/{$client->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Clients/Show')
            ->has('client', fn ($page) => $page
                ->where('id', $client->id)
                ->where('name', 'Detail Client')
                ->where('customer_type', 'credit')
                ->etc()
            )
        );
    }
}