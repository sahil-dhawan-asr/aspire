<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_loan_id',
        'repayment_date',
        'repayment_amount',
    ];

    public function userLoan(){
        return $this->belongsTo(UserLoan::class);
    }
}
