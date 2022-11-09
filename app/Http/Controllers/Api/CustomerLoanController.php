<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Config;
use DB;
use App\Models\UserLoan;
use App\Models\UserLoanRepayment;
use Auth;

class CustomerLoanController extends Controller
{
    /**
     * Here we are applying for new Loan
     * Loans can't be applied if existing loan request is still pending
     * Minimum Loan Amount is 1000 and maximum amount is 100000
     */
    /**
    * @OA\POST(
    *     path="/create-loan",
    *     tags={"Apply Loan"},
    *     summary="Apply for a new loan.",
    *     security={{"bearerAuth":{}}},
    *     operationId="createLoan",
    *       @OA\Parameter(
    *          name="amount",
    *          description="Loan Amount For Approval",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="term",
    *          description="Loan Terms",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *     @OA\Response(
    *          response=200,
    *          description="Loan request submitted successfully and is under review."
    *       ),
    *     @OA\Response(
    *         response=400,
    *         description="The amount must be at least 1000,The amount must not be greater than 100000,The term must be at least 1,The term must not be greater than 6."
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="Unauthenticated."
    *     ),
    *    @OA\MediaType(mediaType="application/json")),
    * )
    */
    public function createLoan(Request $request){
        
        $response = $this->validateLoanAmountAndTerm($request->all());  //From ValidationsTrait
        $data =[];
        $this->checkResponse($response); // Exit If Error
        $this->checkLoanAlreadyExists($request->user());
        try{
            
            $data = $this->saveLoan($request);
            $data = UserLoan::with("loanRepayments")->where("id",$data->id)->first();
            $status = $this->success;
            $this->message =  __("messages.loanCreated");
        }catch(Exception $e){
            $status = $this->error;
            $this->message =  $e->getMessage();
        }
        return $this->sendResponse($status,$this->message,$data);
    }
    /**
  * @OA\Get(
  *    path="/view-all-own-loans/{type}",summary="return all loans belonging to user",operationId="viewAllLoans",description= "List of all Loans with Filter on basis of type",security={{"bearerAuth":{}}},
  *    tags={"View User Loans"},
  *    @OA\Parameter(name="type",in="path",required=true,description="in-progress,pending,completed",
  *    @OA\Schema(type="string")),
  *
  *    @OA\Response(response=200,description="List of user loans",
  *    @OA\MediaType(mediaType="application/json")),
  *    @OA\Response(response=400,description="No such Loan exists."),
  * )
   **/
    public function viewLoans(Request $request){
        $type = isset($request['type']) ? $request['type'] : "";
        if($type && !in_array($request['type'],Config::get("constants.loan_status"))){
            return $this->sendResponse($this->error,__("messages.invalidLoanType"));
        }
        
        $user = Auth::user();
        $userLoans = UserLoan::with("loanRepayments")->where("user_id",$user->id)->when($type,function($q)use($type){
            return $q->where("status",$type);
        })->get();
        
        $message = (!$userLoans->count()) ? __("messages.noLoanFound") : __("messages.userLoans");
        $status =  (!$userLoans->count()) ? $this->error : $this->success;
        return $this->sendResponse($status,$message,$userLoans);
    }
/**
  * @OA\Get(
  *    path="/view-loan/{id}",summary="return loan belonging to user",operationId="viewIndividualLoan",description= "Loan Details",security={{"bearerAuth":{}}},
  *    tags={"View User Loans"},
  *    @OA\Parameter(name="id",in="path",required=true,
  *    @OA\Schema(type="integer")),
  *
  *    @OA\Response(response=200,description="Details of user loan",
  *    @OA\MediaType(mediaType="application/json")),
  *    @OA\Response(response=400,description="You don't own this loan."),
  * )
   **/
    public function viewLoan($id){
        
        $userLoan = UserLoan::with("loanRepayments")->where("id",$id)->first();
       
        if($response = Auth::user()->cannot('view', [$userLoan ] ) ){  // Call To policy for checking ownership of loan
            return $this->sendResponse($this->error,__("messages.invalidAccess"));
        }
        return $this->sendResponse($this->success,__("messages.loanDetails"),$userLoan);
        
    }
    /**Only Loan with status pending will be approved otherwise error will be thrown 
     * Only Admin can access this as scopes are mentioned in routes
     * If Customer Tries To Access this Then unauthenticated error will be thrown by the system
    */
    /**
    * @OA\Patch(
    *     path="/approve-loan",
    *     tags={"Admin Approve Loan"},
    *     summary="Approve a pending loan request.",
    *     security={{"bearerAuth":{}}},
    *     operationId="approveLoan",
    *       @OA\Parameter(
    *          name="loan_id",
    *          description="Loan Id For Approval",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *     @OA\Response(
    *          response=200,
    *          description="Loan approved successfully."
    *       ),
    *     @OA\Response(
    *         response=400,
    *         description="Only loans which are not approved can be approved again,No such Loan exists."
    *     ),
    *    @OA\MediaType(mediaType="application/json")),
    *     @OA\Response(response=403, description="Unauthenticated"),
    * )
    */
    public function approveLoan(Request $request){
        $response = $this->validateLoan($request->all());  //From ValidationsTrait
        $this->checkResponse($response); // Exit If Error
        
        try{
            UserLoan::where("id",$request['loan_id'])->update(["status"=>"approved"]);
            $status = $this->success;
            $this->message =  __("messages.loanApproved");
        }catch(Exception $e){
            $status = $this->error;
            $this->message =  $e->getMessage();
        }
        return $this->sendResponse($status,$this->message);
    }

/**
 * Only Owner of the loan can be able to make repayment
 * Here we are checking whether loan is approved or in progress or not
 * Minimum amount Must be equal to repayment amount
 * Maximum amount Must be equal to repayment amount or total Loan amount
 * Can Pay the loan in single installment as well
 * And rest of all adjusted
 * If Last installment is paid then loan marked as completed as well
 */

