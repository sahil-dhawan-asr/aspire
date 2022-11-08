<?php

namespace App\Http\Controllers\Api;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
Use App\Models\User;
use Hash;
use Config;

class UsersController extends Controller
{
    /** sendResponse Method is defined in ApiResponseTrait with order of parameters status, message, data */
    
    private $userObj;

    public function __construct(){
        $this->userObj = new User;
    }
    /** @OA\Get(
        *     path="/api/users",
        *     @OA\Response(response="200", description="An example endpoint")
        * )
        */
    /**Api For Creating Customer with name,email,password parameters. Here Validation is performed using ValidationsTrait and for response ApiResponse Trait is Used  */
    public function createCustomer(Request $request){
        $response = $this->validateCustomer($request->all());  //From ValidationsTrait
        
        $this->checkResponse($response); // Exit If Error
        try{
            $this->userObj->saveUser($request);
            $status = $this->success;
            $this->message =  __("messages.customerRegistered");
        }catch(Exception $e){
            $status = $this->error;
            $this->message =  $e->getMessage();
        }
        return $this->sendResponse($status,$this->message);
    }
    /** Function For Logging in For Admin/Customer */
    public function login(Request $request){
        $response = $this->validateUserDetails($request->all());  //From ValidationsTrait
        $this->checkResponse($response);
        $userDetails = $this->userObj->getUserByEmail($request->email);
        $userFound = $userDetails ? ($this->checkPassword($userDetails,$request->password)) : Config::get('constants.error_code');
        
        if(!$userFound){
            return $this->sendResponse($this->error,__("messages.invalidEmailPassword"));
        }
        $scopes = $userDetails->role == Config::get('customer_role') ? ['create-loan','view-loan','add-repayment'] : ['approve-loan','view-all-loans']; 
        $userDetails->token = $userDetails->createToken($userDetails->name,$scopes)->accessToken;
        
        return $this->sendResponse($this->success,__("messages.loginSuccess"),$this->prepareApiResponseForUser($userDetails));
    }

    public function logout(Request $request){
        Auth::user()->token()->revoke();
        return $this->sendResponse($this->success,__("messages.logoutSuccess"));
    }

    /** End Of Api Methods */

    /** Function For Sending Response in case of validation error */
    private function checkResponse($response){
        if($response['status']){
             exit(json_encode($this->sendResponse($this->error,$response['message']))); //From ApiResponseTrait
        }
    }
    /** Checking for user password */
    private function checkPassword($userDetails,$password){
        return Hash::check($password, $userDetails->password) ? Config::get('constants.success_code') : Config::get('constants.error_code');
    }
    
}
