@extends('layouts.master')

@section('title')
Generate Student Invoices
@endsection

@section('content')

<style>
    body{
        background:#f5f7fb;
    }

    .glass-card{
        background:#fff;
        border:1px solid #e9edf3;
        border-radius:14px;
        box-shadow:0 6px 18px rgba(0,0,0,.05);
    }

    .soft-box{
        background:#fff;
        border:1px solid #eef1f5;
        border-radius:12px;
        padding:18px;
    }

    .section-title{
        font-size:13px;
        font-weight:600;
        color:#6b7280;
        margin-bottom:10px;
    }

    .loading{
        padding:20px;
        text-align:center;
        color:#6b7280;
    }

    .summary-card{
        border-radius:12px;
        border:1px solid #edf0f5;
        padding:18px;
        background:#fff;
        text-align:center;
        transition:.25s;
    }

    .summary-card:hover{
        transform:translateY(-2px);
        box-shadow:0 8px 20px rgba(0,0,0,.06);
    }

    .summary-card h3{
        margin:0;
        font-weight:700;
        color:#0d6efd;
    }

    .summary-card small{
        color:#6c757d;
    }

    table th,
    table td{
        vertical-align:middle!important;
    }

    .table input[type=number]{
        text-align:right;
    }

    #invoice_status{
        font-size:14px;
        font-weight:500;
    }

    .btn{
        border-radius:10px;
    }
</style>

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="glass-card p-3 mb-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h4 class="mb-1">
                    <i class="fa fa-file-text-o text-primary"></i>
                    Generate Student Invoices
                </h4>

                <small class="text-muted">
                    Select a class, choose students and verify the fee structure before generating invoices.
                </small>

            </div>

        </div>

    </div>

    {{-- FILTERS --}}
    <div class="glass-card p-3 mb-3">

        <div class="row g-3">

            <div class="col-md-4">

                <label class="section-title">
                    Academic Year
                </label>

                <select class="form-control" id="session_year_id">

                    @foreach($session_year_all as $year)

                    <option value="{{ $year->id }}"
                        {{ $year->default ? 'selected' : '' }}>

                        {{ $year->name }}

                    </option>

                    @endforeach

                </select>

            </div>

            <div class="col-md-4">

                <label class="section-title">
                    Semester
                </label>

                <select class="form-control" id="semester_id">

                    @foreach($semesters as $sem)

                    <option value="{{ $sem->id }}">
                        {{ $sem->name }}
                    </option>

                    @endforeach

                </select>

            </div>

            <div class="col-md-4">

                <label class="section-title">
                    Class
                </label>

                <select class="form-control" id="class_id">

                    <option value="">
                        Select Class
                    </option>

                    @foreach($classes as $class)

                    <option value="{{ $class->id }}">
                        {{ $class->name }}
                    </option>

                    @endforeach

                </select>

            </div>

        </div>

    </div>

    {{-- LIVE SUMMARY --}}
    <div class="row mb-3">

        <div class="col-md-3">

            <div class="summary-card">

                <h3 id="summaryStudents">0</h3>

                <small>Students Selected</small>

            </div>

        </div>

        <div class="col-md-3">

            <div class="summary-card">

                <h3 id="summaryFees">0</h3>

                <small>Fee Items</small>

            </div>

        </div>

        <div class="col-md-3">

            <div class="summary-card">

                <h3 id="summaryTotal">
                    KES 0
                </h3>

                <small>Total Invoice Value</small>

            </div>

        </div>

        <div class="col-md-3">

            <div class="summary-card">

                <h3 id="summaryAverage">
                    KES 0
                </h3>

                <small>Average Per Student</small>

            </div>

        </div>

    </div>

    {{-- STATUS --}}
    <div class="glass-card p-3 mb-3">

        <div class="row align-items-center">

            <div class="col-md-8">

                <div
                    id="invoice_status"
                    class="alert alert-warning mb-0">

                    <i class="fa fa-info-circle"></i>

                    Select a class to begin.

                </div>

            </div>

            <div class="col-md-4 text-end">

                <button
                    id="generateBtn"
                    class="btn btn-success btn-lg"
                    disabled>

                    <i class="fa fa-check-circle"></i>

                    Generate Invoices

                </button>

            </div>

        </div>

    </div>

    {{-- MAIN --}}
    <div class="glass-card p-3">

        <div class="row g-3">

            {{-- =======================
                 STUDENTS
            ======================== --}}
            <div class="col-lg-4">

                <div class="soft-box h-100">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <div class="section-title mb-0">
                                Students
                            </div>

                            <small class="text-muted">
                                <span id="studentCount">0</span> loaded
                            </small>

                        </div>

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="select_all"
                                checked>

                            <label
                                class="form-check-label small"
                                for="select_all">

                                Select All

                            </label>

                        </div>

                    </div>

                    <input
                        type="text"
                        id="student_search"
                        class="form-control mb-3"
                        placeholder="Search student...">

                    <div
                        id="student_list"
                        style="max-height:600px;overflow-y:auto;">

                        <div class="loading">

                            <i class="fa fa-users fa-2x mb-2"></i>

                            <div>

                                Select a class to load students

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- =======================
                 FEE STRUCTURE
            ======================== --}}
            <div class="col-lg-8">

                <div class="soft-box">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <div class="section-title mb-0">

                                Fee Structure

                            </div>

                            <small class="text-muted">

                                Tick to include or exclude fee items.

                            </small>

                        </div>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">

                                <tr>

                                    <th width="40"></th>

                                    <th>Fee Item</th>

                                    <th class="text-end">
                                        Amount
                                    </th>

                                </tr>

                            </thead>

                            <tbody id="fee_items">

                                <tr>

                                    <td
                                        colspan="3"
                                        class="text-center text-muted py-4">

                                        Select a class first.

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- =======================
         SELECTED STUDENTS
    ======================== --}}
    <div class="glass-card p-3 mt-3">

        <div class="soft-box">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <div class="section-title mb-0">

                        Selected Students

                    </div>

                    <small class="text-muted">

                        Each student's fees can be edited individually.

                    </small>

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>

                            <th width="50">#</th>

                            <th width="120">Admission</th>

                            <th width="240">Student</th>

                            <th>Fee Breakdown</th>
                            <th width="150" class="text-end">
                                Invoice Total
                            </th>

                        </tr>

                    </thead>

                    <tbody id="selected_students">

                        <tr>

                            <td
                                colspan="5"
                                class="text-center text-muted py-5">

                                <i
                                    class="fa fa-user-plus fa-2x mb-2">
                                </i>

                                <div>

                                    No students selected.

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
    </div>

