@extends('layouts.master')

@section('title')
{{ __('Fees Dashboard') }}
@endsection

@section('content')

<style>
    .stat-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        transition: all .2s ease-in-out;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .stat-title {
        font-size: 12px;
        color: #6c757d;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 700;
        margin-top: 5px;
    }

    .soft-scroll {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 10px;
    }


    .page-title {
        font-weight: 600;
    }

    .soft-scroll {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .soft-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .soft-scroll::-webkit-scrollbar-thumb {
        background: #d6d6d6;
        border-radius: 20px;
    }

    .fee-chip {
        min-width: 360px;
        border-radius: 18px;
        padding: 20px;
        background: #fff;
        border: 1px solid #eef1f6;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        transition: .3s;
    }

    .fee-chip:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 35px rgba(0, 0, 0, .10);
    }

    .fee-name {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .amount-label {
        font-size: 12px;
        text-transform: uppercase;
        color: #8b8b8b;
        font-weight: 600;
    }

    .amount-value {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    /* ===========================
       Circular Progress
       ===========================*/

    .progress-circle {
        position: relative;
        width: 95px;
        height: 95px;
        margin: auto;
    }

    .progress-circle svg {
        transform: rotate(-90deg);
    }

    .progress-circle circle {
        fill: none;
        stroke-width: 9;
    }

    .progress-circle .track {
        stroke: #edf2f7;
    }

    .progress-circle .indicator {
        stroke: currentColor;
        stroke-linecap: round;
        transition: stroke-dashoffset .8s ease;
    }

    .progress-value {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
    }

    .status-badge {
        margin-top: 8px;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
    }

    .progress {
        height: 22px;
        border-radius: 30px;
        overflow: hidden;
        background: #edf2f7;
    }

    .progress-bar {
        font-weight: 700;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .collection-label {
        font-size: 12px;
        color: #888;
        margin-bottom: 6px;
        font-weight: 600;
    }
</style>

<div class="content-wrapper">

    {{-- HEADER --}}
    <div class="page-header mb-3">
        <h3 class="page-title">{{ __('Fees Dashboard') }}</h3>
    </div>

    @php
    $invoiced = $stats['total_invoiced'] ?? 0;
    $paid = $stats['total_paid'] ?? 0;
    $pending = $stats['total_pending'] ?? 0;
    $rate = $stats['collection_rate'] ?? 0;
    $fullyPaid = $stats['fully_paid'] ?? 0;
    $defaulters = $stats['defaulters'] ?? 0;
    @endphp

    {{-- ================= MAIN STATS ================= --}}
    <div class="row">

        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card p-3">
                <div class="stat-title">Invoiced</div>
                <div class="stat-value">{{ $invoiced }}</div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card p-3">
                <div class="stat-title">Collected</div>
                <div class="stat-value text-success">{{ $paid }}</div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card p-3">
                <div class="stat-title">Outstanding</div>
                <div class="stat-value text-danger">{{ $pending }}</div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card p-3">
                <div class="stat-title">Collection Rate</div>
                <div class="stat-value">{{ $rate }}%</div>
            </div>
        </div>

    </div>

    {{-- ================= SECONDARY STATS ================= --}}
    <div class="row mb-3">

        <div class="col-md-3 col-6 mb-2">
            <div class="card stat-card p-2 text-center">
                <div class="stat-title">Invoice Coverage</div>
                <div class="stat-value">
                    {{ $invoiced > 0 ? round(($paid / $invoiced) * 100, 2) : 0 }}%
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-2">
            <div class="card stat-card p-2 text-center">
                <div class="stat-title">Fully Paid</div>
                <div class="stat-value">{{ $fullyPaid }}</div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-2">
            <div class="card stat-card p-2 text-center">
                <div class="stat-title">Defaulters</div>
                <div class="stat-value">{{ $defaulters }}</div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-2">
            <div class="card stat-card p-2 text-center">
                <div class="stat-title">Overdue</div>
                <div class="stat-value">{{ $pending }}</div>
            </div>
        </div>

    </div>

    {{-- ================= FEE BREAKDOWN ================= --}}
    <div class="card stat-card p-3 mb-3">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0 fw-bold">
                <i class="mdi mdi-chart-donut text-primary"></i>
                Fee Breakdown
            </h5>

            <small class="text-muted">
                Collection Performance by Fee Type
            </small>

        </div>

        <div class="soft-scroll">

            @foreach($feeBreakdown as $fee)

            @php

            $percentage = $fee->invoiced > 0
            ? round(($fee->paid / $fee->invoiced) * 100)
            : 0;

            if($percentage >= 95){

            $color='success';
            $status='Excellent';
            $icon='mdi-check-circle';

            }elseif($percentage >= 80){

            $color='primary';
            $status='Good';
            $icon='mdi-thumb-up';

            }elseif($percentage >= 60){

            $color='info';
            $status='Okay';
            $icon='mdi-information';

            }elseif($percentage >= 40){

            $color='warning';
            $status='Needs Attention';
            $icon='mdi-alert';

            }else{

            $color='danger';
            $status='Critical';
            $icon='mdi-alert-circle';

            }

            $radius = 38;
            $circumference = 2 * pi() * $radius;
            $offset = $circumference - ($circumference * $percentage / 100);

            @endphp

            <div class="fee-chip">

                <div class="row">

                    <div class="col-7">

                        <div class="fee-name">
                            {{ $fee->name }}
                        </div>

                        <div class="amount-label">
                            Invoiced
                        </div>

                        <div class="amount-value">
                            {{ number_format($fee->invoiced,2) }}
                        </div>

                        <div class="amount-label text-success">
                            Paid
                        </div>

                        <div class="amount-value text-success">
                            {{ number_format($fee->paid,2) }}
                        </div>

                        <div class="amount-label text-danger">
                            Balance
                        </div>

                        <div class="amount-value text-danger">
                            {{ number_format($fee->balance,2) }}
                        </div>

                    </div>

                    <div class="col-5 text-center">

                        <div class="progress-circle text-{{ $color }}">

                            <svg width="95" height="95">

                                <circle class="track" cx="47.5" cy="47.5" r="{{ $radius }}">
                                </circle>

                                <circle class="indicator" cx="47.5" cy="47.5" r="{{ $radius }}"
                                    stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}">
                                </circle>

                            </svg>

                            <div class="progress-value">

                                {{ $percentage }}%

                            </div>

                        </div>

                        <span class="badge bg-{{ $color }} status-badge">

                            <i class="mdi {{ $icon }}"></i>

                            {{ $status }}

                        </span>

                    </div>

                </div>

                <hr>

                <div class="collection-label">

                    Collection Progress

                </div>

                <div class="progress">

                    <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percentage }}%;">

                        {{ $percentage }}%

                    </div>

                </div>

            </div>

            @endforeach

        </div>

    </div>

    {{-- ================= FILTER + TABLE ================= --}}
    <div class="card stat-card p-3">

        <div id="toolbar">

            <div class="row">

                <div class="col-md-4 mb-2">
                    <label class="stat-title">Session Year</label>
                    <select id="session_year_id" class="form-control">
                        @foreach ($session_year_all as $session_year)
                        <option value="{{ $session_year->id }}" {{ $session_year->default ? 'selected' : '' }}>
                            {{ $session_year->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-2">
                    <label class="stat-title">Fees</label>
                    <select id="filter_fees_id" class="form-control"></select>
                </div>

                <div class="col-md-4 mb-2">
                    <label class="stat-title">Status</label>
                    <select id="filter_paid_status" class="form-control">
                        <option value="0">Unpaid</option>
                        <option value="1">Paid</option>
                    </select>
                </div>

            </div>

        </div>

        <div class="table-responsive mt-3 mb-5">


            <table id="table_list" class="table table-hover" data-toggle="table"
                data-url="{{ route('fees.paid.list', 1) }}" data-side-pagination="server" data-pagination="true"
                data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                data-show-toggle="true" data-show-export="true" data-export-data-type="all"
                data-page-list="[5, 10, 20, 50, 100, 200]" data-sort-name="id" data-query-params="queryParams"
                data-sort-order="desc">

                <thead>
                    <tr>
                        <th data-field="invoice_no">Invoice No</th>

                        <th data-field="student_name">Student</th>

                        <th data-field="admission_no">Admission No</th>

                        <th data-field="class">Class</th>

                        <th data-field="fees">Fees</th>

                        <th data-field="fee_types">Fee Types</th>

                        <th data-field="total_amount">Total Amount</th>

                        <th data-field="paid_amount">Paid Amount</th>

                        <th data-field="balance">Balance</th>

                        <th data-field="due_date">Due Date</th>

                        <th data-field="status" data-escape="false">
                            Status
                        </th>

                        <th data-field="operate" data-escape="false">
                            Action
                        </th>
                    </tr>
                </thead>

            </table>

        </div>


    </div>

</div>


<script>
    function queryParams(params) {
    return {
        limit: params.limit,
        offset: params.offset,
        search: params.search,
        sort: params.sort,
        order: params.order,

        session_year_id: $('#session_year_id').val() || '',
        fees_id: $('#filter_fees_id').val() || '',
        paid_status: $('#filter_paid_status').val() || ''
    };
}

function refreshTable() {
    $('#table_list').bootstrapTable('refresh', {
        silent: true
    });
}

function loadFees() {

    let sessionYearId = $('#session_year_id').val();

    $.get("{{ route('fees.search') }}", {
        session_year_id: sessionYearId
    }, function (res) {

        let fees = res.data ?? res;

        $('#filter_fees_id').html('<option value="">All Fees</option>');

        fees.forEach(function (fee) {
            $('#filter_fees_id').append(
                `<option value="${fee.id}">${fee.name}</option>`
            );
        });

    });

}

$(function () {

    // initial load
    loadFees();

    // ALL filter changes → one refresh only
    $('#session_year_id, #filter_fees_id, #filter_paid_status')
        .on('change', function () {

            if ($(this).attr('id') === 'session_year_id') {
                loadFees();
            }

            refreshTable();
        });

});

</script>

@endsection