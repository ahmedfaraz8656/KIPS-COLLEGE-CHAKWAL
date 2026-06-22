@extends('layouts.app')

@section('title', 'Timetable')

@section('breadcrumb')
    <span class="bc-current">Timetable</span>
@endsection

@push('styles')
<style>
    .tt-table { width: 100%; border-collapse: collapse; }
    .tt-table th { background: #1E3A5F; color: #fff; padding: 8px; font-size: 11px; text-align: center; }
    .tt-table td { border: 1px solid #f0f0f0; padding: 4px; text-align: center; vertical-align: middle; height: 56px; min-width: 110px; }
    .period-time { font-size: 10px; color: #6C757D; }
    .tt-cell { cursor: pointer; border-radius: 8px; padding: 4px; transition: background .15s; }
    .tt-cell:hover { background: #EBF5FB; }
    .tt-cell.filled { background: rgba(30,58,95,.06); }
    .tt-subject { font-size: 12px; font-weight: 700; color: #1E3A5F; }
    .tt-teacher { font-size: 10px; color: #6C757D; }
    .section-selector { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .section-btn { padding: 7px 16px; border-radius: 20px; border: 2px solid #e9ecef; background: #fff;
        font-size: 13px; font-weight: 600; cursor: pointer; }
    .section-btn.active { background: #1E3A5F; color: #fff; border-color: #1E3A5F; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-table-cells"></i></span>
        Timetable
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-sm" style="background:#E74C3C;color:#fff;border-radius:8px;"><i class="fa-solid fa-file-pdf me-1"></i> Export PDF</button>
        <button class="btn btn-sm" style="background:#2C3E50;color:#fff;border-radius:8px;"><i class="fa-solid fa-print me-1"></i> Print</button>
    </div>
</div>

<div class="card-custom mb-3">
    <div class="card-body-c">
        <label class="form-label small fw-600 d-block mb-2">Select Section</label>
        <div class="section-selector">
            @foreach($sections as $section)
                <button class="section-btn" data-id="{{ $section->id }}">{{ $section->code }}</button>
            @endforeach
        </div>
    </div>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <div class="table-responsive">
            <table class="tt-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        @foreach($days as $day)<th>{{ $day }}</th>@endforeach
                    </tr>
                </thead>
                <tbody id="ttBody">
                    @foreach($periodSlots as $slot)
                    <tr>
                        <td><b>{{ $slot->period_number }}</b><br><span class="period-time">{{ $slot->start_time->format('h:i A') }}</span></td>
                        @foreach($days as $day)
                            <td class="tt-cell" data-day="{{ $day }}" data-period="{{ $slot->id }}"></td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted text-center small mt-3" id="selectPrompt">Select a section above to view/edit its timetable.</p>
    </div>
</div>

{{-- ASSIGN MODAL --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:14px 14px 0 0;">
                <h6 class="modal-title text-white" id="assignModalTitle">Assign Period</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small fw-600">Subject</label>
                <select id="assignSubject" class="form-select form-select-sm mb-3"></select>
                <label class="form-label small fw-600">Teacher</label>
                <select id="assignTeacher" class="form-select form-select-sm">
                    @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-clear-period" style="background:#E74C3C;color:#fff;border-radius:8px;">Clear Period</button>
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSaveAssign" style="background:#27AE60;color:#fff;border-radius:8px;">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
let activeSectionId = null, activeCell = null, activeEntryId = null;

$('.section-btn').on('click', function () {
    $('.section-btn').removeClass('active'); $(this).addClass('active');
    activeSectionId = $(this).data('id');
    $('#selectPrompt').hide();
    $('.tt-cell').removeClass('filled').html('');

    $.get(`/timetable/section/${activeSectionId}`, function (res) {
        res.data.forEach(e => {
            const $cell = $(`.tt-cell[data-day="${e.day}"][data-period="${e.period_slot_id}"]`);
            $cell.addClass('filled').data('entry', e.id).html(
                `<div class="tt-subject">${e.subject.name}</div><div class="tt-teacher">${e.teacher.name}</div>`
            );
        });
    });
});

$(document).on('click', '.tt-cell', function () {
    if (!activeSectionId) { toastr.warning('Select a section first.'); return; }
    activeCell = $(this);
    activeEntryId = $(this).data('entry') || null;

    $.get(`/timetable/section/${activeSectionId}/subjects`, function (res) {
        const $sub = $('#assignSubject').empty();
        res.data.forEach(s => $sub.append(`<option value="${s.id}">${s.name}</option>`));
        assignModal.show();
    });
});

$('#btnSaveAssign').on('click', function () {
    doAssign(false);
});

function doAssign(confirmConflict) {
    $.post('{{ route("timetable.assign") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        section_id: activeSectionId, day: activeCell.data('day'), period_slot_id: activeCell.data('period'),
        subject_id: $('#assignSubject').val(), teacher_id: $('#assignTeacher').val(),
        confirm_conflict: confirmConflict,
    }).done(function (res) {
        toastr.success(res.message);
        activeCell.addClass('filled').data('entry', res.data.id).html(
            `<div class="tt-subject">${res.data.subject.name}</div><div class="tt-teacher">${res.data.teacher.name}</div>`
        );
        assignModal.hide();
    }).fail(function (xhr) {
        if (xhr.status === 409) {
            Swal.fire({
                title: 'Conflict Detected', text: xhr.responseJSON.message, icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#F39C12', confirmButtonText: 'Save Anyway',
            }).then(r => { if (r.isConfirmed) doAssign(true); });
        } else {
            toastr.error(xhr.responseJSON?.message || 'Failed to save.');
        }
    });
}

$('.btn-clear-period').on('click', function () {
    if (!activeEntryId) { assignModal.hide(); return; }
    $.ajax({ url: `/timetable/entry/${activeEntryId}`, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
        .done(res => { toastr.success(res.message); activeCell.removeClass('filled').html(''); assignModal.hide(); });
});
</script>
@endpush
