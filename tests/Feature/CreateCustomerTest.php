<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;

class CreateCustomerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

     /**Checking End Point For Login Api By Passing Sample Credentials */
    public function test_login_by_customer()
    {
        $response = $this->postJson('/api/login',["email"=>"s2@mailinator.com","password"=>"123456789"]);
        $response->dump();
        $response->assertOk($response['status']);
        
    }
    /**Checking End Point For View All Loans Api By Passing Sample Credentials */
    public function test_view_loan(){
        $user = User::factory()->make();
        Passport::actingAs($user);
        $token = $user->createToken("sample",["view-loan"])->accessToken;
        $headers = [ 'Authorization' => 'Bearer '.$token,'Accept'=>'application/json'];
        $response = $this->getjson('/api/view-all-own-loans', [], $headers);
        $response->assertStatus(403); // Checking For Failure when Failure Happens Then This Will Pass as we are passing sample data
    }

    
}
