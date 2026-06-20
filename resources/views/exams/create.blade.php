@extends('layouts.app')

@section('title', 'Create Exam')

@section('breadcrumb')
    <a href="{{ route('exams.index') }}" class="bc-item">Examinations</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Create Exam</span>
@endsection

@push('styles')
<style>
    .step-indicator { display: flex; justify-content: center; gap: 8px; margin-bottom: 28px; }
    .step-dot { width: 34px; height: 34px; border-radius: 50%; background: #e9ecef; color: #6C757D;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
        position: relative; }
    .step-dot.active { background: #1E3A5F; color: #fff; }
    .step-dot.done { background: #27AE60; color: #fff; }
    .step-line { width: 50px; height: 2px; background: #e9ecef; margin-top: 16px; }
    .step-line.done { background: #27AE60; }
    .form-label { font-size: 13px; font-weight: 600; color: #2C3E50; margin-bottom: 6px; }
    .form-control, .form-select { border: 2px solid #e9ecef; border-radius: 8px; font-size: 13px; padding: 9px 12px; }
    .req { color: #E74C3C; }
    .prog-tab-nav { display: flex; gap: 6px; margin-bottom: 14px; flex-wrap: wrap; }
    .prog-tab-btn { padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600;
        border: 2px solid #e9ecef; background: #fff; cursor: pointer; }
    .prog-tab-btn.active { background: #1E3A5F; color: #fff; border-color: #1E3A5F; }
    .prog-tab-btn .badge-count { background: rgba(255,255,255,.25); padding: 1px 6px; border-radius: 8px; font-size: 10px; margin-left: 4px; }
    table.marks-config-table th { background: #1E3A5F; color: #fff; font-size: 12px; padding: 8px; text-align: center; }
    table.marks-config-table td { padding: 8px; text-align: center; vertical-align: middle; font-size: 13px; }
    table.marks-config-table input { width: 80px; text-align: center; border: 2px solid #e9ecef; border-radius: 6px; padding: 5px; }
    .grand-total-box { background: rgba(30,58,95,.06); padding: 12px 18px; border-radius: 10px;
        font-weight: 700; color: #1E3A5F; display: flex; justify-content: space-between; margin-top: 10px; }
    .section-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;
        background: rgba(39,174,96,.1); color: #27AE60; border-radius: 20px; font-size: 12px; margin: 3px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-file-circle-plus"></i></span>
        Create Exam
    </h1>
    <a href="{{ route('exams.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="step-indicator">
    <div class="step-dot active" id="dot1">1</div><div class="step-line" id="line1"></div>
    <div class="step-dot" id="dot2">2</div><div class="step-line" id="line2"></div>
    <div class="step-dot" id="dot3">3</div><div class="step-line" id="line3"></div>
    <div class="step-dot" id="dot4">4</div>
</div>

<form id="examForm">
@csrf

{{-- STEP 1: Basic Info --}}
<div class="card-custom step-pane" id="step1">
    <div class="card-body-c">
        <h6 class="mb-3" style="color:#1E3A5F;">Exam Basic Info</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Exam Type <span class="req">*</span></label>
                <select id="examType" class="form-select">
                    @foreach($examTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4" id="sequenceWrap">
                <label class="form-label">Test Number (1-10)</label>
                <select id="sequence" class="form-select">
                    @for($i=1;$i<=10;$i++)<option value="{{ $i }}">Test {{ $i }}</option>@endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Exam Name <span class="req">*</span></label>
                <input type="text" id="examName" class="form-control" placeholder="e.g. Test 1">
            </div>
            <div class="col-md-4">
                <label class="form-label">Exam Date <span class="req">*</span></label>
                <input type="date" id="examDate" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Campus Scope <span class="req">*</span></label>
                <select id="campusScope" class="form-select">
                    <option value="both">Both Campuses</option>
                    <option value="boys">Boys Only</option>
                    <option value="girls">Girls Only</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Year Scope <span class="req">*</span></label>
                <select id="yearScope" class="form-select">
                    <option value="both">Both Years</option>
                    <option value="first">First Year Only</option>
                    <option value="second">Second Year Only</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Marks Due Date (optional)</label>
                <input type="datetime-local" id="marksDueDate" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Grading Template</label>
                <select id="gradingTemplate" class="form-select">
                    @foreach($gradingTemplates as $gt)
                        <option value="{{ $gt->id }}" {{ $gt->is_default ? 'selected' : '' }}>{{ $gt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description (optional)</label>
                <textarea id="examDescription" class="form-control" rows="2"></textarea>
            </div>
        </div>
    </div>
</div>

{{-- STEP 2: Subject Marks Configuration --}}
<div class="card-custom step-pane d-none" id="step2">
    <div class="card-body-c">
        <h6 class="mb-3" style="color:#1E3A5F;">Subject Marks Configuration</h6>

        <div class="prog-tab-nav" id="programTabs">
            @foreach($programs as $program)
                <button type="button" class="prog-tab-btn" data-program="{{ $program->id }}" data-code="{{ $program->code }}">
                    {{ $program->code }}
                </button>
            @endforeach
        </div>

        <div id="yearSubTabs" class="mb-3"></div>

        <table class="marks-config-table w-100" id="subjectsTable">
            <thead><tr><th>#</th><th>Subject</th><th>Default Marks</th><th>Exam Marks</th><th>Action</th></tr></thead>
            <tbody id="subjectsTableBody">
                <tr><td colspan="5" class="text-center text-muted py-3">Select a Program tab above to load subjects</td></tr>
            </tbody>
        </table>

        <div class="grand-total-box">
            <span>Grand Total (current tab)</span>
            <span id="grandTotalDisplay">0</span>
        </div>

        <button type="button" class="btn btn-sm mt-2" id="btnResetDefaults" style="background:#6C757D;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-rotate-left me-1"></i> Reset to Defaults
        </button>

        <p class="small text-muted mt-3">
            <i class="fa-solid fa-circle-info"></i>
            Configure marks for each program tab separately. Switching tabs saves your current tab's values automatically.
        </p>
    </div>
</div>

{{-- STEP 3: Section Assignment Preview --}}
<div class="card-custom step-pane d-none" id="step3">
    <div class="card-body-c">
        <h6 class="mb-3" style="color:#1E3A5F;">Section Assignment Preview</h6>
        <div id="sectionPreviewBox">
            <p class="text-muted text-center py-3">Loading affected sections...</p>
        </div>
    </div>
</div>

{{-- STEP 4: Confirm --}}
<div class="card-custom step-pane d-none" id="step4">
    <div class="card-body-c">
        <h6 class="mb-3" style="color:#1E3A5F;">Confirm & Create</h6>
        <div id="confirmSummary"></div>
    </div>
</div>

<div class="d-flex justify-content-between mt-3">
    <button type="button" class="btn" id="btnPrev" style="background:#6C757D;color:#fff;border-radius:8px;display:none;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </button>
    <div class="ms-auto">
        <button type="button" class="btn" id="btnNext" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            Next <i class="fa-solid fa-arrow-right ms-1"></i>
        </button>
        <button type="button" class="btn d-none" id="btnCreate" style="background:#27AE60;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-check me-1"></i> <span id="btnCreateText">Create Exam</span>
        </button>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
let currentStep = 1;
const programSubjectData = {}; // cache per program_id+year -> {subjects, grand_total}
let activeProgram = null, activeYear = 'first';
const tabState = {}; // program_id-year -> [{subject_id,name,total_marks}]

// ── STEP NAVIGATION ──────────────────────────────────────────────
function goToStep(step) {
    $('.step-pane').addClass('d-none');
    $(`#step${step}`).removeClass('d-none');
    $('.step-dot').removeClass('active done');
    for (let i = 1; i < step; i++) $(`#dot${i}`).addClass('done');
    $(`#dot${step}`).addClass('active');
    $('.step-line').removeClass('done');
    for (let i = 1; i < step; i++) $(`#line${i}`).addClass('done');

    $('#btnPrev').toggle(step > 1);
    $('#btnNext').toggle(step < 4);
    $('#btnCreate').toggleClass('d-none', step !== 4);
    currentStep = step;

    if (step === 2 && $('#programTabs .prog-tab-btn').first().length && !activeProgram) {
        $('#programTabs .prog-tab-btn').first().trigger('click');
    }
    if (step === 3) loadSectionPreview();
    if (step === 4) buildConfirmSummary();
}

$('#examType').on('change', function () {
    $('#sequenceWrap').toggle($(this).val() === 'test');
});

$('#btnNext').on('click', () => goToStep(currentStep + 1));
$('#btnPrev').on('click', () => goToStep(currentStep - 1));

// ── STEP 2: Program Tabs ─────────────────────────────────────────
$(document).on('click', '.prog-tab-btn', function () {
    saveCurrentTabState();
    $('.prog-tab-btn').removeClass('active');
    $(this).addClass('active');
    activeProgram = $(this).data('program');
    loadSubjectsForTab();
});

function saveCurrentTabState() {
    if (!activeProgram) return;
    const key = activeProgram + '-' + activeYear;
    const rows = [];
    $('#subjectsTableBody tr[data-subject]').each(function () {
        rows.push({
            subject_id: $(this).data('subject'),
            program_id: activeProgram,
            year: activeYear,
            total_marks: parseInt($(this).find('.marksInput').val()) || 0,
        });
    });
    tabState[key] = rows;
}

function loadSubjectsForTab() {
    const type = $('#examType').val();
    const sequence = $('#sequence').val();

    $.get('{{ route("exams.default-subjects") }}', {
        program_id: activeProgram, year: activeYear, type, sequence,
    }, function (res) {
        let html = '';
        res.data.forEach((s, i) => {
            const key = activeProgram + '-' + activeYear;
            const saved = (tabState[key] || []).find(r => r.subject_id === s.subject_id);
            const marks = saved ? saved.total_marks : s.default_marks;
            html += `<tr data-subject="${s.subject_id}">
                <td>${i+1}</td><td>${s.name}</td><td>${s.default_marks}</td>
                <td><input type="number" class="marksInput" value="${marks}" min="0"></td>
                <td><button type="button" class="btn btn-sm btn-clear-marks" style="background:#6C757D;color:#fff;">Clear</button></td>
            </tr>`;
        });
        $('#subjectsTableBody').html(html);
        updateGrandTotal();
    });
}

$(document).on('input', '.marksInput', updateGrandTotal);
$(document).on('click', '.btn-clear-marks', function () {
    $(this).closest('tr').find('.marksInput').val(0);
    updateGrandTotal();
});

function updateGrandTotal() {
    let total = 0;
    $('.marksInput').each(function () { total += parseInt($(this).val()) || 0; });
    $('#grandTotalDisplay').text(total);
}

$('#btnResetDefaults').on('click', loadSubjectsForTab);

// ── STEP 3: Section Preview ──────────────────────────────────────
function loadSectionPreview() {
    saveCurrentTabState();
    const campusScope = $('#campusScope').val();
    let html = '';
    const programIds = Object.keys(tabState).map(k => k.split('-')[0]);
    const uniquePrograms = [...new Set(programIds)];

    let pending = 0, results = [];
    $('#programTabs .prog-tab-btn').each(function () {
        const programId = $(this).data('program');
        ['first', 'second'].forEach(year => {
            if (!tabState[programId + '-' + year] || !tabState[programId + '-' + year].length) return;
            if ($('#yearScope').val() !== 'both' && $('#yearScope').val() !== year) return;
            pending++;
            $.get('{{ route("exams.affected-sections") }}', { program_id: programId, year, campus_scope: campusScope }, function (res) {
                results.push({ code: $(`.prog-tab-btn[data-program="${programId}"]`).data('code'), year, sections: res.sections, total: res.total_students });
                pending--;
                if (pending === 0) renderPreview(results);
            });
        });
    });

    if (pending === 0) {
        $('#sectionPreviewBox').html('<p class="text-muted text-center py-3">No subject marks configured yet. Go back to Step 2.</p>');
    }
}

function renderPreview(results) {
    let html = '', totalStudents = 0;
    results.forEach(r => {
        totalStudents += r.total;
        html += `<div class="mb-3"><b>${r.code} (${r.year} year):</b><br>`;
        r.sections.forEach(s => html += `<span class="section-chip"><i class="fa-solid fa-check"></i> ${s}</span>`);
        html += ` <span class="text-muted small">(${r.total} students)</span></div>`;
    });
    html += `<div class="alert alert-info mt-2">This exam will be created for <b>${results.reduce((a,r)=>a+r.sections.length,0)}</b> section(s), affecting <b>${totalStudents}</b> students.</div>`;
    $('#sectionPreviewBox').html(html);
}

// ── STEP 4: Confirm Summary ───────────────────────────────────────
function buildConfirmSummary() {
    $('#confirmSummary').html(`
        <table class="table table-sm">
            <tr><td><b>Exam Name</b></td><td>${$('#examName').val()}</td></tr>
            <tr><td><b>Type</b></td><td>${$('#examType').val()}</td></tr>
            <tr><td><b>Date</b></td><td>${$('#examDate').val()}</td></tr>
            <tr><td><b>Campus Scope</b></td><td>${$('#campusScope').val()}</td></tr>
            <tr><td><b>Year Scope</b></td><td>${$('#yearScope').val()}</td></tr>
        </table>
    `);
}

// ── FINAL SUBMIT ──────────────────────────────────────────────────
$('#btnCreate').on('click', function () {
    saveCurrentTabState();

    let subjectMarks = [];
    Object.values(tabState).forEach(rows => subjectMarks = subjectMarks.concat(rows));

    if (!subjectMarks.length) { toastr.error('Please configure at least one subject in Step 2.'); return; }

    $(this).prop('disabled', true);
    $('#btnCreateText').html('<span class="spinner-border spinner-border-sm"></span> Creating...');

    $.post('{{ route("exams.store") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        name: $('#examName').val(),
        type: $('#examType').val(),
        sequence: $('#sequence').val(),
        exam_date: $('#examDate').val(),
        campus_scope: $('#campusScope').val(),
        year_scope: $('#yearScope').val(),
        marks_due_date: $('#marksDueDate').val(),
        grading_template_id: $('#gradingTemplate').val(),
        description: $('#examDescription').val(),
        subject_marks: subjectMarks,
    }).done(function (res) {
        toastr.success(res.message);
        setTimeout(() => window.location.href = res.redirect, 800);
    }).fail(function (xhr) {
        toastr.error(xhr.responseJSON?.message || 'Failed to create exam.');
        $('#btnCreate').prop('disabled', false);
        $('#btnCreateText').text('Create Exam');
    });
});
</script>
@endpush
