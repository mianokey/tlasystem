@if($students->count())

@foreach($students as $student)

@php
$outstanding = $student->outstanding_balance ?? 0;
@endphp

<div class="col-12 mb-2">

    <div class="card border-0 shadow-sm">

        <div class="card-body py-2 px-3">

            <div class="row align-items-center">

                <!-- Student -->
                <div class="col-lg-4 col-md-3">

                    <div class="d-flex align-items-center">

                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center mr-3"
                            style="width:45px;height:45px;font-size:18px;">

                            <i class="fa fa-user"></i>

                        </div>

                        <div>

                            <div class="font-weight-bold mb-0">
                                {{ $student->user->first_name }}
                                {{ $student->user->last_name }}
                            </div>

                            <small class="text-muted">
                                {{ $student->admission_no }}
                                •
                                {{ optional($student->class_section->class)->name }}
                                {{ optional($student->class_section->section)->name }}
                            </small>

                        </div>

                    </div>

                </div>

                <!-- Summary -->
                <div class="col-lg-5 col-md-6">

                    <div class="row text-center">

                        <div class="col">
                            <small class="text-muted d-block">Invoices</small>
                            <strong>{{ $student->open_invoices_count }}</strong>
                        </div>

                        <div class="col">
                            <small class="text-muted d-block">Outstanding</small>
                            <strong class="text-danger">
                                {{ number_format($outstanding,2) }}
                            </strong>
                        </div>

                        <div class="col">
                            <small class="text-muted">
                                Credit Balance
                            </small>

                            <h6 class="text-success mb-0">

                                <i class="fa fa-wallet mr-1"></i>

                                {{ number_format($student->credit_balance ?? 0,2) }}

                            </h6>

                        </div>

                        <div class="col">
                            <small class="text-muted">
                                Paid
                            </small>

                            <h6 class="text-success mb-0">

                                <i class="fa fa-wallet mr-1"></i>

                                {{ number_format($student->total_paid ?? 0,2) }}

                            </h6>

                        </div>

                        <div class="col">
                            <small class="text-muted text-info">
                                Net Balance
                            </small>

                            @php
                            $netBalance = ($student->credit_balance ?? 0) - ($student->outstanding_balance ?? 0);
                            @endphp

                            <h6 class="mb-0 {{ $netBalance >= 0 ? 'text-success' : 'text-danger' }}">

                                KES {{ number_format(abs($netBalance), 2) }}
                            </h6>

                        </div>






                    </div>

                </div>

                <!-- Button -->
                <div class="col-lg-3 col-md-3 text-md-right mt-2 mt-md-0">

                    <a href="{{ url('/fees/payments/'.$student->id.'/receive') }}" class="btn btn-success btn-sm">

                        <i class="fa fa-money mr-1"></i>

                        Receive Payment

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

@endforeach

@else

<div class="col-12">

    <div class="alert alert-warning text-center mb-0">

        No matching students found.

    </div>

</div>

@endif