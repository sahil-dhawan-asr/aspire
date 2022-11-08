<?php

namespace App\Http\Traits;


trait ApiResponseTrait{

    public function sendResponse($status,$message,$data=[]){

        return compact("status","message","data");
    }
    public function prepareApiResponseForUser($data){
        $response = new \stdClass();
        $response->name = $data->name;
        $response->email = $data->email;
        $response->token = $data->token;
        $response->created_at = $data->created_at;
        $response->updated_at = $data->updated_at;
        return $response;
    }
}