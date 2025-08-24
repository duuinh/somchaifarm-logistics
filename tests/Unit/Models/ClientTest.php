<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_client()
    {
        $clientData = [
            'name' => 'ฟาร์มทดสอบ',
            'address' => '123 ถนนทดสอบ',
            'phone' => '02-123-4567',
            'customer_type' => 'regular',
        ];

        $client = Client::create($clientData);

        $this->assertDatabaseHas('clients', $clientData);
        $this->assertEquals('ฟาร์มทดสอบ', $client->name);
        $this->assertEquals('regular', $client->customer_type);
    }

    /** @test */
    public function it_has_proper_fillable_attributes()
    {
        $client = new Client();
        $fillable = ['name', 'address', 'phone', 'customer_type'];
        
        $this->assertEquals($fillable, $client->getFillable());
    }


    /** @test */
    public function it_validates_customer_type()
    {
        $validTypes = ['regular', 'credit'];
        
        foreach ($validTypes as $type) {
            $client = Client::create([
                'name' => 'Test Client',
                'customer_type' => $type,
            ]);
            
            $this->assertEquals($type, $client->customer_type);
        }
    }

    /** @test */
    public function it_can_be_updated()
    {
        $client = Client::create([
            'name' => 'Original Name',
            'customer_type' => 'regular',
        ]);

        $client->update([
            'name' => 'Updated Name',
            'customer_type' => 'credit',
        ]);

        $this->assertEquals('Updated Name', $client->fresh()->name);
        $this->assertEquals('credit', $client->fresh()->customer_type);
    }

    /** @test */
    public function it_handles_optional_fields()
    {
        $client = Client::create([
            'name' => 'Minimal Client',
            'customer_type' => 'regular',
        ]);

        $this->assertNull($client->address);
        $this->assertNull($client->phone);
        $this->assertNotNull($client->id);
    }
}