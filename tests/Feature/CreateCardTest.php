<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateCardTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function test_example()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    public function test_card_created_succesfully()
    {
    
        $response = $this->putJson('/api/cards/registerCard?api_token=$2y$10$MF04soaIUURRghJwok6vJesvDaMNMugYX2xzNIWSV4zDUQzx7/SwW', 
        ['name' => 'Guerrera con espada', 
         'description' => 'Bestia Mitica',
         'collection' => 1,]);
 
        $response
            ->assertStatus(200)
            ->assertJson([

                'status' => 1,
            ]);
    }
}
