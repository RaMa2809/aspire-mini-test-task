<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = ['total_loan_amount',
        'user_id','loan_status','loan_terms'];

    public function loan_tenure()
    {
        return $this->hasMany(LoanTenure::class,'loan_id','id');
    }

}
