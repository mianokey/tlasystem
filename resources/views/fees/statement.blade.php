@extends('layouts.master')

@section('title','Student Fee Statement')

@section('content')


<style>


body{
    background:#f4f6f9;
    font-size:.90rem;
}

.statement-card{
    border:none;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 3px 12px rgba(0,0,0,.08);
}

.statement-title{
    font-weight:700;
    letter-spacing:.5px;
}

.small-title{
    font-size:12px;
    color:#6c757d;
    text-transform:uppercase;
    letter-spacing:.4px;
}

/*==================================================
SUMMARY CARDS
==================================================*/

.summary-card{

    border:none;
    border-radius:12px;
    color:#fff;
    transition:.2s;
    overflow:hidden;

}

.summary-card:hover{

    transform:translateY(-2px);

}

.summary-card .icon{

    font-size:28px;
    opacity:.25;
    position:absolute;
    right:18px;
    top:15px;

}

.summary-card h6{

    font-size:12px;
    text-transform:uppercase;
    margin-bottom:6px;
    letter-spacing:.4px;

}

.summary-card h3{

    margin:0;
    font-weight:700;

}

.bg-invoiced{

    background:#0d6efd;

}

.bg-paid{

    background:#198754;

}

.bg-balance{

    background:#dc3545;

}

.bg-credit{

    background:#6f42c1;

}

.bg-adjustment{

    background:#fd7e14;

}

.bg-invoicecount{

    background:#0dcaf0;

}

/*==================================================
FILTERS
==================================================*/

.filter-btn{

    margin-right:6px;
    margin-bottom:6px;
    border-radius:30px;

}

/*==================================================
PRINTABLE AREA
==================================================*/

.statement-paper{

    background:#fff;
    border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    padding:30px;

}

/*==================================================
SCHOOL HEADER
==================================================*/

.school-header{

    border-bottom:3px solid #0d6efd;
    padding-bottom:15px;
    margin-bottom:20px;

}

.school-name{

    font-size:30px;
    font-weight:700;
    color:#0d47a1;

}

.school-motto{

    font-style:italic;
    color:#777;

}

.statement-heading{

    font-size:22px;
    font-weight:700;
    margin-top:15px;
    text-transform:uppercase;

}

/*==================================================
STUDENT INFO
==================================================*/

.student-box{

    border:1px solid #dee2e6;
    border-radius:8px;
    padding:15px;
    margin-bottom:25px;

}

.student-box table{

    width:100%;

}

.student-box td{

    padding:5px;

}

/*==================================================
INVOICE HEADER
==================================================*/

.invoice-header{

    background:#edf4ff;
    border-left:5px solid #0d6efd;
    border-radius:6px;
    padding:12px 18px;
    margin-top:30px;
    margin-bottom:0;

}

.invoice-header table{

    width:100%;

}

.invoice-header td{

    padding:3px 8px;
    white-space:nowrap;

}

.invoice-title{

    font-weight:700;
    color:#0d47a1;

}

/*==================================================
STATUS BADGES
==================================================*/

.badge-paid{

    background:#198754;

}

.badge-partial{

    background:#ffc107;
    color:#000;

}

.badge-unpaid{

    background:#dc3545;

}

/*==================================================
FEE ITEMS
==================================================*/

.fee-table{

    margin-bottom:0;

}

.fee-table thead{

    background:#f8f9fa;

}

.fee-item{

    background:#fff;

}

.fee-item td{

    font-weight:600;
    vertical-align:middle;

}

/*==================================================
TRANSACTION ROWS
==================================================*/

.transaction-row{

    background:#fafafa;

}

.transaction-row td{

    border-top:none;
    padding-top:4px;
    padding-bottom:4px;
    font-size:13px;

}

.transaction-indent{

    padding-left:45px !important;

}

.transaction-type{

    font-size:11px;
    padding:4px 8px;
    border-radius:30px;

}