<script>

let students = [];
let selectedStudents = [];
let feeItems = [];

const TRANSPORT_FEE_NAME = "school_transport";

/* ==========================================
   BACKEND DATA
========================================== */

const allFeeStructures = @json($feeStructures);

/* ==========================================
   HELPERS
========================================== */

function formatMoney(amount) {

    return "KES " + Number(amount).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });

}

function activeFees() {
    return feeItems.filter(f => f.active);
}

function isTransportFee(fee) {
    if (!fee.name) return false;

    const name = fee.name
        .trim()
        .replace(/\s+/g, " ")
        .toUpperCase();

    return name.includes("TRANSPORT");

}

/* ==========================================
   LOAD CLASS
========================================== */

document.getElementById('class_id')
    .addEventListener('change', loadClassData);

function loadClassData() {

    const classId = document.getElementById('class_id').value;

    students = [];
    selectedStudents = [];
    feeItems = [];

    renderStudents();
    renderFees();
    renderSelected();
    validatePage();

    if (!classId)
        return;

    loadStudents(classId);
    loadFees(classId);

}

/* ==========================================
   LOAD STUDENTS
========================================== */

function loadStudents(classId) {

    document.getElementById('student_list').innerHTML = `
        <div class="loading">
            <i class="fa fa-spinner fa-spin"></i><br>
            Loading students...
        </div>
    `;

    fetch(`/students/byclass/${classId}`)
        .then(res => res.json())
        .then(data => {

            students = data || [];

            selectedStudents = students.map(student => ({
                ...student,
                fees: {}
            }));

            document.getElementById('studentCount').innerHTML =
                students.length;

            document.getElementById('select_all').checked = true;

            renderStudents();
            renderSelected();
            validatePage();

        })
        .catch(() => {

            students = [];
            selectedStudents = [];

            renderStudents();
            renderSelected();
            validatePage();

        });

}

