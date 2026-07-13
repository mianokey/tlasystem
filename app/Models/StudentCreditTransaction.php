<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCreditTransaction extends Model
{
    use HasFactory;


    protected $fillable = [

        'student_id',

        'payment_transaction_id',

        'student_fee_invoice_id',

        'student_fee_invoice_item_id',

        'user_id',

        'type',

        'amount',

        'balance_after',

        'reference',

        'remarks',

    ];



    protected $casts = [

        'amount' => 'decimal:2',

        'balance_after' => 'decimal:2',

    ];



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    public function student()
    {
        return $this->belongsTo(Students::class);
    }



    public function paymentTransaction()
    {
        return $this->belongsTo(
            PaymentTransaction::class
        );
    }



    public function invoice()
    {
        return $this->belongsTo(
            StudentFeeInvoice::class,
            'student_fee_invoice_id'
        );
    }

    public function invoiceItem()
{
    return $this->belongsTo(
        StudentFeeInvoiceItem::class,
        'student_fee_invoice_item_id'
    );
}




public function user()
{
    return $this->belongsTo(User::class);
}



    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */


    public function scopeCredits($query)
    {
        return $query->whereIn('type',[
            'overpayment',
            'refund',
            'adjustment'
        ]);
    }


    public function scopeApplications($query)
    {
        return $query->where(
            'type',
            'invoice_application'
        );
    }

}