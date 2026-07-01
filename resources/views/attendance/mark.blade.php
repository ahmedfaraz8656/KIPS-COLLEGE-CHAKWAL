@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('breadcrumb')
    <span class="bc-current">Mark Attendance</span>
@endsection

@push('styles')
<style>
    .att-filter-group { display:flex; flex-direction:column; gap:4px; }
    .att-filter-group label { font-size:11px; font-weight:700; color:#6C757D; text-transform:uppercase; letter-spacing:.4px; }
    .att-filter-group select, .att-filter-group input { padding:9px 14px; border:2px solid #e9ecef; border-radius:8px; font-size:13px; min-width:160px; background:#fff; }

    .holiday-banner {
        background:linear-gradient(135deg,rgba(52,152,219,.1),rgba(52,152,219,.05));
        border:1px solid rgba(52,152,219,.3); border-radius:12px; padding:14px 18px;
        color:#1a5276; margin-bottom:16px; display:none;
        display:flex; align-items:center; gap:12px; font-size:13px;
    }

    /* Attendance stat bar */
    .att-stat-bar { display:flex; gap:14px; flex-wrap:wrap; align-items:center; padding:12px 0; margin-bottom:6px; }
    .att-stat { font-size:12px; color:#6C757D; display:flex; align-items:center; gap:6px; }
    .att-stat b { color:#2C3E50; }
    .progress-thin { height:6px; background:#f0f2f5; border-radius:20px; overflow:hidden; margin-bottom:12px; }
    .progress-thin-fill { height:100%; background:#27AE60; border-radius:20px; transition:width .3s ease; }

    /* Student attendance row */
    .att-row { display:flex; align-items:center; gap:12px; padding:10px 14px; border-bottom:1px solid #f5f5f5; transition:background .15s; }
    .att-row:last-child { border-bottom:none; }
    .att-row:hover { background:#EBF5FB; }
    .att-row.was-saved { border-left:3px solid #27AE60; }
    .att-photo { width:34px; height:34px; border-radius:50%; object-fit:cover; flex-shrink:0; }
    .att-roll { min-width:70px; font-size:11.5px; color:#6C757D; font-weight:600; }
    .att-name { flex:1; font-size:13px; font-weight:500; color:#2C3E50; }
    .att-btns { display:flex; gap:6px; margin-left:auto; flex-shrink:0; }
    .att-btn {
        width:38px; height:38px; border-radius:10px; border:2px solid #e9ecef;
        background:#fff; font-size:12px; font-weight:700; cursor:pointer;
        transition:all .15s ease; display:flex; align-items:center; justify-content:center;
        color:#6C757D;
    }
    .att-btn:hover { transform:translateY(-1px); }
    .att-btn.p.sel { background:#27AE60; border-color:#27AE60; color:#fff; box-shadow:0 2px 8px rgba(39,174,96,.3); }
    .att-btn.a.sel { background:#E74C3C; border-color:#E74C3C; color:#fff; box-shadow:0 2px 8px rgba(231,76,60,.3); }
    .att-btn.l.sel { background:#F39C12; border-color:#F39C12; color:#fff; box-shadow:0 2px 8px rgba(243,156,18,.3); }
    .late-badge { font-size:9.5px; background:#F39C12; color:#fff; padding:2px 6px; border-radius:6px; flex-shrink:0; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-clipboard-check"></i></span>
        Mark Attendance
    </h1>
    <a href="{{ route('attendance.reports') }}" class="btn btn-sm" style="background:#3498DB;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-chart-bar me-1"></i> View Reports
    </a>
</div>

{{-- FILTERS --}}
<div class="card-custom mb-3">
    <div class="card-body-c">
        <div class="d-flex gap-3 flex-wrap align-items-end">
            <div class="att-filter-group">
                <label>Section</label>
                <select id="sectionSelect">
                    <option value="">— Select Section —</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}">{{ $s->code }} ({{ ucfirst($s->campus) }}, {{ ucfirst($s->year) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="att-filter-group">
                <label>Date</label>
                <input type="date" id="dateSelect" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="att-filter-group">
                <label>&nbsp;</label>
                <button class="btn btn-sm" id="btnLoad" style="background:#1E3A5F;color:#fff;border-radius:8px;padding:9px 20px;">
                    <i class="fa-solid fa-sync-alt me-1"></i> Load Students
                </button>
            </div>
        </div>
    </div>
</div>

{{-- HOLIDAY BANNER --}}
<div class="holiday-banner" id="holidayBanner" style="display:none;">
    <i class="fa-solid fa-umbrella-beach fa-lg text-info"></i>
    <div><b>Holiday</b> — This date is marked as a holiday for this campus. No attendance required.</div>
</div>

{{-- ATTENDANCE CARD --}}
<div class="card-custom" id="attendanceCard" style="display:none;">
    <div class="card-body-c">

        {{-- Stats + Bulk + Late info --}}
        <div class="att-stat-bar">
            <div class="att-stat"><i class="fa-solid fa-users" style="color:#1E3A5F;"></i> Total: <b id="statTotal">0</b></div>
            <div class="att-stat"><i class="fa-solid fa-circle-check" style="color:#27AE60;"></i> Present: <b id="statPresent">0</b></div>
            <div class="att-stat"><i class="fa-solid fa-circle-xmark" style="color:#E74C3C;"></i> Absent: <b id="statAbsent">0</b></div>
            <div class="att-stat"><i class="fa-solid fa-plane-departure" style="color:#F39C12;"></i> Leave: <b id="statLeave">0</b></div>
            <div class="att-stat text-muted" id="lateCutoffNote"></div>

            <div class="ms-auto d-flex gap-2">
                <button class="btn btn-sm" id="btnMarkAllP" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-check-double me-1"></i> All Present
                </button>
                <button class="btn btn-sm" id="btnMarkAllA" style="background:#E74C3C;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-xmark me-1"></i> All Absent
                </button>
            </div>
        </div>

        {{-- Progress: % of students marked --}}
        <div class="progress-thin"><div class="progress-thin-fill" id="markedProgress" style="width:0%;"></div></div>

        {{-- Student Rows --}}
        <div id="studentRows"></div>

        {{-- Save --}}
        <div class="d-flex justify-content-end pt-3 mt-3" style="border-top:1px solid #f5f5f5;">
            <button class="btn" id="btnSave" style="background:#1E3A5F;color:#fff;border-radius:8px;padding:10px 28px;font-weight:600;">
                <i class="fa-solid fa-save me-1"></i> <span id="btnSaveText">Save Attendance</span>
            </button>
        </div>
    </div>
</div>

{{-- EMPTY STATE --}}
<div id="emptyState">
    <div class="empty-state-block"><i class="fa-solid fa-clipboard-check"></i><p>Select a section and date, then click <b>Load Students</b>.</p></div>
</div>
@endsection

@push('scripts')
<script>
let studentsData = [], lateCutoff = '08:30';

$('#btnLoad').on('click', function () {
    const sectionId = $('#sectionSelect').val(), date = $('#dateSelect').val();
    if (!sectionId || !date) { toastr.warning('Please select a section and date first.'); return; }

    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.get('{{ route("attendance.mark.students") }}', { section_id: sectionId, date }, function (res) {
        lateCutoff = res.late_cutoff;
        $('#lateCutoffNote').html(`<i class="fa-regular fa-clock"></i> Late after: <b>${lateCutoff}</b>`);

        if (res.is_holiday) {
            $('#holidayBanner').show();
            $('#attendanceCard, #emptyState').hide();
            return;
        }

        $('#holidayBanner').hide();
        studentsData = res.data;
        renderRows();
        updateStats();
        $('#attendanceCard').show();
        $('#emptyState').hide();
    }).fail(() => toastr.error('Failed to load students.'))
      .always(() => $('#btnLoad').prop('disabled', false).html('<i class="fa-solid fa-sync-alt me-1"></i> Load Students'));
});

function renderRows() {
    let html = '';
    studentsData.forEach(s => {
        html += `<div class="att-row ${s.saved ? 'was-saved' : ''}" data-id="${s.id}">
            <img src="${s.photo_url ?? '/img/default-avatar.png'}" class="att-photo">
            <span class="att-roll">${s.roll_number}</span>
            <span class="att-name">${s.name}</span>
            ${s.is_late ? '<span class="late-badge"><i class="fa-solid fa-clock fa-xs me-1"></i>LATE</span>' : ''}
            <div class="att-btns">
                <button class="att-btn p ${s.status === 'present' ? 'sel' : ''}" data-status="present" title="Present">P</button>
                <button class="att-btn a ${s.status === 'absent'  ? 'sel' : ''}" data-status="absent"  title="Absent">A</button>
                <button class="att-btn l ${s.status === 'leave'   ? 'sel' : ''}" data-status="leave"   title="Leave">L</button>
            </div>
        </div>`;
    });
    $('#studentRows').html(html);
}

function updateStats() {
    const p = studentsData.filter(s => s.status === 'present').length;
    const a = studentsData.filter(s => s.status === 'absent').length;
    const l = studentsData.filter(s => s.status === 'leave').length;
    const marked = p + a + l;
    const total = studentsData.length;

    $('#statTotal').text(total);
    $('#statPresent').text(p);
    $('#statAbsent').text(a);
    $('#statLeave').text(l);
    $('#markedProgress').css('width', total > 0 ? Math.round((marked / total) * 100) + '%' : '0%');
}

$(document).on('click', '.att-btn', function () {
    const $row = $(this).closest('.att-row');
    const newStatus = $(this).data('status');
    $row.find('.att-btn').removeClass('sel');
    $(this).addClass('sel');

    const id = $row.data('id');
    const student = studentsData.find(s => s.id == id);
    student.status = newStatus;

    if (newStatus === 'present') {
        const now = new Date();
        const nowStr = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
        student.is_late = nowStr > lateCutoff;
    }

    updateStats();
});

$('#btnMarkAllP').on('click', function () {
    studentsData.forEach(s => { s.status = 'present'; s.is_late = false; });
    renderRows(); updateStats();
});
$('#btnMarkAllA').on('click', function () {
    studentsData.forEach(s => { s.status = 'absent'; });
    renderRows(); updateStats();
});

$('#btnSave').on('click', function () {
    const records = studentsData.map(s => ({ student_id: s.id, status: s.status || 'absent', is_late: s.is_late || false }));
    if (!records.length) { toastr.warning('No students to save.'); return; }

    $('#btnSave').prop('disabled', true);
    $('#btnSaveText').html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.post('{{ route("attendance.mark.save") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        section_id: $('#sectionSelect').val(),
        date: $('#dateSelect').val(),
        records,
    }).done(res => {
        toastr.success(res.message);
        studentsData.forEach(s => s.saved = true);
        renderRows();
    }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to save attendance.'))
      .always(() => {
          $('#btnSave').prop('disabled', false);
          $('#btnSaveText').text('Save Attendance');
      });
});
</script>
@endpush
