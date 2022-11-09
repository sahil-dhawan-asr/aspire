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
     *      version="1.0.0",
     *      title="Aspire Systems",
     *      description="Implementation of Loan System API's",
     *      @OA\Contact(
     *          email="sahildhawan74@gmail.com"
     *      ),
     *      @OA\License(
     *          name="Apache 2.0",
     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *      )
     * )
     *
     * @OA\Server(
     *      url=L5_SWAGGER_CONST_HOST,
     *      description="Demo API Server"
     * )

     *
     *

    * @OA\SecurityScheme(
    * securityScheme="bearerAuth",
    * in="header",
    * name="bearerAuth",
    * type="http",
    * scheme="bearer"
    * )

    */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ValidationsTrait, ApiResponseTrait;
    
    protected $success = Response::HTTP_OK;
    protected $error = Response::HTTP_BAD_REQUEST;
    protected $message = "";
}
