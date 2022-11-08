<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount',
        'term'
    ];

    public function loanRepayments(){
        return $this->hasMany(UserLoanRepayment::class);
    }
}