/* ==========================================
   LOAD FEES
========================================== */

function loadFees(classId) {

    feeItems = allFeeStructures
        .filter(f => f.class_id == classId)
        .map(f => ({
            fees_id: f.fees_id,
            fees_type_id: f.fees_type_id,
            id: f.fees_type.id,
            title: f.fees_type.name,
            name: f.fees_type.name,
            amount: Number(f.amount ?? 0),
            active: true
        }));

    renderFees();

    validatePage();

}

/* ==========================================
   SEARCH STUDENTS
========================================== */

document
.getElementById('student_search')
.addEventListener('change', function () {

    const keyword = this.value.toLowerCase();

    document.querySelectorAll('.student-row')
        .forEach(row => {

            row.style.display =
                row.dataset.name.includes(keyword) ||
                row.dataset.admission.includes(keyword)
                    ? ''
                    : 'none';

        });

});

/* ==========================================
   INITIAL PAGE STATE
========================================== */

document.addEventListener('DOMContentLoaded', () => {

    validatePage();

});

/* ==========================================
   RENDER STUDENTS
========================================== */

function renderStudents() {

    const box = document.getElementById("student_list");

    if (!students.length) {

        box.innerHTML = `
            <div class="loading">
                No students found.
            </div>
        `;

        return;
    }

    box.innerHTML = students.map(student => {

        const selected = selectedStudents.some(s => s.id == student.id);

        const transportBadge =
            !student.school_transport ||
            student.school_transport.toLowerCase() === "no"

                ? `<span class="badge bg-secondary">
                        No Transport
                   </span>`

                : `<span class="badge bg-success">
                        <i class="fa fa-bus"></i>
                        ${student.school_transport.replace('_',' ').toUpperCase()}
                   </span>`;

        return `

        <div
            class="student-row border rounded p-2 mb-2"
            data-name="${student.name.toLowerCase()}"
            data-admission="${student.admission_no.toLowerCase()}">

            <div class="d-flex justify-content-between align-items-start">

                <div>

                    <div class="fw-bold">

                        ${student.name}

                    </div>

                    <small class="text-muted">

                        ${student.admission_no}

                    </small>

                    <br>

                    ${transportBadge}

                </div>

                <div>

                    <input
                        class="form-check-input"
                        type="checkbox"
                        ${selected ? "checked" : ""}
                        onchange="toggleStudent(${student.id})">

                </div>

            </div>

        </div>

        `;

    }).join('');

}

/* ==========================================
   TOGGLE STUDENT
========================================== */

function toggleStudent(studentId) {

    const existing =
        selectedStudents.find(s => s.id == studentId);

    if (existing) {

        selectedStudents =
            selectedStudents.filter(s => s.id != studentId);

    } else {

        const student =
            students.find(s => s.id == studentId);

        selectedStudents.push({

            ...student,

            fees:{}

        });

    }

    document.getElementById("select_all").checked =
        selectedStudents.length === students.length;

    renderStudents();

    renderSelected();

    validatePage();

}

/* ==========================================
   SELECT ALL
========================================== */

document
.getElementById("select_all")
.addEventListener("change", function(){

    if(this.checked){

        selectedStudents = students.map(student=>({

            ...student,

            fees:{}

        }));

    }else{

        selectedStudents=[];

    }

    renderStudents();

    renderSelected();

    validatePage();

});

/* ==========================================
   RENDER FEES
========================================== */

