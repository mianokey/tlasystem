@extends('layouts.master')

@section('title')
    {{ __('Pay Compulsory Fees') }}
@endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            {{ __('Pay Compulsory Fees') }}
        </h3>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card search-container">
            <div class="card">
                <div class="card-body d-flex justify-content-center">

                    <form class="pt-3 create-form form-validation col-sm-12 col-md-6"
                          method="post"
                          action="{{ route('fees.compulsory.store') }}"
                          data-success-function="successFunction">

                        @csrf

                        {{-- Hidden fields --}}
                        <input type="hidden" name="fees_id" value="{{ $fees->id }}"/>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="parent_id" value="{{ $student->student->guardian_id }}"/>
                        <input type="hidden" id="total_compulsory_fees" value="{{ $fees->total_compulsory_fees }}">
                        <input type="hidden" id="remaining_amount" value="{{ $fees->remaining_amount }}">

                        <h4>
                            {{ $student->full_name }} :- {{ $student->student->class_section->full_name }}
                        </h4>

                        <hr>

                        {{-- Date --}}
                        <div class="form-group">
                            <label for="payment-date">{{ __('Date') }} <span class="text-danger">*</span></label>
                            <input id="payment-date"
                                   type="text"
                                   name="date"
                                   class="datepicker-popup form-control"
                                   required>
                        </div>

                        {{-- Fee Breakdown --}}
                        <div class="form-group">
                            <table class="table">
                                <tbody>
                                @foreach($fees->compulsory_fees as $fee)
                                    <tr>
                                        <td>{{ $fee->fees_type_name }}</td>
                                        <td class="text-right">
                                            {{ $fee->amount.' '.$currencySymbol }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        {{-- PAYMENT INPUT --}}
                        <div class="form-group">
                            <label for="payment_amount">
                                {{ __('Enter Payment Amount') }}
                            </label>

                            <input type="number"
                                   id="payment_amount"
                                   name="payment_amount"
                                   class="form-control"
                                   min="0"
                                   max="{{ $fees->remaining_amount }}"
                                   placeholder="Enter amount to pay"
                                   required>
                        </div>

                        {{-- BALANCE DISPLAY --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong>Current Balance:</strong><br>
                                    <span id="current_balance">
                                        {{ $fees->remaining_amount }}
                                    </span> {{ $currencySymbol }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong>New Balance:</strong><br>
                                    <span id="new_balance">
                                        {{ $fees->remaining_amount }}
                                    </span> {{ $currencySymbol }}
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- MODE (Cash disabled) --}}
                        <div class="form-group">
                            <label>{{ __('Mode') }}</label><br>

                            <div class="form-check form-check-inline">
                                <input type="radio"
                                       name="mode"
                                       value="1"
                                       class="form-check-input"
                                       disabled>
                                <label class="form-check-label text-muted">
                                    Cash (Disabled)
                                </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input type="radio"
                                       name="mode"
                                       value="2"
                                       class="form-check-input"
                                       checked>
                                <label class="form-check-label">
                                    Cheque
                                </label>
                            </div>
                        </div>

                        {{-- Cheque No --}}
                        <div class="form-group cheque-no-container">
                            <label for="Bank Ref Number">{{ __('Bank Ref Number') }}</label>
                            <input type="text"
                                   name="cheque_no"
                                   id="cheque_no"
                                   class="form-control"
                                   placeholder="Bank Reference Number">
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-primary">
                            {{ __('Pay') }}
                        </button>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('js')
<script>
    // Date picker
    $('#payment-date').datepicker({
        format: "dd-mm-yyyy",
        rtl: isRTL()
    }).datepicker("setDate", 'now');

    // Balance calculation
    const remaining = parseFloat($('#remaining_amount').val()) || 0;

    $('#payment_amount').on('input', function () {
        let pay = parseFloat($(this).val()) || 0;

        if (pay > remaining) {
            pay = remaining;
            $(this).val(pay);
        }

        let newBalance = remaining - pay;

        $('#new_balance').text(newBalance.toFixed(2));
    });

    function successFunction() {
        window.location.href = "{{ route('fees.paid.index') }}";
    }
</script>
@endsection