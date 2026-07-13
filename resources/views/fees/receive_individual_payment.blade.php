@extends('layouts.master')

@section('title','Record Student Payment')

@section('content')

@section('content')

<div class="container-fluid">

    <form action="{{ route('fees.paid.receive.payment.manual.store') }}" method="POST" enctype="multipart/form-data">

        @csrf

        <input type="hidden"
               name="student_id"
               value="{{ $student->id }}">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-0 text-success">
                            <i class="fa fa-money mr-2"></i>
                            Receive Cheque Payment
                        </h4>

                        <small class="text-muted">
                            Record cheque details then allocate the payment to fee categories.
                        </small>

                    </div>

                    <a href="{{ route('fees.paid.receive.search.index') }}"
                       class="btn btn-outline-secondary btn-sm">

                        <i class="fa fa-arrow-left"></i>

                        Back

                    </a>

                </div>

            </div>

            <div class="card-body">

                <div class="row">

                    <!-- Student Card -->

                    <div class="col-lg-5">

                        <div class="card border shadow-sm h-100">

                            <div class="card-header bg-light py-2">

                                <strong>

                                    <i class="fa fa-user text-primary mr-2"></i>

                                    Student Details

                                </strong>

                            </div>

                            <div class="card-body py-3">

                                <div class="row small">

                                    <div class="col-4 text-muted">
                                        Student
                                    </div>

                                    <div class="col-8 font-weight-bold">
                                        {{ $student->user->first_name }}
                                        {{ $student->user->last_name }}
                                    </div>


                                    <div class="col-4 text-muted mt-2">
                                        Admission
                                    </div>

                                    <div class="col-8 mt-2">
                                        {{ $student->admission_no }}
                                    </div>


                                    <div class="col-4 text-muted mt-2">
                                        Class
                                    </div>

                                    <div class="col-8 mt-2">
                                        {{ $student->class_section->class->name }}
                                        {{ $student->class_section->section->name }}
                                    </div>


                                    <div class="col-4 text-muted mt-2">
                                        Outstanding
                                    </div>

                                    <div class="col-8 mt-2">

                                       @php
    $badgeClass = $netBalance > 0
        ? 'badge-danger'
        : ($netBalance < 0
            ? 'badge-success'
            : 'badge-secondary');
@endphp

<span class="badge {{ $badgeClass }} px-3 py-2">

    KES {{ number_format(abs($netBalance), 2) }}

    @if($netBalance < 0)
        (Credit)
    @elseif($netBalance > 0)
        (Outstanding)
    @else
        (Cleared)
    @endif

