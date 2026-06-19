@extends('layouts.app')

@section('title', 'Section Transfer')

@section('breadcrumb')
    <a href="{{ route('students.index') }}" class="bc-item">Students</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Section Transfer</span>
@endsection

@push('styles')
<style>
    .transfer-col { background: #fff; border-radius: 14px; border: 1px solid #f0f0f0; padding: 18px; }
    .transfer-col h6 { font-size: 13px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;
        letter-spacing: .5px; margin-bottom: 14px; }
    .transfer-arrow { display: flex; align-items: center; justify-content: center;
        font-size: 28px; color: #F39C12; }
    select.t-select { width: 100%; padding: 9px 12px; border: 2px solid #e9ecef;
        border-radius: 8px; font-size: 13px; margin-bottom: 10px; background: #fff; }
    .student-pick-row { display: flex; align-items: center; gap: 10px; padding: 8px 10px;
        border-radius: 8px; font-size: 13px; }
    .student-pick-row:hover { background: #f8f9fa; }
    .section-count-badge { background: #1E3A5F; color: #fff; font-size: 10px;
        padding: 1px 7px; border-radius: 10px; margin-left: 6px; }
    #studentListBox { max-height: 380px; overflow-y: auto; border: 1px solid #f0f0f0;
        border-radius: 10px; padding: 6px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
        Section Transfer
    </h1>
    <a href="{{ route('students.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card-custom mb-3">
    <div class="card-body-c">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-600">Campus</label>
                <select id="campus" class="t-select">
                    <option value="">Select Campus</option>
                    <option value="boys">Boys</option>
                    <option value="girls">Girls</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-600">Year</label>
                <select id="year" class="t-select">
                    <option value="">Select Year</option>
                    <option value="first">First Year</option>
                    <option value="second">Second Year</option>
                </select>
            </div>
        </div>
        <p class="text-muted small mb-0 mt-2">
            <i class="fa-solid fa-circle-info"></i>
            Only sections matching the selected Campus + Year will appear below —
            this prevents moving students across campus or year by mistake.
        </p>
    </div>
</div>

<div class="row g-3">
    {{-- FROM --}}
    <div class="col-md-5">
        <div class="transfer-col">
            <h6><i class="fa-solid fa-arrow-up-from-bracket me-1"></i> From Section</h6>
            <select id="fromSection" class="t-select" disabled>
                <option value="">Select Campus + Year first</option>
            </select>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-muted">Select students to move</span>
                <button class="btn btn-sm p-0 text-primary" id="selectAllStudents" style="font-size:12px;">Select All</button>
            </div>

            <div id="studentListBox">
                <div class="text-center text-muted py-4" style="font-size:13px;">
                    <i class="fa-solid fa-users fa-2x mb-2 d-block opacity-25"></i>
                    Select a section to load students
                </div>
            </div>
        </div>
    </div>

    {{-- ARROW --}}
    <div class="col-md-2 d-flex align-items-center justify-content-center">
        <div class="transfer-arrow"><i class="fa-solid fa-circle-arrow-right"></i></div>
    </div>

    {{-- TO --}}
    <div class="col-md-5">
        <div class="transfer-col">
            <h6><i class="fa-solid fa-arrow-down-to-bracket me-1"></i> To Section</h6>
            <select id="toSection" class="t-select" disabled>
                <option value="">Select Campus + Year first</option>
            </select>

            <label class="form-label small fw-600 mt-2">Reason (optional)</label>
            <textarea id="reason" class="form-control" rows="3"
                placeholder="e.g. Parent requested section change"
                style="font-size:13px;border-radius:8px;"></textarea>

            <button class="btn w-100 mt-3" id="btnTransfer" disabled
                style="background:#F39C12;color:#fff;border-radius:8px;font-weight:600;">
                <i class="fa-solid fa-exchange-alt me-1"></i>
                <span id="btnTransferText">Transfer Selected</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedStudentIds = new Set();

function loadSections(targetSelect, excludeSectionId = null) {
    const campus = $('#campus').val();
    const year = $('#year').val();
    if (!campus || !year) return;

    $.get('{{ route("students.transfer.sections") }}', { campus, year }, function (res) {
        const $sel = $(targetSelect);
        $sel.prop('disabled', false).empty().append('<option value="">Select Section</option>');
        res.data.forEach(s => {
            if (s.id == excludeSectionId) return;
            $sel.append(`<option value="${s.id}">${s.code} <span class="section-count-badge">${s.count}</span></option>`.replace(/<span.*?<\/span>/, '') + ` (${s.count})`);
        });
    });
}

$('#campus, #year').on('change', function () {
    loadSections('#fromSection');
    loadSections('#toSection');
    $('#fromSection, #toSection').val('');
    $('#studentListBox').html('<div class="text-center text-muted py-4" style="font-size:13px;"><i class="fa-solid fa-users fa-2x mb-2 d-block opacity-25"></i>Select a section to load students</div>');
    selectedStudentIds.clear();
    checkTransferReady();
});

$('#fromSection').on('change', function () {
    const sectionId = $(this).val();
    selectedStudentIds.clear();

    if (!sectionId) {
        $('#studentListBox').html('<div class="text-center text-muted py-4" style="font-size:13px;">Select a section to load students</div>');
        return;
    }

    $.get('{{ route("students.transfer.students") }}', { section_id: sectionId }, function (res) {
        if (!res.data.length) {
            $('#studentListBox').html('<div class="text-center text-muted py-4" style="font-size:13px;">No active students in this section</div>');
            return;
        }
        let html = '';
        res.data.forEach(s => {
            html += `
                <label class="student-pick-row">
                    <input type="checkbox" class="pick-student" value="${s.id}">
                    <span class="text-muted" style="min-width:70px;font-size:12px;">${s.roll_number}</span>
                    <span>${s.name}</span>
                </label>`;
        });
        $('#studentListBox').html(html);
    });

    // Refresh "To" dropdown to exclude the currently selected "From" section
    loadSections('#toSection', sectionId);
});

$(document).on('change', '.pick-student', function () {
    const id = $(this).val();
    if (this.checked) selectedStudentIds.add(id); else selectedStudentIds.delete(id);
    checkTransferReady();
});

$('#selectAllStudents').on('click', function () {
    $('.pick-student').prop('checked', true).trigger('change');
});

$('#toSection').on('change', checkTransferReady);

function checkTransferReady() {
    const ready = selectedStudentIds.size > 0 && $('#toSection').val();
    $('#btnTransfer').prop('disabled', !ready);
}

$('#btnTransfer').on('click', function () {
    const toSectionCode = $('#toSection option:selected').text();
    const count = selectedStudentIds.size;

    Swal.fire({
        title: 'Move Student(s)?',
        html: `<b>${count}</b> student(s) will be moved to <b>${toSectionCode}</b>. Continue?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#F39C12',
        confirmButtonText: 'Yes, Move',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (!result.isConfirmed) return;

        const $btn = $('#btnTransfer');
        $btn.prop('disabled', true);
        $('#btnTransferText').html('<span class="spinner-border spinner-border-sm"></span> Moving...');

        $.post('{{ route("students.transfer.move") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            student_ids: Array.from(selectedStudentIds),
            to_section_id: $('#toSection').val(),
            reason: $('#reason').val(),
        }).done(function (res) {
            toastr.success(res.message);
            $('#fromSection').trigger('change');
            selectedStudentIds.clear();
            $('#reason').val('');
        }).fail(function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Transfer failed.');
        }).always(function () {
            $('#btnTransferText').text('Transfer Selected');
            checkTransferReady();
        });
    });
});
</script>
@endpush
