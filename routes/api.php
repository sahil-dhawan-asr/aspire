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
/**Common Routes */
Route::post("/login",[UsersController::class,'login']);
Route::post('/logout',[UsersController::class,'logout'])->middleware(["auth:api"]);
/**End Common Routes */


/**Customer Specific Routes */
Route::post("/create-customer",[UsersController::class,'createCustomer']);
Route::post("/create-loan",[CustomerLoanController::class,'createLoan'])
->middleware(['auth:api', 'scope:create-loan,view-loan,add-repayment,view-all-own-loans']);
Route::get("/view-all-own-loans/{type?}",[CustomerLoanController::class,'viewLoans'])
->middleware(['auth:api', 'scope:create-loan,view-loan,add-repayment,view-all-own-loans']);
Route::get("/view-loan/{id}",[CustomerLoanController::class,'viewLoan'])
->middleware(['auth:api', 'scope:create-loan,view-loan,add-repayment,view-all-own-loans']);
Route::post("/add-repayment",[CustomerLoanController::class,'addRepayment'])
->middleware(['auth:api', 'scope:create-loan,view-loan,add-repayment,view-all-own-loans']);
/**End of customer specific routes */


/**Admin Specific Routes */
Route::patch("/approve-loan",[CustomerLoanController::class,'approveLoan'])
->middleware(['auth:api', 'scope:approve-loan,view-all-loans']);
/**End of Admin Specific Routes */