</span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Payment Card -->

                    <div class="col-lg-7">

                        <div class="card border shadow-sm">

                            <div class="card-header bg-light py-2">

                                <strong>

                                    <i class="fa fa-credit-card text-success mr-2"></i>

                                    Cheque Details

                                </strong>

                            </div>

                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-6 mb-3">

                                        <label class="small font-weight-bold">

                                            Cheque Number

                                        </label>

                                        <input type="text"
                                               name="cheque_no"
                                               class="form-control form-control-sm"
                                               required>

                                    </div>

                                    <div class="col-md-6 mb-3">

                                        <label class="small font-weight-bold">

                                            Cheque Date

                                        </label>

                                        <input type="date"
                                               name="cheque_date"
                                               value="{{ date('Y-m-d') }}"
                                               class="form-control form-control-sm"
                                               required>

                                    </div>

                                    <div class="col-md-6 mb-3">

                                        <label class="small font-weight-bold">

                                            Cheque Amount

                                        </label>

                                        <input type="number"
                                               step="0.01"
                                               min="1"
                                               id="chequeAmount"
                                               name="amount"
                                               class="form-control form-control-sm"
                                               required>

                                    </div>

                                    <div class="col-md-6 mb-3">

                                        <label class="small font-weight-bold">

                                            Received By

                                        </label>

                                        <input type="text"
                                               class="form-control form-control-sm"
                                               name="received_by"
                                               value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}"
                                               readonly>

                                    </div>

                                    <div class="col-md-12">

                                        <label class="small font-weight-bold">

                                            Bank (Optional)

                                        </label>

                                        <input type="text"
                                               name="bank_name"
                                               class="form-control form-control-sm">

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <hr class="my-4">
                                <!-- Allocation Card -->

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">

                            <i class="fa fa-list text-primary mr-2"></i>

                            Allocate Payment

                        </h5>

                        <small class="text-muted">

                            Allocate the cheque amount to the appropriate fee categories.

                        </small>

                    </div>
                    @if($feeItems->count() > 0)

                    <div class="card-body p-0">

                        <table class="table table-hover table-sm mb-0">

                            <thead class="bg-light">

                                <tr>

                                    <th style="width:45%;">
                                        Fee Category
                                    </th>

                                    <th class="text-right">
                                        Outstanding
                                    </th>

                                    <th style="width:180px;">
                                        Allocate
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($feeItems as $item)

                                <tr>

                                    <td>

                                        <strong>

                                            {{ optional($item->fee)->name }}

                                        </strong>

                                    </td>

                                    <td class="text-right text-danger">

                                        {{ number_format($item->balance,2) }}

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="{{ $item->balance }}"
                                            value="0"
                                            class="form-control form-control-sm allocation"
                                            name="allocations[{{ $item->fees_id }}]">

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td colspan="3">

                                        <div class="alert alert-success mb-0 text-center">

                                            Student has no outstanding balances.

                                        </div>

                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    @endif

                </div>

                <div class="row mt-3">

                    <div class="col-md-4">

                        <div class="card bg-light border-0">

                            <div class="card-body py-2 text-center">

                                <small class="text-muted">

                                    Cheque Amount

                                </small>

                                <h5 class="mb-0 text-primary" id="displayCheque">

                                    0.00

                                </h5>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="card bg-light border-0">

                            <div class="card-body py-2 text-center">

                                <small class="text-muted">

                                    Total Allocated

                                </small>

                                <h5 class="mb-0 text-success" id="displayAllocated">

                                    0.00

                                </h5>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="card bg-light border-0">

                            <div class="card-body py-2 text-center">

                                <small class="text-muted">

                                    Remaining

                                </small>

                                <h5 class="mb-0 text-danger" id="displayRemaining">

                                    0.00

                                </h5>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="mt-4 text-right">

                    <a href="{{ url()->previous() }}"
                       class="btn btn-light">

                        Cancel

                    </a>

                    <button
                        type="submit"
                        class="btn btn-success ml-2"
                        id="saveBtn">

                        <i class="fa fa-save mr-1"></i>

                        Save Payment

                    </button>

                </div>

            </div>

        </div>

    </form>

</div>


<script>

$(function () {


    function autoAllocateCheque() {

        let cheque = parseFloat($("#chequeAmount").val()) || 0;

        let remaining = cheque;


        $(".allocation").each(function () {

            let max = parseFloat($(this).attr("max")) || 0;


            if (remaining > 0) {

                let allocation = Math.min(max, remaining);


                $(this).val(allocation.toFixed(2));


                remaining -= allocation;

            } else {

                $(this).val("0.00");

            }


        });


        updateTotals();

    }



    function updateTotals() {

        let cheque = parseFloat($("#chequeAmount").val()) || 0;

        let allocated = 0;


        $(".allocation").each(function () {

            let max = parseFloat($(this).attr("max")) || 0;

            let value = parseFloat($(this).val()) || 0;


            // Prevent exceeding category balance
            if (value > max) {

                value = max;

                $(this).val(max.toFixed(2));

            }


            if (value < 0) {

                value = 0;

                $(this).val("0.00");

            }


            allocated += value;


        });


        let remaining = cheque - allocated;



        $("#displayCheque").text(
            cheque.toLocaleString(undefined,{
                minimumFractionDigits:2,
                maximumFractionDigits:2
            })
        );


        $("#displayAllocated").text(
            allocated.toLocaleString(undefined,{
                minimumFractionDigits:2,
                maximumFractionDigits:2
            })
        );


        $("#displayRemaining").text(
            remaining.toLocaleString(undefined,{
                minimumFractionDigits:2,
                maximumFractionDigits:2
            })
        );



        if (remaining < 0) {

            $("#displayRemaining")
                .removeClass("text-success")
                .addClass("text-danger");

        } else {

            $("#displayRemaining")
                .removeClass("text-danger")
                .addClass("text-success");

        }



if (
    cheque <= 0 ||
    allocated > cheque
) {

    $("#saveBtn").prop("disabled", true);

} else {

    $("#saveBtn").prop("disabled", false);

}

    }



    /*
       When cheque amount is entered:
       Automatically distribute
    */
    $("#chequeAmount").on("change keyup", function(){

        autoAllocateCheque();

    });



    /*
       If user manually changes allocation,
       stop auto behaviour and allow adjustment
    */
    $(document).on("change keyup", ".allocation", function(){

        updateTotals();

    });



    updateTotals();


});

</script>

@endsection