.type-payment{

    background:#d1e7dd;
    color:#0f5132;

}

.type-credit{

    background:#e2d9f3;
    color:#59359c;

}

.type-adjustment{

    background:#ffe5d0;
    color:#b45309;

}

.type-refund{

    background:#ffd6d6;
    color:#842029;

}

.type-waiver{

    background:#d1f4ff;
    color:#055160;

}

/*==================================================
TOTALS
==================================================*/

.statement-total{

    background:#f8f9fa;
    border-top:3px solid #0d6efd;
    border-radius:8px;
    margin-top:30px;

}

.statement-total table{

    width:100%;

}

.statement-total td{

    padding:10px 15px;

}

.grand-total{

    font-size:20px;
    font-weight:bold;

}

/* ===========================
   PDF MODE
=========================== */

.pdf-mode{

    width: 190mm;
    margin: auto;
    padding: 10mm;
    background: #fff;
    box-shadow: none !important;
}

.pdf-mode body,
.pdf-mode{

    font-size: 11px !important;
    line-height: 1.25;
}

.pdf-mode .school-name{

    font-size: 22px !important;
}

.pdf-mode .statement-heading{

    font-size: 16px !important;
}

.pdf-mode .invoice-header{

    padding: 6px 10px !important;
}

.pdf-mode table{

    font-size: 10px !important;
}

.pdf-mode td{

    padding: 3px 5px !important;
}

.pdf-mode .badge{

    font-size: 9px !important;
}

.pdf-mode .transaction-row td{

    font-size: 9px !important;
}

.pdf-mode .statement-total{

    margin-top: 15px;
}

.pdf-mode .grand-total{

    font-size: 15px !important;
}

.pdf-mode .student-box{

    margin-bottom:10px;
}

.pdf-mode .invoice-header{

    margin-top:15px;
}

.pdf-mode .fee-table{

    margin-bottom:8px;
}


/*==================================================
PRINT
==================================================*/

@media print{

.no-print{

display:none !important;

}

body{

background:#fff !important;

}

.statement-paper{

box-shadow:none;

padding:0;

}

.card{

border:none !important;
box-shadow:none !important;

}

table{

font-size:12px;

}

.invoice-header{

background:#f4f4f4 !important;
print-color-adjust:exact;
-webkit-print-color-adjust:exact;

}

.summary-card{

display:none !important;

}

.filter-toolbar{

display:none !important;

}

}

</style>