    /**
    * @OA\POST(
    *     path="/add-repayment",
    *     tags={"Loan Repayment"},
    *     summary="Make Repayment For Loan.",
    *     security={{"bearerAuth":{}}},
    *     operationId="addRepayment",
    *       @OA\Parameter(
    *          name="loan_id",
    *          description="Loan For which repayment to be done",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="loan_repayment_id",
    *          description="Loan Installment for which repayment to be done",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="amount",
    *          description="Amount to be paid",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *     @OA\Response(
    *          response=200,
    *          description="Loan request submitted successfully and is under review."
    *       ),
    *     @OA\Response(
    *         response=400,
    *         description="No such Loan exists,Loan is already completed,Invalid loan repayment,Amount must be greater than equal to minimum amount e.g 1000,Amount must be lesser than maximum amount e.g 100000 ."
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="Unauthenticated."
    *     ),
    *    @OA\MediaType(mediaType="application/json")),
    * )
    */
    public function addRepayment(Request $request){
        $response = $this->validateRepayment($request->all());  //From ValidationsTrait
        $this->checkResponse($response); // Exit If Error
        try{
            $userLoan = UserLoan::with("loanRepayments")->where("id",$request['loan_id'])->first();
            
            if($response = Auth::user()->cannot('view', [$userLoan ] ) ){  // Call To policy for checking ownership of loan
                return $this->sendResponse($this->error,__("messages.invalidAccess"));
            }   
            $status = $this->success;
            $this->message = DB::transaction(function () use($request,$userLoan) {
                 $paid_date = date("Y-m-d");   
                /** Case When All Amount is to be Paid at once Here we'll update repayments status as well as loan status
                 * If This is our 1st installment and no repayment is done and repayment amount is equal to Total Loan Amount
                 */
                if($request['amount'] == $userLoan->amount && $userLoan->status ==Config::get('constants.approved')){ 
                    $userLoan->amount_paid = $request['amount'];
                    $userLoan->status = 'completed';
                    $userLoan->completed_on = $paid_date;
                    $userLoan->save();
                    foreach($userLoan->loanRepayments as $key=>$value){
                        $value->amount_paid = $value->repayment_amount;
                        $value->paid_on = $paid_date;
                        $value->status = Config::get("constants.paid_status");
                        $value->save();
                    }
                    return __("messages.loanCompleted");
                }else{ // This is the case when repayment is already done or repayment amount is not equals to Total amount
                  $amountPaidTillNow = $userLoan->amount_paid;
                  /**
                   * Last Loan Repayment Details
                   * Current Installment Details
                   * Number of pending installments
                   */
                  $userLoanRepaymentLast = UserLoanRepayment::where("user_loan_id",$request['loan_id'])->orderBy("id","desc")->first();
                  $userLoanRepayment = UserLoanRepayment::with("userLoan")->where("id",$request['loan_repayment_id'])->first();
                  $peningInstallments = UserLoanRepayment::where([["user_loan_id",$request['loan_id']],["status",Config::get("constants.pending_status")]]);
                  
                  //Here checking whether this is last installment or not
                  $amountPaidTillNow +=$request['amount'];
                  $pendingAmount = $userLoan->amount - $amountPaidTillNow; //Calculating pending amount 
                  $userLoan->amount_paid = $amountPaidTillNow;
                  
                  if($userLoanRepayment->id != $userLoanRepaymentLast->id){
                      $totalPeningInstallments = $peningInstallments->count();
                      $totalPeningInstallments-=1;
                      $userLoan->status = Config::get("constants.in_prog_status");
                      if($totalPeningInstallments ==1){  // If One installment is pending then Updating Current and Pending installment 
                        $userLoan->amount_pending = $pendingAmount;
                        $userLoan->save();
                        UserLoanRepayment::where("id",$request['loan_repayment_id'])
                        ->update(["amount_paid"=>$amountPaidTillNow,"amount_left"=>$pendingAmount,"paid_on"=>$paid_date,"status"=>Config::get("constants.paid_status")]);
                        UserLoanRepayment::where([["status",Config::get("constants.pending_status")],["user_loan_id",$request['loan_id']]])
                        ->update(["repayment_amount"=>$pendingAmount]);
                        return __("messages.repaymentSuccess");      
                    }else{
                        
                        if($request['amount'] == $userLoan->amount_pending){  // In case Entire Remaining Amount Paid at once 
                            
                            $userLoan->status = Config::get('constants.completed');
                            $userLoan->completed_on = $paid_date;
                            $userLoan->amount_pending = $pendingAmount;
                            $userLoan->save();
                            UserLoanRepayment::where("id",$request['loan_repayment_id'])
                            ->update(["amount_paid"=>$request['amount'],"amount_left"=>$pendingAmount,"paid_on"=>$paid_date,"status"=>Config::get("constants.paid_status")]);
                            foreach($peningInstallments->get() as $key=>$value){
                                UserLoanRepayment::where("id",$value->id)
                                ->update(["paid_on"=>$paid_date,"status"=>Config::get("constants.paid_status")]);
                            }
                            return __("messages.loanCompleted");    
                        }else{  // If Paid in installments only
                            $userLoan->status = Config::get("constants.in_prog_status");
                            $userLoan->save();
                            $newWeeklyInstallment = str_replace(",","",number_format(($pendingAmount)/$totalPeningInstallments,2));
                            $lastInstallmentAmount = $pendingAmount - ($newWeeklyInstallment *($totalPeningInstallments-1)); 
                            UserLoanRepayment::where("id",$request['loan_repayment_id'])
                            ->update(["amount_paid"=>$amountPaidTillNow,"amount_left"=>$pendingAmount,"paid_on"=>$paid_date,"status"=>Config::get("constants.paid_status")]);
                            foreach($peningInstallments->get() as $key=>$value){
                                $repayment_amount = ($key !=($totalPeningInstallments-1)) ? $newWeeklyInstallment : $lastInstallmentAmount;
                                UserLoanRepayment::where("id",$value->id)
                                ->update(["repayment_amount"=>$repayment_amount]);
                            }
                            return __("messages.repaymentSuccess");
                        }
                        
                    }
                  }else{ // If Last Installment is Getting Paid making Loan Complete and Updating Repayment Status
                        $userLoan->amount_paid = $amountPaidTillNow;
                        $userLoan->amount_pending = $pendingAmount;
                        $userLoan->status = Config::get("constants.completed");
                        $userLoan->completed_on = $paid_date;
                        $userLoan->save();
                        UserLoanRepayment::where("id",$request['loan_repayment_id'])
                        ->update(["amount_paid"=>$request['amount'],"amount_left"=>$pendingAmount,"paid_on"=>$paid_date,"status"=>Config::get("constants.paid_status")]);
                        return __("messages.loanCompleted");            
                  }
                  
                }
            });
        }catch(Exception $e){
            $status = $this->error;
            $this->message =  $e->getMessage();
        }
        return $this->sendResponse($status,$this->message);
    }
    /** End Of Api Methods */
    
