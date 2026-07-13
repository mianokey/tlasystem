<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StudentFeeInvoice extends Model
{
    use SoftDeletes;

protected $fillable = [
    'student_id',
    'session_year_id',
    'semester_id',
    'invoice_no',
    'total_amount',
    'paid_amount',
    'balance',
    'status',
    'due_date',
    'school_id',
    'generated_by', 
];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Student (User)
public function student()
{
    return $this->belongsTo(Students::class, 'student_id', 'id');
}

    

    // Session Year
    public function sessionYear()
    {
        return $this->belongsTo(SessionYear::class, 'session_year_id');
    }

    // Invoice Items
    public function items()
    {
        return $this->hasMany(StudentFeeInvoiceItem::class, 'student_fee_invoice_id');
    }


    // Payments
    public function payments()
    {
        return $this->hasMany(PaymentTransaction::class, 'student_fee_invoice_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getIsPaidAttribute()
    {
        return $this->balance <= 0;
    }

    public function getIsPartialAttribute()
    {
        return $this->paid_amount > 0 && $this->balance > 0;
    }

    

    public function scopeOwner($query)
{
    if (Auth::check()) {

        if (Auth::user()->hasRole('Super Admin')) {
            return $query;
        }

        if (Auth::user()->school_id) {
            return $query->where('school_id', Auth::user()->school_id);
        }
    }

    return $query;
}

}