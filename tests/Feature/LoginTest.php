<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    //use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_succesful_login()
    {
    
        $response = $this->postJson('/api/login', ['username' => 'Botas', 'password' => '1234Bor']);
 
        $response
            //->assertStatus(201)
            ->assertJson([

                'status' => 1,
            ]);
    }

    public function test_wrong_user()
    {
    
        $response = $this->postJson('/api/login', ['username' => 'Votas', 'password' => '1234Bo']);
 
        $response
            //->assertStatus(201)
            ->assertJson([

                'status' => 0,
                'msg' => 'Usuario no registrado',
            ]);
    }

    public function test_wrong_password()
    {
    
        $response = $this->postJson('/api/login', ['username' => 'Botas', 'password' => '12345']);
 
        $response
            //->assertStatus(201)
            ->assertJson([

                'status' => 0,
                'msg' => 'Contrasena incorrecta, intentelo nuevamente',
            ]);
    }

}
