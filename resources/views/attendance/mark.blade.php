@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('breadcrumb')
    <span class="bc-current">Attendance</span>
@endsection

@push('styles')
<style>
    .att-filters { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:16px; }
    .att-filters select, .att-filters input { padding:9px 12px; border:2px solid #e9ecef;
        border-radius:8px; font-size:13px; min-width:160px; background:#fff; }
    .holiday-banner { background:#e8f4ff; border:1px solid #3498DB; border-radius:10px;
        padding:14px 18px; color:#1a5276; margin-bottom:16px; display:none; }
    .att-row { display:flex; align-items:center; gap:14px; padding:10px 14px;
        border-bottom:1px solid #f5f5f5; }
    .att-row:hover { background:#EBF5FB; }
    .att-photo { width:34px; height:34px; border-radius:50%; object-fit:cover; }
    .att-btn-group { display:flex; gap:6px; margin-left:auto; }
    .att-btn { width:36px; height:36px; border-radius:8px; border:2px solid #e9ecef;
        background:#fff; font-size:12px; font-weight:700; cursor:pointer; transition:all .15s; }
    .att-btn.p.active { background:#27AE60; border-color:#27AE60; color:#fff; }
    .att-btn.a.active { background:#E74C3C; border-color:#E74C3C; color:#fff; }
    .att-btn.l.active { background:#F39C12; border-color:#F39C12; color:#fff; }
    .late-subbadge { font-size:9px; background:#F39C12; color:#fff; padding:1px 5px;
        border-radius:6px; margin-left:4px; }
    .bulk-actions { display:flex; gap:8px; margin-bottom:12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-clipboard-check"></i></span>
        Mark Attendance
    </h1>
</div>

<div class="card-custom mb-3">
    <div class="card-body-c">
        <div class="att-filters">
            <select id="sectionSelect">
                <option value="">Select Section</option>
                @foreach($sections as $s)
                    <option value="{{ $s->id }}">{{ $s->code }} ({{ ucfirst($s->campus) }}, {{ ucfirst($s->year) }})</option>
                @endforeach
            </select>
            <input type="date" id="dateSelect" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
            <button class="btn btn-sm" id="btnLoad" style="background:#1E3A5F;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-sync-alt me-1"></i> Load
            </button>
        </div>
    </div>
</div>

<div class="holiday-banner" id="holidayBanner">
    <i class="fa-solid fa-umbrella-beach me-2"></i>
    This date is marked as a <b>Holiday</b> for this campus. Attendance is not required.
</div>

<div class="card-custom" id="attendanceCard" style="display:none;">
    <div class="card-body-c">
        <div class="bulk-actions">
            <button class="btn btn-sm" id="btnMarkAllPresent" style="background:#27AE60;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-check-double me-1"></i> Mark All Present
            </button>
            <button class="btn btn-sm" id="btnMarkAllAbsent" style="background:#E74C3C;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-xmark me-1"></i> Mark All Absent
            </button>
            <span class="ms-auto small text-muted align-self-center" id="lateCutoffNote"></span>
        </div>

        <div id="studentRows"></div>

        <div class="d-flex justify-content-end mt-3">
            <button class="btn" id="btnSaveAttendance" style="background:#1E3A5F;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-save me-1"></i> <span id="btnSaveText">Save Attendance</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let studentsData = [];
let lateCutoff = '08:30';

$('#btnLoad').on('click', function () {
    const sectionId = $('#sectionSelect').val(), date = $('#dateSelect').val();
    if (!sectionId || !date) { toastr.warning('Select section and date first.'); return; }

    $.get('{{ route("attendance.mark.students") }}', { section_id: sectionId, date }, function (res) {
        lateCutoff = res.late_cutoff;
        $('#lateCutoffNote').text('Late cutoff: ' + lateCutoff);

        if (res.is_holiday) {
            $('#holidayBanner').show();
            $('#attendanceCard').hide();
            return;
        }
        $('#holidayBanner').hide();
        $('#attendanceCard').show();

        studentsData = res.data;
        renderRows();
    });
});

function renderRows() {
    let html = '';
    studentsData.forEach(s => {
        html += `
        <div class="att-row" data-id="${s.id}">
            <img src="${s.photo_url}" class="att-photo">
            <span style="min-width:70px;font-size:12px;color:#6C757D;">${s.roll_number}</span>
            <span style="flex:1;">${s.name} ${s.is_late ? '<span class="late-subbadge">LATE</span>' : ''}</span>
            <div class="att-btn-group">
                <button class="att-btn p ${s.status === 'present' ? 'active' : ''}" data-status="present" title="Present">P</button>
                <button class="att-btn a ${s.status === 'absent' ? 'active' : ''}" data-status="absent" title="Absent">A</button>
                <button class="att-btn l ${s.status === 'leave' ? 'active' : ''}" data-status="leave" title="Leave">L</button>
            </div>
        </div>`;
    });
    $('#studentRows').html(html);
}

$(document).on('click', '.att-btn', function () {
    const $row = $(this).closest('.att-row');
    $row.find('.att-btn').removeClass('active');
    $(this).addClass('active');

    const id = $row.data('id');
    const student = studentsData.find(s => s.id == id);
    student.status = $(this).data('status');

    // Live late-detection preview if marking Present right now
    if (student.status === 'present') {
        const now = new Date();
        const nowStr = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
        student.is_late = nowStr > lateCutoff;
        renderRows();
    }
});

$('#btnMarkAllPresent').on('click', function () {
    studentsData.forEach(s => s.status = 'present');
    renderRows();
});
$('#btnMarkAllAbsent').on('click', function () {
    studentsData.forEach(s => s.status = 'absent');
    renderRows();
});

$('#btnSaveAttendance').on('click', function () {
    const records = studentsData.filter(s => s.status).map(s => ({ student_id: s.id, status: s.status }));
    if (!records.length) { toastr.warning('Mark at least one student before saving.'); return; }

    $('#btnSaveAttendance').prop('disabled', true);
    $('#btnSaveText').html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.post('{{ route("attendance.mark.save") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        section_id: $('#sectionSelect').val(),
        date: $('#dateSelect').val(),
        records,
    }).done(res => {
        toastr.success(res.message);
    }).fail(xhr => {
        toastr.error(xhr.responseJSON?.message || 'Failed to save attendance.');
    }).always(() => {
        $('#btnSaveAttendance').prop('disabled', false);
        $('#btnSaveText').text('Save Attendance');
    });
});
</script>
@endpush
