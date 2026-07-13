@extends('layouts.master')

@section('title','Invoice')

@section('content')

<div class="container">

    <div class="card">

        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">

    <div>
        <h3 class="mb-0">
            <i class="fa fa-file-text-o text-primary mr-2"></i>
            <h3>Invoice {{ $invoice->invoice_no }}</h3>
        </h3>
        <small class="text-muted">
            Complete Invoice History
        </small>
    </div>

    <div>

        {{-- Go to Previous Page --}}
        <a href="{{ url()->previous() }}"
           class="btn btn-outline-secondary mr-2">

            <i class="fa fa-arrow-left"></i>
            Back

        </a>

        {{-- Go to Dashboard --}}
        <a href="{{ route('fees.paid.index') }}"
           class="btn btn-primary">

            <i class="fa fa-home"></i>
            Dashboard

        </a>

    </div>

</div>


        <div class="card-body">

            <h5>{{ $invoice->student->user->full_name ?? $invoice->student->full_name }}</h5>

            <p>
                Status:
                <span class="badge badge-{{ $invoice->status=='paid' ? 'success' : ($invoice->status=='partial' ? 'warning' : 'danger') }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </p>

            <table class="table table-bordered">

                <thead>

                    <tr>
                        <th>Fee Type</th>
                        <th class="text-right">Amount</th>
                    </tr>

                </thead>

                <tbody>

                @foreach($invoice->items as $item)

                    <tr>

                        <td>{{ $item->feeType->name }}</td>

                        <td class="text-right">
                            {{ number_format($item->amount,2) }}
                        </td>

                    </tr>

                @endforeach

                </tbody>

                <tfoot>

                    <tr>

                        <th>Total</th>

                        <th class="text-right">
                            {{ number_format($invoice->total_amount,2) }}
                        </th>

                    </tr>

                    <tr>

                        <th>Paid</th>

                        <th class="text-right text-success">
                            {{ number_format($invoice->paid_amount,2) }}
                        </th>

                    </tr>

                    <tr>

                        <th>Balance</th>

                        <th class="text-right text-danger">
                            {{ number_format($invoice->balance,2) }}
                        </th>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

</div>

@endsection