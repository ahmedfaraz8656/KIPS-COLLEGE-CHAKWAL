@extends('layouts.app')

@section('title', 'Marks Entry')

@section('breadcrumb')
    <a href="{{ route('exams.index') }}" class="bc-item">Examinations</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Marks Entry</span>
@endsection

@push('styles')
<style>
    /* Filter bar */
    .me-filter { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; }
    .me-filter-group { display:flex; flex-direction:column; gap:4px; }
    .me-filter-group label { font-size:11px; font-weight:700; color:#6C757D; text-transform:uppercase; letter-spacing:.4px; }
    .me-filter-group select { padding:9px 14px; border:2px solid #e9ecef; border-radius:8px; font-size:13px; min-width:160px; background:#fff; }

    /* Marks table */
    table.me-table { width:100%; border-collapse:collapse; }
    table.me-table thead tr th {
        background:#1E3A5F; color:#fff; padding:10px 12px; font-size:12px;
        font-weight:600; text-align:center; white-space:nowrap;
    }
    table.me-table tbody tr { border-bottom:1px solid #f5f5f5; transition:background .15s; }
    table.me-table tbody tr:hover { background:#EBF5FB; }
    table.me-table td { padding:10px 12px; font-size:13px; text-align:center; vertical-align:middle; }
    table.me-table td.td-name { text-align:left; }
    .photo-sm { width:30px; height:30px; border-radius:50%; object-fit:cover; }
    input.marks-input {
        width:80px; text-align:center; padding:6px; border:2px solid #e9ecef;
        border-radius:8px; font-size:13px; font-weight:600; outline:none; transition:border-color .15s;
    }
    input.marks-input:focus { border-color:#1E3A5F; }
    input.marks-input.over-limit { border-color:#E74C3C; background:#fff5f5; }
    input.marks-input.empty-warn { border-color:#F39C12; background:#fffbf0; }
    .row-absent { background:rgba(231,76,60,0.04) !important; }
    .row-leave  { background:rgba(243,156,18,0.04) !important; }
    .row-absent td, .row-leave td { opacity:.7; }
    .badge-ab { background:rgba(231,76,60,.12); color:#E74C3C; font-size:10px; font-weight:700; padding:3px 8px; border-radius:8px; }
    .badge-lv { background:rgba(243,156,18,.12); color:#F39C12; font-size:10px; font-weight:700; padding:3px 8px; border-radius:8px; }

    /* Total marks pill */
    .total-pill {
        background:rgba(30,58,95,.08); color:#1E3A5F; font-weight:700; font-size:13px;
        padding:8px 18px; border-radius:12px; display:inline-flex; align-items:center; gap:8px;
    }
    .locked-notice { background:rgba(231,76,60,.08); border:1px solid rgba(231,76,60,.2);
        border-radius:10px; padding:12px 18px; font-size:13px; color:#c0392b; display:none; }
    .progress-bar-thin { height:6px; background:#f0f2f5; border-radius:20px; overflow:hidden; margin-bottom:14px; }
    .progress-bar-fill { height:100%; background:#27AE60; border-radius:20px; transition:width .4s ease; }

    /* Stat bar above table */
    .me-stat-bar { display:flex; gap:16px; flex-wrap:wrap; align-items:center; padding:12px 0; margin-bottom:6px; }
    .me-stat { font-size:12px; color:#6C757D; display:flex; align-items:center; gap:6px; }
    .me-stat b { color:#2C3E50; }
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

{{-- FILTERS --}}
<div class="card-custom mb-3">
    <div class="card-body-c">
        <div class="me-filter">
            <div class="me-filter-group">
                <label>Exam</label>
                <select id="examSelect">
                    <option value="">— Select Exam —</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }} ({{ $exam->exam_date->format('d M Y') }})</option>
                    @endforeach
                </select>
            </div>
            <div class="me-filter-group">
                <label>Year</label>
                <select id="yearSelect" disabled>
                    <option value="">— Year —</option>
                    <option value="first">First Year</option>
                    <option value="second">Second Year</option>
                </select>
            </div>
            <div class="me-filter-group">
                <label>Campus</label>
                <select id="campusSelect" disabled>
                    <option value="">— Campus —</option>
                    <option value="boys">Boys</option>
                    <option value="girls">Girls</option>
                </select>
            </div>
            <div class="me-filter-group">
                <label>Section</label>
                <select id="sectionSelect" disabled><option value="">— Section —</option></select>
            </div>
            <div class="me-filter-group">
                <label>Subject</label>
                <select id="subjectSelect" disabled><option value="">— Subject —</option></select>
            </div>
            <div class="me-filter-group">
                <label>&nbsp;</label>
                <button class="btn btn-sm" id="btnLoad" style="background:#1E3A5F;color:#fff;border-radius:8px;padding:9px 18px;">
                    <i class="fa-solid fa-table me-1"></i> Load Students
                </button>
            </div>
        </div>
    </div>
</div>

{{-- LOCKED NOTICE --}}
<div class="locked-notice" id="lockedNotice">
    <i class="fa-solid fa-lock me-2"></i>
    <strong>Marks entry is locked.</strong> The due date for this exam has passed.
    Please ask the Principal to extend the due date before editing marks.
</div>

{{-- MAIN MARKS TABLE AREA --}}
<div id="marksArea" style="display:none;">
    <div class="card-custom">
        <div class="card-body-c">

            {{-- Stats row --}}
            <div class="me-stat-bar">
                <div class="total-pill"><i class="fa-solid fa-star-half-stroke"></i> Total Marks: <span id="totalDisplay">0</span></div>
                <div class="me-stat"><i class="fa-solid fa-users"></i> Students: <b id="statTotal">0</b></div>
                <div class="me-stat"><i class="fa-solid fa-circle-check" style="color:#27AE60;"></i> Present: <b id="statPresent">0</b></div>
                <div class="me-stat"><i class="fa-solid fa-circle-xmark" style="color:#E74C3C;"></i> Absent: <b id="statAbsent">0</b></div>
                <div class="me-stat"><i class="fa-solid fa-plane-departure" style="color:#F39C12;"></i> Leave: <b id="statLeave">0</b></div>
                <div class="ms-auto d-flex gap-2">
                    <button class="btn btn-sm" id="btnHighlight" style="background:#F39C12;color:#fff;border-radius:8px;">
                        <i class="fa-solid fa-highlighter me-1"></i> Highlight Empty
                    </button>
                    <button class="btn btn-sm" id="btnSave" style="background:#27AE60;color:#fff;border-radius:8px;">
                        <i class="fa-solid fa-save me-1"></i> <span id="btnSaveText">Save All</span>
                    </button>
                </div>
            </div>

            {{-- Progress of filled entries --}}
            <div class="progress-bar-thin"><div class="progress-bar-fill" id="fillProgress" style="width:0%;"></div></div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="me-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Roll No</th>
                            <th class="text-start">Name</th>
                            <th>Obtained Marks</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="marksBody"></tbody>
                </table>
            </div>

            {{-- Save button bottom --}}
            <div class="text-end mt-3">
                <button class="btn btn-sm" id="btnSaveBottom" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-save me-1"></i> Save All Marks
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Empty state --}}
<div id="emptyState">
    <div class="empty-state-block">
        <i class="fa-solid fa-table-list"></i>
        <p>Select Exam → Year → Campus → Section → Subject,<br>then click <strong>Load Students</strong>.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
let totalMarks = 0, isLocked = false;

$('#examSelect').on('change', function () {
    $('#yearSelect, #campusSelect').prop('disabled', !$(this).val());
    $('#sectionSelect, #subjectSelect').prop('disabled', true).find('option:gt(0)').remove();
    hideTable();
});
$('#yearSelect, #campusSelect').on('change', loadSections);
$('#sectionSelect').on('change', loadSubjects);

function loadSections() {
    const examId = $('#examSelect').val(), year = $('#yearSelect').val(), campus = $('#campusSelect').val();
    if (!examId || !year || !campus) return;
    $.get('{{ route("exams.marks-entry.sections") }}', { exam_id: examId, campus, year }, function (res) {
        const $s = $('#sectionSelect').prop('disabled', false).empty().append('<option value="">— Section —</option>');
        res.data.forEach(s => $s.append(`<option value="${s.id}">${s.code}</option>`));
        $('#subjectSelect').prop('disabled', true).empty().append('<option value="">— Subject —</option>');
        hideTable();
    });
}

function loadSubjects() {
    const examId = $('#examSelect').val(), sectionId = $('#sectionSelect').val();
    if (!sectionId) return;
    $.get('{{ route("exams.marks-entry.subjects") }}', { exam_id: examId, section_id: sectionId }, function (res) {
        const $s = $('#subjectSelect').prop('disabled', false).empty();
        if (!res.data.length) {
            $s.append('<option value="">No subjects assigned to you</option>');
        } else {
            $s.append('<option value="">— Subject —</option>');
            res.data.forEach(s => $s.append(`<option value="${s.id}">${s.name}</option>`));
        }
        hideTable();
    });
}

$('#btnLoad').on('click', function () {
    const exam_id = $('#examSelect').val(), section_id = $('#sectionSelect').val(), subject_id = $('#subjectSelect').val();
    if (!exam_id || !section_id || !subject_id) { toastr.warning('Please complete all filter selections first.'); return; }

    $('#btnLoad').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Loading...');

    $.get('{{ route("exams.marks-entry.table") }}', { exam_id, section_id, subject_id }, function (res) {
        if (!res.success) { toastr.error(res.message); return; }

        totalMarks = res.total_marks;
        isLocked = res.is_locked;

        $('#totalDisplay').text(totalMarks);
        $('#lockedNotice').toggle(isLocked);
        $('#btnSave, #btnSaveBottom').prop('disabled', isLocked);

        let absent = 0, leave = 0, present = 0;
        let html = '';

        res.data.forEach((s, i) => {
            const isAb = s.is_absent, isLv = s.is_leave;
            if (isAb) absent++;
            else if (isLv) leave++;
            else present++;

            html += `<tr class="${isAb ? 'row-absent' : isLv ? 'row-leave' : ''}" data-id="${s.student_id}">
                <td>${i+1}</td>
                <td><img src="${s.photo_url ?? '/img/default-avatar.png'}" class="photo-sm"></td>
                <td><b>${s.roll_number}</b></td>
                <td class="td-name">${s.name}</td>
                <td>
                    <input type="number" class="marks-input" min="0" max="${totalMarks}"
                        value="${(isAb || isLv) ? 0 : (s.obtained_marks ?? '')}"
                        ${(isAb || isLv || isLocked) ? 'disabled' : ''}>
                </td>
                <td>
                    ${isAb ? '<span class="badge-ab">AB</span>' : ''}
                    ${isLv ? '<span class="badge-lv">LEAVE</span>' : ''}
                    ${(!isAb && !isLv) ? '<span class="text-muted small">—</span>' : ''}
                </td>
            </tr>`;
        });

        $('#marksBody').html(html);
        $('#statTotal').text(res.data.length);
        $('#statPresent').text(present);
        $('#statAbsent').text(absent);
        $('#statLeave').text(leave);
        updateProgress();

        $('#emptyState').hide();
        $('#marksArea').show();
    }).fail(() => toastr.error('Failed to load marks table.'))
      .always(() => $('#btnLoad').prop('disabled', false).html('<i class="fa-solid fa-table me-1"></i> Load Students'));
});

$(document).on('input', '.marks-input', function () {
    const val = parseInt($(this).val()), max = totalMarks;
    $(this).toggleClass('over-limit', !isNaN(val) && val > max);
    if (!isNaN(val) && val > max) $(this).val(max);
    $(this).removeClass('empty-warn');
    updateProgress();
});

function updateProgress() {
    const inputs = $('.marks-input:not([disabled])');
    const filled = inputs.filter(function () { return $(this).val() !== ''; }).length;
    const pct = inputs.length > 0 ? Math.round((filled / inputs.length) * 100) : 0;
    $('#fillProgress').css('width', pct + '%');
}

$('#btnHighlight').on('click', function () {
    $('.marks-input:not([disabled])').each(function () {
        if ($(this).val() === '') $(this).addClass('empty-warn');
    });
    toastr.info('Empty fields highlighted in orange.');
});

function doSave() {
    if (isLocked) { toastr.error('Marks entry is locked for this exam.'); return; }
    const marks = [];
    let hasOverLimit = false;
    $('#marksBody tr').each(function () {
        const $inp = $(this).find('.marks-input');
        const val = parseInt($inp.val()) || 0;
        if (val > totalMarks) { hasOverLimit = true; return false; }
        marks.push({ student_id: $(this).data('id'), obtained_marks: val });
    });
    if (hasOverLimit) { toastr.error(`Some marks exceed the maximum (${totalMarks}). Please fix before saving.`); return; }

    $('#btnSave, #btnSaveBottom').prop('disabled', true);
    $('#btnSaveText').html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.post('{{ route("exams.marks-entry.save") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        exam_id: $('#examSelect').val(),
        section_id: $('#sectionSelect').val(),
        subject_id: $('#subjectSelect').val(),
        marks,
    }).done(res => toastr.success(res.message))
      .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to save marks.'))
      .always(() => {
          $('#btnSave, #btnSaveBottom').prop('disabled', false);
          $('#btnSaveText').text('Save All');
      });
}

$('#btnSave, #btnSaveBottom').on('click', doSave);

function hideTable() {
    $('#marksArea').hide();
    $('#emptyState').show();
    $('#lockedNotice').hide();
}
</script>
@endpush