function renderFees(){

    const box=document.getElementById("fee_items");

    if(!feeItems.length){

        box.innerHTML=`

        <tr>

            <td
                colspan="3"
                class="text-center text-muted py-4">

                <i class="fa fa-exclamation-circle text-warning"></i>

                <br><br>

                No fee structure found for this class.

            </td>

        </tr>

        `;

        return;

    }

    box.innerHTML=feeItems.map(fee=>`

        <tr>

            <td>

                <input
                    class="form-check-input"
                    type="checkbox"
                    ${fee.active ? "checked" : ""}
                    onchange="toggleFee(${fee.id})">

            </td>

            <td>

                ${fee.name}

            </td>

            <td class="text-end">

                ${formatMoney(fee.amount)}

            </td>

        </tr>

    `).join('');

}

/* ==========================================
   TOGGLE FEE
========================================== */

function toggleFee(feeId){

    const fee =
        feeItems.find(f=>f.id==feeId);

    if(!fee)
        return;

    fee.active=!fee.active;

    renderFees();

    renderSelected();

    validatePage();

}
/* ==========================================
   RENDER SELECTED STUDENTS
========================================== */

function renderSelected() {

    const box = document.getElementById("selected_students");

    if (!selectedStudents.length) {

        box.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-5">

                    <i class="fa fa-user-plus fa-2x mb-2"></i>

                    <div>
                        No students selected.
                    </div>

                </td>
            </tr>
        `;

        validatePage();
        return;
    }

    const fees = activeFees();

    box.innerHTML = selectedStudents.map((student, index) => {

        let feeHtml = "";
        let studentTotal = 0;

        fees.forEach(fee => {

            if ( isTransportFee(fee) &&
                (
                    !student.school_transport ||
                    student.school_transport.toLowerCase() === "no"
                    
                )
            ) {
                
                return;
            }

            const value = Number(
                student.fees[fee.id] ?? fee.amount
            );

            studentTotal += value;

            feeHtml += `

                <div class="row mb-2 align-items-center">

                    <div class="col-md-7">

                        <small>

                            ${fee.name}

                        </small>

                    </div>

                    <div class="col-md-5">

                        <input
                            type="number"
                            min="0"
                            class="form-control form-control-sm text-end"

                            value="${value}"

                            onchange="updateFee(${student.id}, ${fee.id}, this.value)">

                    </div>

                </div>

            `;

        });

        if (feeHtml === "") {

            feeHtml = `
                <div class="text-muted text-center py-3">

                    No applicable fee items.

                </div>
            `;

        }

        const badge =
            student.school_transport &&
            student.school_transport.toLowerCase() !== "no"

                ? `<span class="badge bg-success">
                        <i class="fa fa-bus"></i>
                        ${student.school_transport.replace("_"," ").toUpperCase()}
                   </span>`

                : `<span class="badge bg-secondary">

                        No Transport

                   </span>`;

        return `

            <tr>

                <td>

                    ${index + 1}

                </td>

                <td>

                    ${student.admission_no}

                </td>

                <td>

                    <div class="fw-bold">

                        ${student.name}

                    </div>

                    ${badge}

                </td>

                <td>

                    ${feeHtml}

                </td>

                <td class="text-end">

                    <div
                        class="fw-bold text-success">

                        ${formatMoney(studentTotal)}

                    </div>

                </td>

            </tr>

        `;

    }).join("");

    validatePage();

}

/* ==========================================
   UPDATE INDIVIDUAL FEE
========================================== */

function updateFee(studentId, feeId, value) {

    const student =
        selectedStudents.find(s => s.id == studentId);

    if (!student)
        return;

    student.fees[feeId] =
        Math.max(
            0,
            Number(value || 0)
        );

    /* Recalculate immediately */

    renderSelected();

}
/* ==========================================
   PAGE VALIDATION & LIVE SUMMARY
========================================== */

function validatePage() {

    const btn = document.getElementById("generateBtn");
    const status = document.getElementById("invoice_status");

    const summaryStudents = document.getElementById("summaryStudents");
    const summaryFees = document.getElementById("summaryFees");
    const summaryTotal = document.getElementById("summaryTotal");
    const summaryAverage = document.getElementById("summaryAverage");

    const fees = activeFees();

    let grandTotal = 0;

    selectedStudents.forEach(student => {

        fees.forEach(fee => {

            // Skip transport where applicable
            if (
                isTransportFee(fee) &&
                (
                    !student.school_transport ||
                    student.school_transport.toLowerCase() === "no"
                )
            ) {
                return;
            }

            grandTotal += Number(
                student.fees[fee.id] ?? fee.amount
            );

        });

    });

    const average =
        selectedStudents.length
            ? grandTotal / selectedStudents.length
            : 0;

    summaryStudents.innerHTML =
        selectedStudents.length;

    summaryFees.innerHTML =
        fees.length;

    summaryTotal.innerHTML =
        formatMoney(grandTotal);

    summaryAverage.innerHTML =
        formatMoney(average);

    let errors = [];

    if (!document.getElementById("class_id").value)
        errors.push("Select a class.");

    if (!feeItems.length)
        errors.push("No fee structure found.");

    if (!fees.length)
        errors.push("No active fee items.");

    if (!selectedStudents.length)
        errors.push("No students selected.");

    if (grandTotal <= 0)
        errors.push("Invoice total is zero.");

    if (errors.length) {

        btn.disabled = true;

        btn.classList.remove("btn-success");
        btn.classList.add("btn-secondary");

        status.className =
            "alert alert-warning mb-0";

        status.innerHTML = `

            <i class="fa fa-exclamation-circle"></i>

            ${errors.join("<br>")}

        `;

    } else {

        btn.disabled = false;

        btn.classList.remove("btn-secondary");
        btn.classList.add("btn-success");

        status.className =
            "alert alert-success mb-0";

        status.innerHTML = `

            <i class="fa fa-check-circle"></i>

            Ready to generate
            <strong>${selectedStudents.length}</strong>
            invoice(s).

        `;

    }

}

/* ==========================================
   GENERATE BUTTON
========================================== */

document
.getElementById("generateBtn")
$('#generateBtn').click(function () {

    const btn = $(this);

    if (btn.prop('disabled')) {
        return;
    }

    const data = buildInvoiceData();

    btn.prop('disabled', true);

    btn.html(`
        <span class="spinner-border spinner-border-sm me-2"></span>
        Generating...
    `);

    $.ajax({

        url: "{{ route('fees.invoice.generate.store') }}",

        type: "POST",

        data: JSON.stringify(data),

        contentType: "application/json",

        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function (res) {

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: res.message
            });

            // Clear selected students
            selectedStudents = [];

            renderStudents();
            renderSelected();

        },

        error: function (xhr) {

            let message = "Something went wrong.";

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });

        },

        complete: function () {

            btn.prop('disabled', false);

            btn.html(`
                <i class="fa fa-check me-1"></i>
                Generate
            `);

            validatePage();

        }

    });

});


/* ==========================================
   INITIAL PAGE LOAD
========================================== */

document.addEventListener("DOMContentLoaded", () => {

    renderStudents();

    renderFees();

    renderSelected();

    validatePage();

});

function buildInvoiceData() {

    return {

        session_year_id:
            $('#session_year_id').val(),

        semester_id:
            $('#semester_id').val(),

        students:

            selectedStudents.map(student => {

                const fees = [];

                activeFees().forEach(fee => {

                    // Skip transport
                    if (
                        isTransportFee(fee) &&
                        (
                            !student.school_transport ||
                            student.school_transport.toLowerCase() === "no"
                        )
                    ) {
                        return;
                    }

                    fees.push({

                        fees_id: fee.fees_id,

                        fees_type_id: fee.fees_type_id,

                        title: fee.title,

                        amount:
                            Number(
                                student.fees[fee.fees_type_id] ??
                                fee.amount
                            )

                    });

                });

                return {

                    id: student.id,

                    fees: fees

                };

            })

    };

}

$('#generateBtn').click(function () {

    const data = buildInvoiceData();

    $.ajax({

        url: "{{ route('fees.invoice.generate.store') }}",

        method: "POST",

        data: JSON.stringify(data),

        contentType: "application/json",

        headers: {

            'X-CSRF-TOKEN':
                $('meta[name="csrf-token"]').attr('content')

        },

        success(res){

            console.log(res);

        },

        error(err){

            console.log(err);

        }

    });

});

</script>

@endsection