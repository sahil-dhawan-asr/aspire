<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use App\Http\Traits\ValidationsTrait;
use App\Http\Traits\ApiResponseTrait;

 /**
 * @OA\Info(
 *     version="1.0",
 *     title="Example for response examples value"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ValidationsTrait, ApiResponseTrait;
    
    protected $success = Response::HTTP_OK;
    protected $error = Response::HTTP_BAD_REQUEST;
    protected $message = "";
}
