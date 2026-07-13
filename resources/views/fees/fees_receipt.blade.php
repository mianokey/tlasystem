<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<style>
    @page {
        size: A5;
        margin: 10mm;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
    }

    .header {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        padding-bottom:10px;
        margin-bottom:10px;
    }

    .left {
        width:55%;
        display:flex;
        gap:10px;
        align-items:flex-start;
    }

    .right {
        width:45%;
        text-align:right;
        font-size:11px;
        line-height:1.5;
    }

    .school-name {
        font-size:14px;
        font-weight:bold;
    }

    table {
        width:100%;
        border-collapse:collapse;
        font-size:10px;
    }

    th, td {
        border:1px dotted #6a6a6a;
        padding:4px;
    }

    th {
        background:#f2f2f2;
    }

    .text-right {
        text-align:right;
    }

    .text-center {
        text-align:center;
    }

    .no-border td {
        border:none;
    }
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">

    <!-- HEADER -->
<div class="container-fluid" style="padding:0; margin-bottom:10px; border-bottom:1px solid #000; padding-bottom:8px;">

    <div class="row">

        <!-- LEFT SIDE -->
        <div class="col-6" style="display:flex; gap:10px; align-items:flex-start;">

            <!-- LOGO -->
            <div>
                @if(!empty($schoolVerticalLogo))
                    @if ($schoolVerticalLogo->getRawOriginal('data') && Storage::disk('public')->exists($schoolVerticalLogo->getRawOriginal('data')))
                        <img style="height:60px;" src="{{public_path('storage/'.$schoolVerticalLogo->getRawOriginal('data'))}}">
                    @else
                        <img style="height:60px;" src="{{public_path('assets/horizontal-logo.svg')}}">
                    @endif
                @else
                    <img style="height:60px;" src="{{public_path('storage/'.$systemVerticalLogo->getRawOriginal('data'))}}">
                @endif
            </div>

            <!-- SCHOOL INFO -->
            <div>
                <div style="font-size:14px; font-weight:bold;">
                    {{ $school['school_name'] ?? '' }}
                </div>
                <div style="font-size:11px;">
                    {{ $school['school_address'] ?? '' }}
                </div>
                <div style="margin-top:5px; font-weight:bold;">
                    FEE STATE
                </div>
            </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="col-6" style="text-align:right; font-size:11px; line-height:1.6;">

            <div><strong>Invoice No:</strong> {{ $feesPaid->id ?? '' }}</div>
            <div><strong>Date:</strong> {{ date('d-m-Y') }}</div>

            <div style="margin:5px 0;"></div>

            <div><strong>Student:</strong> {{ $student->user->full_name }}</div>
            <div><strong>Class:</strong> {{ $student->class_section->full_name ?? '' }}</div>

        </div>

    </div>

</div>

</div>

<!-- LEDGER TABLE -->
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Credit</th>
            <th class="text-right">Balance</th>
        </tr>
    </thead>

    <tbody>

@php
    $balance = 0;
    $rows = [];

    // 1. FEES (INVOICES)
    foreach ($feesPaid->fees->compulsory_fees ?? [] as $fee) {
        $rows[] = [
            'date' => $fee->created_at ?? now(),
            'desc' => $fee->fees_type_name . ' (Invoice)',
            'debit' => $fee->amount,
            'credit' => 0
        ];
    }

    // 2. PAYMENTS
    foreach ($feesPaid->compulsory_fee ?? [] as $pay) {
        $rows[] = [
            'date' => $pay->date,
            'desc' => 'Payment - ' . $pay->mode,
            'debit' => 0,
            'credit' => $pay->amount + ($pay->due_charges ?? 0)
        ];
    }

    // 3. OPTIONAL FEES
    foreach ($feesPaid->optional_fee ?? [] as $opt) {
        $rows[] = [
            'date' => $opt->date,
            'desc' => $opt->fees_class_type->fees_type_name . ' (Optional)',
            'debit' => $opt->amount,
            'credit' => 0
        ];
    }

    // SORT BY DATE (IMPORTANT FOR YOUR REQUIREMENT)
    usort($rows, fn($a,$b) => strtotime($a['date']) <=> strtotime($b['date']));
@endphp

@foreach($rows as $row)

@php
    $balance += $row['debit'];
    $balance -= $row['credit'];
@endphp

<tr>
    <td>{{ date('d-m-Y', strtotime($row['date'])) }}</td>
    <td>{{ $row['desc'] }}</td>

    <td class="text-right">
        {{ $row['debit'] > 0 ? number_format($row['debit'],2) : '-' }}
    </td>

    <td class="text-right">
        {{ $row['credit'] > 0 ? number_format($row['credit'],2) : '-' }}
    </td>

    <td class="text-right">
        {{ number_format($balance,2) }}
    </td>
</tr>

@endforeach

    </tbody>
</table>

<br>

<!-- FINAL BALANCE -->
<table class="no-border">
    <tr>
        <td class="text-right">
            <strong>FINAL BALANCE:</strong>
            {{ number_format($balance,2) }} {{ $school['currency_symbol'] ?? '' }}
        </td>
    </tr>
</table>

</body>
</html>