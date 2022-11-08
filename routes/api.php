<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\CustomerLoanController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post("/create-customer",[UsersController::class,'createCustomer']);
Route::post("/login",[UsersController::class,'login']);
Route::post("/create-loan",[CustomerLoanController::class,'createLoan'])
->middleware(['auth:api', 'scope:create-loan,view-loan,add-repayment,view-all-own-loans']);
Route::get("/view-all-own-loans/{type?}",[CustomerLoanController::class,'viewLoans'])
->middleware(['auth:api', 'scope:create-loan,view-loan,add-repayment,view-all-own-loans']);
Route::get("/view-loan/{id}",[CustomerLoanController::class,'viewLoan'])
->middleware(['auth:api', 'scope:create-loan,view-loan,add-repayment,view-all-own-loans']);
Route::post("/add-repayment",[CustomerLoanController::class,'addRepayment'])
->middleware(['auth:api', 'scope:create-loan,view-loan,add-repayment,view-all-own-loans']);
/**Admin Specific Routes */
Route::post("/approve-loan",[CustomerLoanController::class,'approveLoan'])
->middleware(['auth:api', 'scope:approve-loan,view-all-loans']);
Route::post('/logout',[UsersController::class,'logout'])->middleware(["auth:api"]);