<div class="container-fluid">
    

    {{--=========================================
        SUMMARY CARDS (NOT PRINTED)
    ==========================================--}}

    <div class="row mb-4 no-print">

        <div class="col-lg-2 col-md-4 mb-3">

            <div class="card summary-card bg-invoiced position-relative">

                <div class="card-body">

                    <i class="fa fa-file-text icon"></i>

                    <h6>Total Invoiced</h6>

                    <h3>

                        {{ number_format($summary['total_invoiced'],2) }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-lg-2 col-md-4 mb-3">

            <div class="card summary-card bg-paid position-relative">

                <div class="card-body">

                    <i class="fa fa-money icon"></i>

                    <h6>Total Paid</h6>

                    <h3>

                        {{ number_format($summary['total_paid'],2) }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-lg-2 col-md-4 mb-3">

            <div class="card summary-card bg-balance position-relative">

                <div class="card-body">

                    <i class="fa fa-balance-scale icon"></i>

                    <h6>Outstanding</h6>

                    <h3>

                        {{ number_format($summary['balance'],2) }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-lg-2 col-md-4 mb-3">

            <div class="card summary-card bg-credit position-relative">

                <div class="card-body">

                    <i class="fa fa-credit-card icon"></i>

                    <h6>Credits</h6>

                    <h3>

                        {{ number_format($summary['credit_balance'],2) }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-lg-2 col-md-4 mb-3">

            <div class="card summary-card bg-adjustment position-relative">

                <div class="card-body">

                    <i class="fa fa-edit icon"></i>

                    <h6>Adjustments</h6>

                    <h3>

                        {{ number_format($summary['adjustments'] ?? 0,2) }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-lg-2 col-md-4 mb-3">

            <div class="card summary-card bg-invoicecount position-relative">

                <div class="card-body">

                    <i class="fa fa-folder-open icon"></i>

                    <h6>Invoices</h6>

                    <h3>

                        {{ $invoices->count() }}

                    </h3>

                </div>

            </div>

        </div>

    </div>

    {{--=========================================
        FILTER TOOLBAR
    ==========================================--}}

<div class="mb-4 no-print">

    <div class="d-flex justify-content-end gap-2">

        <button type="button"
                onclick="window.print()"
                class="btn btn-success">

            <i class="fa fa-print"></i>

            Print

        </button>

        <button type="button"
                onclick="downloadPDF()"
                class="btn btn-danger">

            <i class="fa fa-file-pdf-o"></i>

            PDF

        </button>

    </div>

</div>

    {{--=========================================
        PRINTABLE STATEMENT STARTS HERE
    ==========================================--}}

    <div id="pdf-content" class="statement-paper">
        <div class="school-header">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="{{ asset($school['vertical_logo']) }}" style="height:90px;">
                </div>
                <div class="col-md-8 text-center">
                    <div class="school-name">
                        {{ $school['school_name'] }}
                    </div>
                    <div class="school-motto">
                      " {{ $school['school_tagline'] }} " <br>
                    <b>  {{ $school['school_address'] }} || {{ $school['school_phone'] }} || {{ $school['school_email'] }} </b>
                    </div>
                    <div class="statement-heading">
                        Student Fee Statement
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    Printed
                    <br>
                    {{ now()->format('d M Y H:i') }}
                </div>
            </div>
        </div>
        <div class="student-box">
            <table>
                <tr>
                    <td width="20%"><strong>Student</strong></td>
                    <td width="30%">
                        {{ $student->user->first_name }}
                        {{ $student->user->last_name }}
                    </td>
                    <td width="20%"><strong>Admission</strong></td>
                    <td>
                        {{ $student->admission_no }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Class</strong></td>
                    <td>
                        {{ optional($student->class_section->class)->name }}
                        {{ optional($student->class_section->section)->name }}
                    </td>
                    <td><strong>Statement Date</strong></td>
                    <td>
                        {{ now()->format('d M Y') }}
                    </td>
                </tr>
            </table>
        </div>
{{--==========================================================
    PART 2
    INVOICE LOOP + ONE LINE HEADER + FEE ITEMS
===========================================================--}}

@foreach($invoices as $invoice)

@php

    $statusClass = match(strtolower($invoice->status)){
        'paid'      => 'badge-paid',
        'partial'   => 'badge-partial',
        default     => 'badge-unpaid',
    };

@endphp


{{--==========================================================
    INVOICE HEADER (ONE LINE)
===========================================================--}}

<div class="invoice-header">

    <table>

        <tr>

            <td width="14%">

                <strong>Date</strong><br>

                {{ $invoice->created_at->format('d M Y') }}

            </td>

            <td width="20%">

                <strong>Invoice</strong><br>

                <span class="invoice-title">

                    {{ $invoice->invoice_no }}

                </span>

            </td>

            <td width="15%" class="text-end">

                <strong>Total</strong><br>

                {{ number_format($invoice->total_amount,2) }}

            </td>

            <td width="15%" class="text-end text-success">

                <strong>Paid</strong><br>

                {{ number_format($invoice->paid_amount,2) }}

            </td>

            <td width="15%" class="text-end text-danger">

                <strong>Balance</strong><br>

                {{ number_format($invoice->balance,2) }}

            </td>

            <td width="15%" class="text-center">

                <strong>Status</strong><br>

                <span class="badge {{ $statusClass }}">

                    {{ strtoupper($invoice->status) }}

                </span>

            </td>

        </tr>

    </table>

</div>


{{--==========================================================
    FEE ITEMS
===========================================================--}}

<table class="table table-bordered fee-table mb-5">

    <thead>

        <tr>

            <th width="34%">

                Fee Item

            </th>

            <th width="12%" class="text-end">

                Charge

            </th>

            <th width="12%" class="text-end">

                Paid

            </th>

            <th width="12%" class="text-end">

                Balance

            </th>

            <th>

                Remarks

            </th>

        </tr>

    </thead>

    <tbody>

    @foreach($invoice->items as $item)

        {{--==============================================
            FEE ITEM ROW
        ==============================================--}}

        <tr class="fee-item bg-light">

            <td>

                <i class="fa fa-book text-primary me-2"></i>

                <strong>

                    {{ optional($item->feeType)->name }}

                </strong>

            </td>

            <td class="text-end">

                {{ number_format($item->amount,2) }}

            </td>

            <td class="text-end text-success">

                {{ number_format($item->paid_amount,2) }}

            </td>

            <td class="text-end text-danger">

                {{ number_format($item->balance,2) }}

            </td>

            <td>

                @if($item->balance == 0)

                    <span class="text-success">

                        Fully Paid

                    </span>

                @elseif($item->paid_amount > 0)

                    <span class="text-warning">

                        Partially Paid

                    </span>

                @else

                    <span class="text-danger">

                        Unpaid

                    </span>

                @endif

            </td>

        </tr>


        {{--==============================================
            GET ALL TRANSACTIONS FOR THIS ITEM
        ==============================================--}}

        @php

            /*
            We combine all transaction types
            then sort by date.
            */

            $transactions = collect()

                ->merge(

                    $item->payments->map(function($payment){

                        $payment->transaction_type='payment';

                        $payment->transaction_date=
                            $payment->payment_date ??
                            $payment->created_at;

                        return $payment;

                    })

                )

                ->merge(

                    $item->creditTransactions->map(function($credit){

                        $credit->transaction_type='credit';

                        $credit->transaction_date=
                            $credit->created_at;

                        return $credit;

                    })

                );

            /*
            Optional relations
            Uncomment if available
            */

            /*
            ->merge(
                $item->manualAdjustments->map(...)
            )

            ->merge(
                $item->refunds->map(...)
            )

            ->merge(
                $item->waivers->map(...)
            )
            */

            $transactions =

                $transactions

                ->sortBy('transaction_date');

        @endphp


        {{--==============================================
            IF NO TRANSACTIONS
        ==============================================--}}

        @if($transactions->isEmpty())

            <tr class="transaction-row">

                <td colspan="5"
                    class="transaction-indent text-muted">

                    <i class="fa fa-minus-circle"></i>

                    No transactions recorded.

                </td>

            </tr>

        @endif
{{--==========================================================
    PART 3
    TRANSACTION ROWS
===========================================================--}}

@foreach($transactions as $transaction)

    @php

        /*
        |--------------------------------------------------------------------------
        | Default Values
        |--------------------------------------------------------------------------
        */

        $rowClass   = '';
        $badgeClass = '';
        $badgeText  = '';
        $reference  = '';
        $remarks    = '';
        $amount     = 0;
        $receivedBy = '';

        /*
        |--------------------------------------------------------------------------
        | MONEY IN
        |--------------------------------------------------------------------------
        */

        if($transaction->transaction_type == 'payment'){

            $rowClass   = 'payment';
            $badgeClass = 'type-payment';
            $badgeText  = 'Money In';

            $reference  = $transaction->payment_id;

            $remarks =

                $transaction->remarks
                ? $transaction->remarks
                : $transaction->payment_method;

            $amount = $transaction->amount;

            $receivedBy = optional($transaction->user)->first_name;

        }

        /*
        |--------------------------------------------------------------------------
        | CREDIT
        |--------------------------------------------------------------------------
        */

        elseif($transaction->transaction_type == 'credit'){

            $rowClass   = 'credit';
            $badgeClass = 'type-credit';
            $badgeText  = 'Credit Applied';

            $reference = $transaction->reference;

            $remarks =

                $transaction->remarks ??
                'Credit Allocation';

            $amount = abs($transaction->amount);

            $receivedBy = optional($transaction->user)->first_name;

        }

        /*
        |--------------------------------------------------------------------------
        | MANUAL ADJUSTMENT
        |--------------------------------------------------------------------------
        */

        elseif($transaction->transaction_type == 'adjustment'){

            $rowClass   = 'adjustment';
            $badgeClass = 'type-adjustment';
            $badgeText  = 'Adjustment';

            $reference = $transaction->reference;

            $remarks =

                $transaction->remarks;

            $amount = abs($transaction->amount);

            $receivedBy = optional($transaction->user)->first_name;

        }

        /*
        |--------------------------------------------------------------------------
        | REFUND
        |--------------------------------------------------------------------------
        */

        elseif($transaction->transaction_type == 'refund'){

            $rowClass   = 'refund';
            $badgeClass = 'type-refund';
            $badgeText  = 'Refund';

            $reference = $transaction->reference;

            $remarks =

                $transaction->remarks;

            $amount = abs($transaction->amount);

            $receivedBy = optional($transaction->user)->first_name;

        }

        /*
        |--------------------------------------------------------------------------
        | WAIVER
        |--------------------------------------------------------------------------
        */

        elseif($transaction->transaction_type == 'waiver'){

            $rowClass   = 'waiver';
            $badgeClass = 'type-waiver';
            $badgeText  = 'Waiver';

            $reference = $transaction->reference;

            $remarks =

                $transaction->remarks;

            $amount = abs($transaction->amount);

            $receivedBy = optional($transaction->user)->first_name;

        }

    @endphp


    {{--======================================================
        TRANSACTION ROW
    =======================================================--}}

    <tr

        class="transaction-row"

        data-type="{{ $rowClass }}"

    >

        <td colspan="5" class="transaction-indent">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <span class="badge {{ $badgeClass }}">

                        {{ $badgeText }}

                    </span>

                    &nbsp;

                    <strong>

                        {{ $transaction->transaction_date->format('d M Y') }}

                    </strong>

                    &nbsp;

                    Ref:

                    <strong>

                        {{ $reference }}

                    </strong>

                    @if($remarks)

                        <br>

                        <small class="text-muted">

                            {{ $remarks }}

                        </small>

                    @endif

                    @if($receivedBy)

                        <br>

                        <small class="text-muted">

                            Received By:

                            {{ $receivedBy }}

                        </small>

                    @endif

                </div>

                <div class="text-end">

                    <h6 class="mb-0 text-success">

                        {{ number_format($amount,2) }}

                    </h6>

                </div>

            </div>

        </td>

    </tr>

@endforeach

@endforeach

</tbody>

<tfoot>

<tr class="table-light">

    <th class="text-end">

        Invoice Totals

    </th>

    <th class="text-end">

        {{ number_format($invoice->items->sum('amount'),2) }}

    </th>

    <th class="text-end text-success">

        {{ number_format($invoice->items->sum('paid_amount'),2) }}

    </th>

    <th class="text-end text-danger">

        {{ number_format($invoice->items->sum('balance'),2) }}

    </th>

    <th></th>

</tr>

</tfoot>

</table>

@endforeach
{{--==========================================================
    PART 4
    STATEMENT TOTALS + FOOTER + FILTERS
===========================================================--}}

{{--=========================================
    GRAND TOTALS
==========================================--}}

<div class="statement-total">

    <table class="table table-borderless mb-0">

        <tbody>

            <tr>

                <td width="70%">

                    <strong>Total Charges</strong>

                </td>

                <td class="text-end">

                    {{ number_format($summary['total_invoiced'],2) }}

                </td>

            </tr>

            <tr>

                <td>

                    <strong>Total Payments</strong>

                </td>

                <td class="text-end text-success">

                    {{ number_format($summary['total_paid'],2) }}

                </td>

            </tr>

            <tr>

                <td>

                    <strong>Credits Applied</strong>

                </td>

                <td class="text-end text-primary">

                    {{ number_format($summary['credits'] ?? 0,2) }}

                </td>

            </tr>

            <tr>

                <td>

                    <strong>Manual Adjustments</strong>

                </td>

                <td class="text-end text-warning">

                    {{ number_format($summary['adjustments'] ?? 0,2) }}

                </td>

            </tr>

            <tr class="border-top">

                <td>

                    <h4 class="mb-0">

                        Outstanding Balance

                    </h4>

                </td>

                <td class="text-end">

                    <h4 class="grand-total text-danger mb-0">

                        {{ number_format($summary['balance'],2) }}

                    </h4>

                </td>

            </tr>

        </tbody>

    </table>

</div>


{{--=========================================
    FOOTER
==========================================--}}

<div class="mt-5 pt-4 border-top">

    <div class="row">

        <div class="col-md-6">

            <strong>Prepared By</strong>

            <br>

            Finance Office

            <br><br>

            ___________________________

        </div>

        <div class="col-md-6 text-end">

            <strong>Date Printed</strong>

            <br>

            {{ now()->format('d M Y H:i') }}

            <br><br>

            This is a computer generated statement.

            <br>

            No signature is required.

        </div>

    </div>

</div>

</div> {{-- END statement-paper --}}

</div> {{-- END container --}}

{{--==========================================================
    FILTER SCRIPT
===========================================================--}}

<script>

document.addEventListener("DOMContentLoaded", function () {

    const buttons = document.querySelectorAll(".filter-btn");

    buttons.forEach(function(btn){

        btn.addEventListener("click", function(){

            buttons.forEach(function(b){

                b.classList.remove("active");

            });

            this.classList.add("active");

            let type = this.dataset.filter;

            let rows = document.querySelectorAll(".transaction-row");

            rows.forEach(function(row){

                if(type === "all"){

                    row.style.display = "";

                    return;

                }

                if(row.dataset.type === type){

                    row.style.display = "";

                }else{

                    row.style.display = "none";

                }

            });

        });

    });

});

</script>


{{--==========================================================
    OPTIONAL PDF BUTTON
===========================================================--}}

<script>

async function downloadPDF() {

    const element = document.getElementById('pdf-content');

    if (!element) {
        alert('PDF content not found.');
        return;
    }

    // Apply PDF styling
    element.classList.add('pdf-mode');

    const schoolName = @json($school['school_name'] ?? '');
    const studentName = @json(trim($student->user->first_name . ' ' . $student->user->last_name));
    const admissionNo = @json($student->admission_no);
    const className = @json(optional(optional($student->class_section)->class)->name ?? '');
    const today = "{{ now()->format('Y-m-d') }}";

    const clean = str => String(str ?? '')
        .replace(/[<>:"/\\|?*]+/g, '')
        .replace(/\s+/g, '_');

    const filename =
        `${clean(schoolName)}_${clean(studentName)}_${clean(admissionNo)}_${clean(className)}_${today}_Fee_Statement.pdf`;

    try {

        await html2pdf().set({

            margin: [5, 5, 5, 5],

            filename: filename,

            image: {
                type: 'jpeg',
                quality: 0.90
            },

            html2canvas: {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false
            },

            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait',
                compress: true
            },

            pagebreak: {
                mode: ['avoid-all', 'css', 'legacy']
            }

        }).from(element).save();

    } finally {

        // Restore screen styling
        element.classList.remove('pdf-mode');

    }

}


</script>

@endsection
