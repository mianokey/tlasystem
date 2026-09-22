@extends('layouts.master')

@section('title')
    {{ __('students') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('students') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('students') }}
                        </h4>
                        <div class="row" id="toolbar">
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('Class Section') }} <span class="text-danger">*</span></label>
                                <select name="filter_class_section_id" id="filter_class_section_id" class="form-control">
                                    <option value="">{{ __('select_class_section') }}</option>
                                    @foreach ($class_sections as $class_section)
                                        <option value={{ $class_section->id }}>{{$class_section->full_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('Session Year') }} <span class="text-danger">*</span></label>
                                <select name="filter_session_year_id" id="filter_session_year_id" class="form-control">
                                    @foreach ($sessionYears as $sessionYear)
                                        <option value={{ $sessionYear->id }} {{$sessionYear->default==1?"selected":""}}>{{$sessionYear->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @can('student-delete')
                                <div class="form-group col-12">
                                    <button id="update-status" class="btn btn-secondary" disabled><span class="update-status-btn-name">{{ __('Inactive') }}</span></button>
                                </div>
                            @endcan
                        </div>

                        @can('student-delete')
                            <div class="col-12 mt-4 text-right">
                                <b><a href="#" class="student-list-type active mr-2" data-value="active">{{__('active')}}</a></b> | <a href="#" class="ml-2 student-list-type" data-value="inative">{{__("Inactive")}}</a>
                            </div>
                        @endcan
                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table table-responsive' id='table_list'
                                       data-toggle="table" data-url="{{ route('students.show',[1]) }}" data-click-to-select="true"
                                       data-side-pagination="server" data-pagination="true"
                                       data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                       data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-fixed-columns="true" data-fixed-number="2" data-fixed-right-number="1"
                                       data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                       data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all' data-show-export="true"
                                       data-export-options='{ "fileName": "students-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}' data-query-params="studentDetailsQueryParams"
                                       data-check-on-init="true" data-escape="true">
                                    <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                        <th scope="col" data-field="no">{{ __('no.') }}</th>
                                        <th scope="col" data-field="user.id" data-visible="false">{{ __('User Id') }}</th>
                                        <th scope="col" data-field="user.full_name">{{ __('name') }}</th>
                                        <th scope="col" data-field="user.dob" data-formatter="dateFormatter">{{ __('dob') }}</th>
                                        <th scope="col" data-field="user.image" data-formatter="imageFormatter">{{ __('image') }}</th>
                                        <th scope="col" data-field="class_section.full_name">{{ __('class_section') }}</th>
                                        <th scope="col" data-field="admission_no"> {{ __('Gr Number') }}</th>
                                        <th scope="col" data-field="roll_number">{{ __('roll_no') }}</th>
                                        <th scope="col" data-field="user.gender">{{ __('gender') }}</th>
                                        <th scope="col" data-field="admission_date" data-formatter="dateFormatter">{{ __('admission_date') }}</th>
                                        <th scope="col" data-field="guardian.email">{{ __('guardian') . ' ' . __('email') }}</th>
                                        <th scope="col" data-field="guardian.full_name">{{ __('guardian') . ' ' . __('name') }}</th>
                                        <th scope="col" data-field="guardian.mobile">{{ __('guardian') . ' ' . __('mobile') }}</th>
                                        <th scope="col" data-field="guardian.gender">{{ __('guardian') . ' ' . __('gender') }}</th>
                                        @canany(['student-edit','student-delete'])
                                            <th data-events="studentEvents" class="align-button text-center" scope="col" data-field="operate" data-escape="false">{{ __('action') }}</th>
                                        @endcanany
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




{{-- EDIT SECTION STARTS HERE --}}

@can('student-edit')
<div class="modal fade"
     id="editModal"
     data-backdrop="static"
     tabindex="-1"
     role="dialog"
     aria-labelledby="editStudentModalLabel"
     aria-hidden="true">

    <div style="overflow-y: auto" class="modal-dialog modal-xl modal-dialog-centered student-edit-dialog" role="document">

        <div class="modal-content student-edit-modal border-0 shadow-lg">

            {{-- HEADER --}}
            <div class="modal-header bg-white border-bottom px-4 py-3 student-edit-header">

                <div class="d-flex align-items-center">

                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3"
                         style="width:46px;height:46px;">
                        <i class="fa fa-user-edit"></i>
                    </div>

                    <div>
                        <h4 class="modal-title font-weight-bold mb-1"
                            id="editStudentModalLabel">
                            {{ __('Edit Student') }}
                        </h4>

                        <small class="text-muted">
                            {{ __('Update student, academic, transport and guardian information') }}
                        </small>
                    </div>

                </div>

                <button type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">
                        <i class="fa fa-times"></i>
                    </span>
                </button>

            </div>


            {{-- FORM --}}
            <form id="edit-form"
                  class="edit-student-registration-form"
                  novalidate="novalidate"
                  action="{{ url('students') }}"
                  enctype="multipart/form-data">

                @csrf


                {{-- SCROLLABLE BODY --}}
                <div class="modal-body student-edit-body">


                    {{-- =====================================================
                         ACADEMIC INFORMATION
                    ====================================================== --}}
                    <div class="edit-section">

                        <div class="edit-section-heading">

                            <div class="edit-section-icon bg-primary">
                                <i class="fa fa-graduation-cap"></i>
                            </div>

                            <div>
                                <h5 class="mb-0 font-weight-bold">
                                    {{ __('Academic Information') }}
                                </h5>

                                <small class="text-muted">
                                    {{ __('Class, session and admission details') }}
                                </small>
                            </div>

                        </div>


                        <div class="card edit-card">

                            <div class="card-body">

                                <div class="row">


                                    {{-- GR NUMBER --}}
                                    <div class="form-group col-md-6 col-lg-3">

                                        <label>
                                            {{ __('Gr Number') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::text(
                                            'admission_no',
                                            null,
                                            [
                                                'placeholder' => __('Gr Number'),
                                                'class' => 'form-control',
                                                'id' => 'edit_admission_no',
                                                'readonly' => true
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- CLASS SECTION --}}
                                    <div class="form-group col-md-6 col-lg-3">

                                        <label>
                                            {{ __('Class Section') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select name="class_section_id"
                                                id="edit_class_section"
                                                class="form-control select2"
                                                required>

                                            <option value="">
                                                {{ __('select') . ' ' . __('Class') . ' ' . __('section') }}
                                            </option>

                                            @foreach ($class_sections as $class_section)

                                                <option value="{{ $class_section->id }}">
                                                    {{ $class_section->full_name }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- SESSION YEAR --}}
                                    <div class="form-group col-md-6 col-lg-3">

                                        <label>
                                            {{ __('Session Year') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select name="session_year_id"
                                                id="session_year_id"
                                                class="form-control select2"
                                                required>

                                            @foreach ($sessionYears as $sessionYear)

                                                <option value="{{ $sessionYear->id }}">
                                                    {{ $sessionYear->name }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- ADMISSION DATE --}}
                                    <div class="form-group col-md-6 col-lg-3">

                                        <label>
                                            {{ __('Admission Date') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::text(
                                            'admission_date',
                                            null,
                                            [
                                                'placeholder' => __('Admission Date'),
                                                'class' => 'datepicker-popup-no-future form-control',
                                                'id' => 'edit_admission_date',
                                                'autocomplete' => 'off'
                                            ]
                                        ) !!}

                                    </div>

                                </div>


                                {{-- STATUS --}}
                                @if(!empty($features))

                                    <div class="edit-status-box">

                                        <label class="d-block mb-2">
                                            {{ __('Status') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <div class="custom-control custom-radio custom-control-inline">

                                            <input type="radio"
                                                   class="custom-control-input"
                                                   name="status"
                                                   value="1"
                                                   id="edit_status_active">

                                            <label class="custom-control-label"
                                                   for="edit_status_active">
                                                {{ __('Active') }}
                                            </label>

                                        </div>

                                        <div class="custom-control custom-radio custom-control-inline">

                                            <input type="radio"
                                                   class="custom-control-input"
                                                   name="status"
                                                   value="0"
                                                   id="edit_status_inactive">

                                            <label class="custom-control-label"
                                                   for="edit_status_inactive">
                                                {{ __('Inactive') }}
                                            </label>

                                        </div>

                                        <div class="small text-muted mt-2">
                                            <i class="fa fa-info-circle mr-1"></i>
                                            {{ __('Activating this will consider in your current subscription cycle') }}
                                        </div>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>



                    {{-- =====================================================
                         PERSONAL INFORMATION
                    ====================================================== --}}
                    <div class="edit-section">

                        <div class="edit-section-heading">

                            <div class="edit-section-icon bg-success">
                                <i class="fa fa-user"></i>
                            </div>

                            <div>
                                <h5 class="mb-0 font-weight-bold">
                                    {{ __('Personal Information') }}
                                </h5>

                                <small class="text-muted">
                                    {{ __('Student personal and contact details') }}
                                </small>
                            </div>

                        </div>


                        <div class="card edit-card">

                            <div class="card-body">

                                <div class="row">


                                    {{-- FIRST NAME --}}
                                    <div class="form-group col-md-4">

                                        <label>
                                            {{ __('First Name') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::text(
                                            'first_name',
                                            null,
                                            [
                                                'placeholder' => __('First Name'),
                                                'class' => 'form-control',
                                                'id' => 'edit_first_name'
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- LAST NAME --}}
                                    <div class="form-group col-md-4">

                                        <label>
                                            {{ __('Last Name') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::text(
                                            'last_name',
                                            null,
                                            [
                                                'placeholder' => __('Last Name'),
                                                'class' => 'form-control',
                                                'id' => 'edit_last_name'
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- DOB --}}
                                    <div class="form-group col-md-4">

                                        <label>
                                            {{ __('Date of Birth') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::text(
                                            'dob',
                                            null,
                                            [
                                                'placeholder' => __('Date of Birth'),
                                                'class' => 'datepicker-popup-no-future form-control',
                                                'id' => 'edit_dob',
                                                'autocomplete' => 'off'
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- SCHOOL TRANSPORT --}}
                                    <div class="form-group col-md-4">

                                        <label>
                                            {{ __('School Transport') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select name="school_transport"
                                                id="edit_school_transport"
                                                class="form-control select2"
                                                required>

                                            <option value="van_a">
                                                {{ __('Uses School Van - Zone A') }}
                                            </option>

                                            <option value="van_b">
                                                {{ __('Uses School Van - Zone B') }}
                                            </option>

                                            <option value="no">
                                                {{ __('Does Not Use School Van') }}
                                            </option>

                                        </select>

                                    </div>


                                    {{-- MOBILE --}}
                                    <div class="form-group col-md-4">

                                        <label>
                                            {{ __('Mobile') }}
                                        </label>

                                        {!! Form::number(
                                            'mobile',
                                            null,
                                            [
                                                'placeholder' => __('Mobile'),
                                                'min' => 0,
                                                'class' => 'form-control remove-number-increment',
                                                'id' => 'edit_mobile'
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- GENDER --}}
                                    <div class="form-group col-md-4">

                                        <label class="d-block">
                                            {{ __('Gender') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <div class="custom-control custom-radio custom-control-inline">

                                            <input type="radio"
                                                   name="gender"
                                                   value="male"
                                                   class="custom-control-input"
                                                   id="male">

                                            <label class="custom-control-label"
                                                   for="male">
                                                {{ __('Male') }}
                                            </label>

                                        </div>

                                        <div class="custom-control custom-radio custom-control-inline">

                                            <input type="radio"
                                                   name="gender"
                                                   value="female"
                                                   class="custom-control-input"
                                                   id="female">

                                            <label class="custom-control-label"
                                                   for="female">
                                                {{ __('Female') }}
                                            </label>

                                        </div>

                                    </div>


                                    {{-- CURRENT ADDRESS --}}
                                    <div class="form-group col-md-6">

                                        <label>
                                            {{ __('Current Address') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::textarea(
                                            'current_address',
                                            null,
                                            [
                                                'required',
                                                'placeholder' => __('Current Address'),
                                                'class' => 'form-control',
                                                'rows' => 2,
                                                'id' => 'edit-current-address'
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- PERMANENT ADDRESS --}}
                                    <div class="form-group col-md-6">

                                        <label>
                                            {{ __('Permanent Address') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::textarea(
                                            'permanent_address',
                                            null,
                                            [
                                                'required',
                                                'placeholder' => __('Permanent Address'),
                                                'class' => 'form-control',
                                                'rows' => 2,
                                                'id' => 'edit-permanent-address'
                                            ]
                                        ) !!}

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>



                    {{-- =====================================================
                         STUDENT PHOTO
                    ====================================================== --}}
                    <div class="edit-section">

                        <div class="edit-section-heading">

                            <div class="edit-section-icon bg-info">
                                <i class="fa fa-camera"></i>
                            </div>

                            <div>
                                <h5 class="mb-0 font-weight-bold">
                                    {{ __('Student Photo') }}
                                </h5>

                                <small class="text-muted">
                                    {{ __('Update the student photograph') }}
                                </small>
                            </div>

                        </div>


                        <div class="card edit-card">

                            <div class="card-body">

                                <div class="row align-items-center">

                                    <div class="col-md-8">

                                        <input type="file"
                                               name="image"
                                               class="file-upload-default"/>

                                        <div class="input-group">

                                            <input type="text"
                                                   class="form-control file-upload-info"
                                                   disabled
                                                   placeholder="{{ __('Choose new image') }}"
                                                   id="edit_image"/>

                                            <span class="input-group-append">

                                                <button class="file-upload-browse btn btn-theme"
                                                        type="button">

                                                    <i class="fa fa-upload mr-1"></i>
                                                    {{ __('Upload') }}

                                                </button>

                                            </span>

                                        </div>

                                    </div>


                                    <div class="col-md-4 text-center">

                                        <img src=""
                                             id="edit-student-image-tag"
                                             class="img-fluid rounded shadow-sm"
                                             style="max-height:100px;"
                                             alt="Student">

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>



                    {{-- =====================================================
                         EXTRA FIELDS
                    ====================================================== --}}
                    @if(!empty($extraFields))

                        <div class="edit-section">

                            <div class="edit-section-heading">

                                <div class="edit-section-icon bg-warning">
                                    <i class="fa fa-list-alt"></i>
                                </div>

                                <div>
                                    <h5 class="mb-0 font-weight-bold">
                                        {{ __('Additional Information') }}
                                    </h5>

                                    <small class="text-muted">
                                        {{ __('Additional student information') }}
                                    </small>
                                </div>

                            </div>


                            <div class="card edit-card">

                                <div class="card-body">

                                    <div class="row other-details">

                                        @foreach ($extraFields as $key => $data)

                                            @php
                                                $fieldName = str_replace(' ', '_', $data->name);
                                            @endphp

                                            {{ Form::hidden(
                                                'extra_fields['.$key.'][id]',
                                                '',
                                                ['id' => $fieldName.'_id']
                                            ) }}

                                            {{ Form::hidden(
                                                'extra_fields['.$key.'][form_field_id]',
                                                $data->id
                                            ) }}

                                            {{ Form::hidden(
                                                'extra_fields['.$key.'][input_type]',
                                                $data->type
                                            ) }}


                                            <div class="form-group col-md-6 col-xl-4">

                                                @if($data->type != 'radio' && $data->type != 'checkbox')

                                                    <label>
                                                        {{ $data->name }}

                                                        @if($data->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>

                                                @endif


                                                @if($data->type == 'text')

                                                    {{ Form::text(
                                                        'extra_fields['.$key.'][data]',
                                                        '',
                                                        [
                                                            'class' => 'form-control text-fields',
                                                            'id' => $fieldName,
                                                            'placeholder' => $data->name,
                                                            ($data->is_required == 1 ? 'required' : '')
                                                        ]
                                                    ) }}


                                                @elseif($data->type == 'number')

                                                    {{ Form::number(
                                                        'extra_fields['.$key.'][data]',
                                                        '',
                                                        [
                                                            'min' => 0,
                                                            'class' => 'form-control number-fields',
                                                            'id' => $fieldName,
                                                            'placeholder' => $data->name,
                                                            ($data->is_required == 1 ? 'required' : '')
                                                        ]
                                                    ) }}


                                                @elseif($data->type == 'dropdown')

                                                    {{ Form::select(
                                                        'extra_fields['.$key.'][data]',
                                                        $data->default_values,
                                                        null,
                                                        [
                                                            'id' => $fieldName,
                                                            'class' => 'form-control select-fields',
                                                            ($data->is_required == 1 ? 'required' : ''),
                                                            'placeholder' => 'Select '.$data->name
                                                        ]
                                                    ) }}


                                                @elseif($data->type == 'radio')

                                                    <label class="d-block">
                                                        {{ $data->name }}

                                                        @if($data->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>

                                                    <div class="d-flex flex-wrap">

                                                        @foreach ($data->default_values as $keyRadio => $value)

                                                            <div class="custom-control custom-radio mr-3 mb-2">

                                                                {{ Form::radio(
                                                                    'extra_fields['.$key.'][data]',
                                                                    $value,
                                                                    null,
                                                                    [
                                                                        'id' => $fieldName.'_'.$keyRadio,
                                                                        'class' => 'radio-fields',
                                                                        ($data->is_required == 1 ? 'required' : '')
                                                                    ]
                                                                ) }}

                                                                <label class="ml-1"
                                                                       for="{{ $fieldName.'_'.$keyRadio }}">
                                                                    {{ $value }}
                                                                </label>

                                                            </div>

                                                        @endforeach

                                                    </div>


                                                @elseif($data->type == 'checkbox')

                                                    <label class="d-block">
                                                        {{ $data->name }}

                                                        @if($data->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>

                                                    <div class="d-flex flex-wrap">

                                                        @foreach ($data->default_values as $chkKey => $value)

                                                            <div class="custom-control custom-checkbox mr-3 mb-2">

                                                                {{ Form::checkbox(
                                                                    'extra_fields['.$key.'][data][]',
                                                                    $value,
                                                                    null,
                                                                    [
                                                                        'id' => $fieldName.'_'.$chkKey,
                                                                        'class' => 'form-check-input checkbox-fields',
                                                                        ($data->is_required == 1 ? 'required' : '')
                                                                    ]
                                                                ) }}

                                                                <label class="ml-1"
                                                                       for="{{ $fieldName.'_'.$chkKey }}">
                                                                    {{ $value }}
                                                                </label>

                                                            </div>

                                                        @endforeach

                                                    </div>


                                                @elseif($data->type == 'textarea')

                                                    {{ Form::textarea(
                                                        'extra_fields['.$key.'][data]',
                                                        '',
                                                        [
                                                            'placeholder' => $data->name,
                                                            'id' => $fieldName,
                                                            'class' => 'form-control textarea-fields',
                                                            ($data->is_required ? 'required' : ''),
                                                            'rows' => 3
                                                        ]
                                                    ) }}


                                                @elseif($data->type == 'file')

                                                    <div class="input-group">

                                                        {{ Form::file(
                                                            'extra_fields['.$key.'][data]',
                                                            [
                                                                'class' => 'file-upload-default',
                                                                'id' => $fieldName,
                                                                ($data->is_required ? 'required' : '')
                                                            ]
                                                        ) }}

                                                        {{ Form::text(
                                                            '',
                                                            '',
                                                            [
                                                                'class' => 'form-control file-upload-info',
                                                                'disabled' => '',
                                                                'placeholder' => __('Choose file')
                                                            ]
                                                        ) }}

                                                        <span class="input-group-append">

                                                            <button class="file-upload-browse btn btn-theme"
                                                                    type="button">
                                                                {{ __('Upload') }}
                                                            </button>

                                                        </span>

                                                    </div>

                                                    <div id="file_div_{{ $fieldName }}"
                                                         class="mt-2 d-none file-div">

                                                        <a href=""
                                                           id="file_link_{{ $fieldName }}"
                                                           target="_blank">
                                                            {{ $data->name }}
                                                        </a>

                                                    </div>

                                                @endif

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endif



                    {{-- =====================================================
                         GUARDIAN INFORMATION
                    ====================================================== --}}
                    <div class="edit-section mb-2">

                        <div class="edit-section-heading">

                            <div class="edit-section-icon bg-danger">
                                <i class="fa fa-users"></i>
                            </div>

                            <div>
                                <h5 class="mb-0 font-weight-bold">
                                    {{ __('Guardian Information') }}
                                </h5>

                                <small class="text-muted">
                                    {{ __('Parent or guardian contact information') }}
                                </small>
                            </div>

                        </div>


                        <div class="card edit-card">

                            <div class="card-body">

                                <div class="row">


                                    {{-- GUARDIAN SEARCH --}}
                                    <div class="form-group col-12">

                                        <label>
                                            {{ __('Guardian Email') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select class="edit-guardian-search form-control"
                                                name="guardian_id"
                                                id="edit_guardian_search">
                                        </select>

                                        <input type="hidden"
                                               id="edit_guardian_email"
                                               name="guardian_email">

                                    </div>


                                    {{-- GUARDIAN FIRST NAME --}}
                                    <div class="form-group col-md-4">

                                        <label>
                                            {{ __('Guardian First Name') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::text(
                                            'guardian_first_name',
                                            null,
                                            [
                                                'placeholder' => __('Guardian First Name'),
                                                'class' => 'form-control',
                                                'id' => 'edit_guardian_first_name'
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- GUARDIAN LAST NAME --}}
                                    <div class="form-group col-md-4">

                                        <label>
                                            {{ __('Guardian Last Name') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::text(
                                            'guardian_last_name',
                                            null,
                                            [
                                                'placeholder' => __('Guardian Last Name'),
                                                'class' => 'form-control',
                                                'id' => 'edit_guardian_last_name'
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- GUARDIAN MOBILE --}}
                                    <div class="form-group col-md-4">

                                        <label>
                                            {{ __('Guardian Mobile') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        {!! Form::number(
                                            'guardian_mobile',
                                            null,
                                            [
                                                'placeholder' => __('Guardian Mobile'),
                                                'class' => 'form-control remove-number-increment',
                                                'min' => 1,
                                                'id' => 'edit_guardian_mobile'
                                            ]
                                        ) !!}

                                    </div>


                                    {{-- GUARDIAN GENDER --}}
                                    <div class="form-group col-md-6">

                                        <label class="d-block">
                                            {{ __('Guardian Gender') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <div class="custom-control custom-radio custom-control-inline">

                                            <input type="radio"
                                                   name="guardian_gender"
                                                   value="male"
                                                   class="custom-control-input"
                                                   id="edit-guardian-male">

                                            <label class="custom-control-label"
                                                   for="edit-guardian-male">
                                                {{ __('Male') }}
                                            </label>

                                        </div>

                                        <div class="custom-control custom-radio custom-control-inline">

                                            <input type="radio"
                                                   name="guardian_gender"
                                                   value="female"
                                                   class="custom-control-input"
                                                   id="edit-guardian-female">

                                            <label class="custom-control-label"
                                                   for="edit-guardian-female">
                                                {{ __('Female') }}
                                            </label>

                                        </div>

                                    </div>


                                    {{-- GUARDIAN IMAGE --}}
                                    <div class="form-group col-md-6">

                                        <label>
                                            {{ __('Guardian Image') }}
                                        </label>

                                        <input type="file"
                                               name="guardian_image"
                                               class="file-upload-default"/>

                                        <div class="input-group">

                                            <input type="text"
                                                   class="form-control file-upload-info"
                                                   disabled
                                                   placeholder="{{ __('Choose new image') }}"
                                                   id="edit_guardian_image"/>

                                            <span class="input-group-append">

                                                <button class="file-upload-browse btn btn-theme"
                                                        type="button">

                                                    <i class="fa fa-upload mr-1"></i>
                                                    {{ __('Upload') }}

                                                </button>

                                            </span>

                                        </div>


                                        <div class="mt-2">

                                            <img src=""
                                                 id="edit-guardian-image-tag"
                                                 class="img-fluid rounded shadow-sm"
                                                 style="max-height:100px;"
                                                 alt="Guardian">

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- FOOTER --}}
                <div class="modal-footer bg-light border-top px-4 py-3 student-edit-footer">

                    <button type="button"
                            class="btn btn-light border"
                            data-dismiss="modal">

                        <i class="fa fa-times mr-1"></i>
                        {{ __('Cancel') }}

                    </button>

                    <button type="submit"
                            class="btn btn-theme px-4">

                        <i class="fa fa-save mr-1"></i>
                        {{ __('Save Changes') }}

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endcan
{{-- EDIT SECTION ENDS HERE --}}

@endsection
@section('script')
    <script>
        let userIds;
        $('.student-list-type').on('click', function (e) {
            let value = $(this).data('value');
            let ActiveLang = window.trans['Active'];
            let DeactiveLang = window.trans['Inactive'];
            if (value === "" || value === "active" || value == null) {
                $("#update-status").data("id")
                $('.update-status-btn-name').html(DeactiveLang);
            } else {
                $('.update-status-btn-name').html(ActiveLang);
            }
        })


        function updateUserStatus(tableId, buttonClass) {
            let selectedRows = $(tableId).bootstrapTable('getSelections');
            let selectedRowsValues = selectedRows.map(function (row) {
                return row.user_id;
            });
            userIds = JSON.stringify(selectedRowsValues);

            if (buttonClass != null) {
                if (selectedRowsValues.length) {
                    $(buttonClass).prop('disabled', false);
                } else {
                    $(buttonClass).prop('disabled', true);
                }
            }
        }

        $('#table_list').bootstrapTable({
            onCheck: function (row) {
                updateUserStatus("#table_list", '#update-status');
            },
            onUncheck: function (row) {
                updateUserStatus("#table_list", '#update-status');
            },
            onCheckAll: function (rows) {
                updateUserStatus("#table_list", '#update-status');
            },
            onUncheckAll: function (rows) {
                updateUserStatus("#table_list", '#update-status');
            }
        });
        $("#update-status").on('click', function (e) {
            Swal.fire({
                title: window.trans["Are you sure"],
                text: window.trans["Change Status For Selected Users"],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: window.trans["Yes, Change it"],
                cancelButtonText: window.trans["Cancel"]
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = baseUrl + '/students/change-status-bulk';
                    let data = new FormData();
                    data.append("ids", userIds)

                    function successCallback(response) {
                        $('#table_list').bootstrapTable('refresh');
                        $('#update-status').prop('disabled', true);
                        userIds = null;
                        showSuccessToast(response.message);
                    }

                    function errorCallback(response) {
                        showErrorToast(response.message);
                    }

                    ajaxRequest('POST', url, data, null, successCallback, errorCallback);
                }
            })
        })
        
    </script>
@endsection

<style>
.student-edit-dialog {
    height: calc(100vh - 30px);
    max-height: calc(100vh - 30px);
}

.student-edit-modal {
    height: 100%;
    max-height: 100%;
    display: flex;
    flex-direction: column;
    overflow: auto;
    border-radius: 12px;
}

.student-edit-header {
    flex-shrink: 0;
}

.student-edit-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: auto;
    padding: 24px;
}

.student-edit-footer {
    flex-shrink: 0;
}

.edit-section {
    margin-bottom: 28px;
}

.edit-section-heading {
    display: flex;
    align-items: center;
    margin-bottom: 14px;
}

.edit-section-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: 50%;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
}

.edit-card {
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.edit-card .card-body {
    padding: 22px;
}

.edit-card label {
    font-weight: 600;
    margin-bottom: 6px;
}

.edit-status-box {
    border-top: 1px solid #e5e5e5;
    padding-top: 15px;
    margin-top: 5px;
}

.student-edit-body::-webkit-scrollbar {
    width: 7px;
}

.student-edit-body::-webkit-scrollbar-thumb {
    background: #c8c8c8;
    border-radius: 10px;
}

.student-edit-body::-webkit-scrollbar-track {
    background: #f5f5f5;
}

@media (max-width: 767px) {

    .student-edit-dialog {
        height: calc(100vh - 10px);
        max-height: calc(100vh - 10px);
        margin: 5px;
    }

    .student-edit-body {
        padding: 15px;
    }

    .edit-card .card-body {
        padding: 15px;
    }

    .student-edit-header {
        padding: 15px !important;
    }
}
</style>
