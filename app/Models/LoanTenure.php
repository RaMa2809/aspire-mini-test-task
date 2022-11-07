<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanTenure extends Model
{
    use HasFactory;

    protected $fillable = ['loan_id','loan_terms','part_payment_amount','due_date'];

    public function loan()
    {
        return $this->belongsTo(Loan::class,'loan_id','id');
    }
}
