@extends('layouts.app')

@section('title', 'Marks Entry')

@section('breadcrumb')
    <a href="{{ route('exams.index') }}" class="bc-item">Examinations</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Marks Entry</span>
@endsection

@push('styles')
<style>
    .filter-bar select { padding: 9px 14px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 13px; min-width: 160px; }
    table.marks-table th { background: #1E3A5F; color: #fff; text-align: center; padding: 10px; font-size: 12px; }
    table.marks-table td { text-align: center; padding: 8px; vertical-align: middle; }
    table.marks-table tbody tr:nth-child(even) { background: #F8F9FA; }
    table.marks-table tbody tr.row-absent { background: #fdeaea !important; opacity: .7; }
    table.marks-table input.mark-input { width: 80px; text-align: center; border: 2px solid #e9ecef;
        border-radius: 6px; padding: 6px; }
    table.marks-table input.mark-input.empty-highlight { background: #FFF3CD; border-color: #F39C12; }
    .absent-badge { background: #E74C3C; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 10px; }
    .leave-badge { background: #F39C12; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 10px; }
    .total-marks-pill { background: rgba(30,58,95,.08); color: #1E3A5F; font-weight: 700;
        padding: 6px 16px; border-radius: 10px; display: inline-block; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-pen-to-square"></i></span>
        Marks Entry
    </h1>
    <a href="{{ route('exams.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card-custom mb-3">
    <div class="card-body-c">
        <div class="filter-bar d-flex gap-2 flex-wrap align-items-end">
            <div>
                <label class="form-label small d-block">Exam</label>
                <select id="examSelect" class="form-select form-select-sm">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" data-date="{{ $exam->exam_date->format('Y-m-d') }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small d-block">Year</label>
                <select id="yearSelect" class="form-select form-select-sm" disabled>
                    <option value="">Select Year</option><option value="first">First</option><option value="second">Second</option>
                </select>
            </div>
            <div>
                <label class="form-label small d-block">Campus</label>
                <select id="campusSelect" class="form-select form-select-sm" disabled>
                    <option value="">Select Campus</option><option value="boys">Boys</option><option value="girls">Girls</option>
                </select>
            </div>
            <div>
                <label class="form-label small d-block">Section</label>
                <select id="sectionSelect" class="form-select form-select-sm" disabled><option value="">Select Section</option></select>
            </div>
            <div>
                <label class="form-label small d-block">Subject</label>
                <select id="subjectSelect" class="form-select form-select-sm" disabled><option value="">Select Subject</option></select>
            </div>
            <div>
                <button class="btn btn-sm" id="btnLoad" style="background:#1E3A5F;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-table me-1"></i> Load Students
                </button>
            </div>
        </div>
    </div>
</div>

<div id="marksTableBox" class="d-none">
    <div class="card-custom">
        <div class="card-body-c">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="total-marks-pill">Total Marks: <span id="totalMarksDisplay">0</span></span>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm" id="btnHighlightEmpty" style="background:#F39C12;color:#fff;border-radius:8px;">
                        <i class="fa-solid fa-highlighter me-1"></i> Highlight Empty
                    </button>
                    <button class="btn btn-sm" id="btnSaveTop" style="background:#27AE60;color:#fff;border-radius:8px;">
                        <i class="fa-solid fa-save me-1"></i> <span class="save-text">Save All</span>
                    </button>
                </div>
            </div>

            <div id="lockedWarning" class="alert alert-danger d-none">
                <i class="fa-solid fa-lock"></i> Due date has passed. Marks entry is locked. Ask Principal to extend the due date.
            </div>

            <div class="table-responsive">
                <table class="marks-table w-100">
                    <thead><tr><th>Roll No</th><th>Name</th><th>Father Name</th><th>Marks</th><th>Status</th></tr></thead>
                    <tbody id="marksTableBody"></tbody>
                </table>
            </div>

            <button class="btn btn-sm mt-3" id="btnSaveBottom" style="background:#27AE60;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-save me-1"></i> <span class="save-text">Save All</span>
            </button>
        </div>
    </div>
</div>

<div id="emptyState" class="text-center text-muted py-5">
    <i class="fa-solid fa-table-list fa-3x mb-3 d-block opacity-25"></i>
    Select Exam → Year → Campus → Section → Subject, then click Load Students.
</div>
@endsection

@push('scripts')
<script>
let currentTotalMarks = 0, isLocked = false;

$('#examSelect, #yearSelect, #campusSelect').on('change', function () {
    if ($('#examSelect').val()) $('#yearSelect').prop('disabled', false);
    if ($('#yearSelect').val()) $('#campusSelect').prop('disabled', false);
    if ($('#examSelect').val() && $('#yearSelect').val() && $('#campusSelect').val()) {
        $.get('{{ route("exams.marks-entry.sections") }}', {
            exam_id: $('#examSelect').val(), campus: $('#campusSelect').val(), year: $('#yearSelect').val(),
        }, function (res) {
            const $s = $('#sectionSelect');
            $s.prop('disabled', false).empty().append('<option value="">Select Section</option>');
            res.data.forEach(s => $s.append(`<option value="${s.id}">${s.code}</option>`));
        });
    }
});

$('#sectionSelect').on('change', function () {
    const sectionId = $(this).val();
    if (!sectionId) return;
    $.get('{{ route("exams.marks-entry.subjects") }}', { exam_id: $('#examSelect').val(), section_id: sectionId }, function (res) {
        const $sub = $('#subjectSelect');
        $sub.prop('disabled', false).empty();
        if (!res.data.length) {
            $sub.append('<option value="">No subjects assigned to you here</option>');
        } else {
            $sub.append('<option value="">Select Subject</option>');
            res.data.forEach(s => $sub.append(`<option value="${s.id}">${s.name}</option>`));
        }
    });
});

$('#btnLoad').on('click', function () {
    const exam_id = $('#examSelect').val(), section_id = $('#sectionSelect').val(), subject_id = $('#subjectSelect').val();
    if (!exam_id || !section_id || !subject_id) { toastr.warning('Please select all filters first.'); return; }

    $.get('{{ route("exams.marks-entry.table") }}', { exam_id, section_id, subject_id }, function (res) {
        if (!res.success) { toastr.error(res.message); return; }

        currentTotalMarks = res.total_marks;
        isLocked = res.is_locked;
        $('#totalMarksDisplay').text(currentTotalMarks);
        $('#lockedWarning').toggleClass('d-none', !isLocked);
        $('.save-text').closest('button').prop('disabled', isLocked);

        let html = '';
        res.data.forEach(s => {
            const isAbsentOrLeave = s.is_absent || s.is_leave;
            html += `<tr class="${isAbsentOrLeave ? 'row-absent' : ''}" data-student="${s.student_id}">
                <td>${s.roll_number}</td><td>${s.name}</td><td>${s.father_name}</td>
                <td>
                    <input type="number" class="mark-input" min="0" max="${currentTotalMarks}"
                        value="${isAbsentOrLeave ? 0 : (s.obtained_marks ?? '')}"
                        ${isAbsentOrLeave || isLocked ? 'disabled' : ''}>
                </td>
                <td>
                    ${s.is_absent ? '<span class="absent-badge">ABSENT</span>' : ''}
                    ${s.is_leave ? '<span class="leave-badge">LEAVE</span>' : ''}
                    ${!isAbsentOrLeave ? '<span class="text-success small">—</span>' : ''}
                </td>
            </tr>`;
        });

        $('#marksTableBody').html(html);
        $('#marksTableBox').removeClass('d-none');
        $('#emptyState').addClass('d-none');
    });
});

$(document).on('input', '.mark-input', function () {
    let val = parseInt($(this).val());
    if (val > currentTotalMarks) $(this).val(currentTotalMarks); // real-time clamp
    $(this).removeClass('empty-highlight');
});

$('#btnHighlightEmpty').on('click', function () {
    $('.mark-input').each(function () {
        if ($(this).val() === '' && !$(this).prop('disabled')) $(this).addClass('empty-highlight');
    });
});

function saveMarks() {
    const marks = [];
    $('#marksTableBody tr').each(function () {
        marks.push({
            student_id: $(this).data('student'),
            obtained_marks: $(this).find('.mark-input').val() || 0,
        });
    });

    $('.save-text').html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.post('{{ route("exams.marks-entry.save") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        exam_id: $('#examSelect').val(), section_id: $('#sectionSelect').val(),
        subject_id: $('#subjectSelect').val(), marks,
    }).done(function (res) {
        toastr.success(res.message);
    }).fail(function (xhr) {
        toastr.error(xhr.responseJSON?.message || 'Save failed.');
    }).always(function () {
        $('.save-text').text('Save All');
    });
}

$('#btnSaveTop, #btnSaveBottom').on('click', saveMarks);
</script>
@endpush