    /** Function For Sending Response in case of validation error */
    private function checkResponse($response){
        if($response['status']){
             exit(json_encode($this->sendResponse($this->error,$response['message']))); //From ApiResponseTrait
        }
    }

    private function saveLoan($request){
        return DB::transaction(function () use($request){
            $weeklyInstallment = str_replace(",","",number_format(($request['amount'])/$request['term'],2));
            $lastInstallmentAmount = $request['amount'] - ($weeklyInstallment *($request['term']-1));
            
            $userLoan = new UserLoan(["user_id"=>$request->user()->id,"term"=>$request['term'],"amount"=>$request['amount']]);
            $userLoan->save();
            
            $loanRepayment = [];
            for($i=1;$i<=$request['term'];$i++){
                $amount = $i==$request['term'] ? $lastInstallmentAmount : $weeklyInstallment; 
                $repayment_date = date("Y-m-d",(strtotime('+'.$i.'week' ,strtotime(date("Y-m-d")))));
                $loanRepayment[$i] = new UserLoanRepayment(array("user_id"=>$request->user()->id,"user_loan_id"=>$userLoan->id,"repayment_date"=>$repayment_date,"repayment_amount"=>$amount));
            }
            $userLoan->loanRepayments()->saveMany($loanRepayment);
            return $userLoan;
        });
    }
    private function checkLoanAlreadyExists($user){
       $loanExists = UserLoan::where([["status","pending"],["user_id",$user->id]])->count();
       ($loanExists) ?  exit(json_encode($this->sendResponse($this->error,__("messages.loanPending")))) : '';
    }
}
