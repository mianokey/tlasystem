<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeeInvoiceItem extends Model
{
    protected $fillable = [

        'student_fee_invoice_id',
        'fees_id',
        'fees_type_id',
        'title',
        'amount',
        'paid_amount',
        'balance',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function invoice()
    {
        return $this->belongsTo(StudentFeeInvoice::class, 'student_fee_invoice_id');
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'fees_id');
    }

    public function feeType()
    {
        return $this->belongsTo(FeesType::class, 'fees_type_id');
    }

    public function payments()
{
    return $this->hasMany(
        PaymentTransaction::class,
        'student_fee_invoice_item_id'
    );
}

public function creditTransactions()
{
    return $this->hasMany(
        StudentCreditTransaction::class,
        'student_fee_invoice_item_id'
    );
}


}
