@extends('layouts.master')

@section('title')
Receive Student Payment
@endsection

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h3 class="mb-0 text-success">

                        <i class="fa fa-money mr-2"></i>

                        Receive Student Payment

                    </h3>

                    <small class="text-muted">

                        Search a student by Admission Number or Name to receive payment.

                    </small>

                </div>

            </div>

        </div>

        <div class="card-body">

            <div class="row justify-content-center">

                <div class="col-lg-8">

                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" id="studentSearch"
                            placeholder="Search Admission Number or Student Name...">
                        <div class="input-group-prepend">

                            <span class="input-group-text bg-white">

                                <i class="fa fa-search text-primary"></i>

                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <div class="text-center mt-3 d-none" id="loading">

                <div class="spinner-border text-primary"></div>

                <div class="small text-muted mt-2">

                    Searching students...

                </div>

            </div>

            <hr>

            <div id="results" class="row">

                <div class="col-12">

                    <div class="alert alert-info text-center">
                        <br>

                        Begin typing an Admission Number or Student Name.

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
 let timer;

$("#studentSearch").on("keyup", function () {



    clearTimeout(timer);

    let search = $(this).val().trim();

    if (search.length < 2) {

        $("#results").html(`
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Begin typing an Admission Number or Student Name.
                </div>
            </div>
        `);

        return;
    }

    timer = setTimeout(function () {

        $("#loading").removeClass("d-none");

        $.ajax({

            url: "{{ route('fees.paid.receive.search') }}",

            type: "GET",

            data: {
                search: search
            },

            success: function (response) {

                $("#loading").addClass("d-none");

                $("#results").html(response);

            },

            error: function () {

                $("#loading").addClass("d-none");

                $("#results").html(`
                    <div class="col-12">
                        <div class="alert alert-danger text-center">
                            Unable to search students.
                        </div>
                    </div>
                `);

            }

        });

    },300);

});

</script>
@endsection