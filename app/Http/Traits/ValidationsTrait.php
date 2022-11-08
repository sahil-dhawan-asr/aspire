<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Validator;
use Config;
use App\Models\UserLoan;
use App\Models\UserLoanRepayment;

trait ValidationsTrait{
public $response = array("status"=>"0");

    public function validateCustomer($data){
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|max:20',
        ]);
        return $validator->fails() ? $this->setResponse($validator) : $this->response;
    }

    public function validateUserDetails($data){
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        return $validator->fails() ? $this->setResponse($validator) : $this->response; 
        
    }

    public function validateLoanAmountAndTerm($data){
        $validator = Validator::make($data, [
            'amount' => 'required|integer|min:1000|max:100000',
            'term' => 'required|integer|min:1|max:6',
        ]);
        return $validator->fails() ? $this->setResponse($validator) : $this->response; 
        
    }

    public function validateLoan($data){
        $validator = Validator::make($data, [
            'loan_id' => ['required','integer',function($attribute,$value,$fail){
               $loanExists = UserLoan::where("id",$value)->first();
               if($loanExists && $loanExists->status !='pending'){
                $fail(__("messages.pendingLoansApproved"));
               }
               if(!$loanExists){
                $fail(__("messages.noLoanFound"));
               }
            }],
        ]);
        return $validator->fails() ? $this->setResponse($validator) : $this->response;
    }

    public function validateRepayment($data){
        $validator = Validator::make($data, [
            'loan_id' => ['required','integer',function($attribute,$value,$fail){
               $loanExists = UserLoan::where("id",$value)->first();
               
               if($loanExists && ($loanExists->status =='pending' ||$loanExists->status =='completed')){
                ($loanExists->status =='pending') ? $fail(__("messages.loanRepaymentPending")) : $fail(__("messages.loanAlreadyCompleted"));
               }

               if(!$loanExists){
                $fail(__("messages.noLoanFound"));
               }
            }],
            'loan_repayment_id' =>['required','integer',function($attribute,$value,$fail) use($data){
                $userLoanRepayment = UserLoanRepayment::where([["id",$value],["user_loan_id",$data['loan_id']]])->first();
                if($userLoanRepayment && $userLoanRepayment->status !='pending'){
                 $fail(__("messages.repaymentPaid"));
                }
                if(!$userLoanRepayment){
                 $fail(__("messages.invalidLoanRepayment"));
                }
             }],
            'amount'=>['required','numeric',function($attribute,$value,$fail)use ($data){
                $userLoanRepayment = UserLoanRepayment::with("userLoan")
                ->where("id",$data['loan_repayment_id'])
                ->first();
                
                if($value <$userLoanRepayment->repayment_amount){
                    $fail("Amount must be greater than equal to minimum amount ".$userLoanRepayment->repayment_amount);
                }
                if($value >$userLoanRepayment->userLoan->amount){
                    $fail("Amount must be lesser than maximum amount ".$userLoanRepayment->userLoan->amount);
                }
                if($userLoanRepayment->userLoan->status =='in-progress' && $value>$userLoanRepayment->userLoan->amount_pending){
                    $fail("Amount must be lesser than equal to maximum left amount ".$userLoanRepayment->userLoan->amount_pending);
                }
            }]
        ]);
        return $validator->fails() ? $this->setResponse($validator) : $this->response;
    }

    private function setResponse($validator){
        $this->response['status'] = Config::get('constants.validation_error_status');
        $this->response['message'] = $validator->messages()->first();
        return $this->response;
    }
}



?>