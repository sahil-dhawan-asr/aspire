<?php

namespace Tests\Unit;

use Tests\TestCase;

class CustomerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */

     /** Checking Function To Create Customer Whether Create Customer Is Working Or Not */

    public function test_create_customer_can_be_reached()
    {
        
        $response = $this->post('/api/create-customer',["email"=>"s2@mailinator.com","password"=>"12345678","name"=>"Sample"]);
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 200,
            ]);
    }
    
}
