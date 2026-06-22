@extends('layouts.app')

@section('title', 'Academic Calendar')

@section('breadcrumb')
    <span class="bc-current">Academic Calendar</span>
@endsection

@push('styles')
<style>
    .cal-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .cal-month-label { font-size: 18px; font-weight: 700; color: #1E3A5F; min-width: 200px; text-align: center; }
    .cal-nav-btn { width: 36px; height: 36px; border-radius: 10px; border: 2px solid #e9ecef;
        background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: #1E3A5F; transition: all .2s; }
    .cal-nav-btn:hover { background: #1E3A5F; color: #fff; border-color: #1E3A5F; }

    .legend { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 16px; }
    .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #2C3E50; }
    .legend-dot { width: 11px; height: 11px; border-radius: 50%; display: inline-block; }

    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
    .cal-dow { text-align: center; font-weight: 700; font-size: 12px; color: #6C757D;
        text-transform: uppercase; padding-bottom: 6px; }

    .cal-cell { background: #fff; border: 1px solid #f0f0f0; border-radius: 12px;
        min-height: 110px; padding: 8px; cursor: pointer; transition: all .2s; position: relative; }
    .cal-cell:hover { border-color: #1E3A5F; box-shadow: 0 4px 14px rgba(30,58,95,.12); transform: translateY(-1px); }
    .cal-cell.is-today { border: 2px solid #1E3A5F; background: rgba(30,58,95,.03); }
    .cal-cell.other-month { opacity: .35; }

    .cal-date-num { width: 26px; height: 26px; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
        color: #2C3E50; margin: 0 auto 6px; }
    .cal-cell.is-today .cal-date-num { background: #1E3A5F; color: #fff; }

    .cal-event-pill { display: block; font-size: 10.5px; font-weight: 600; color: #fff;
        padding: 3px 6px; border-radius: 5px; margin-bottom: 3px; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis; }
    .cal-more { font-size: 10px; color: #6C757D; text-align: center; font-weight: 600; }

    @media (max-width: 768px) {
        .cal-grid { grid-template-columns: repeat(7, minmax(40px, 1fr)); gap: 4px; }
        .cal-cell { min-height: 70px; padding: 4px; }
        .cal-event-pill { font-size: 8px; padding: 2px 3px; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-calendar-alt"></i></span>
        Academic Calendar
    </h1>
    @can('manage calendar')
    <button class="btn btn-sm" id="btnAddEvent" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-plus me-1"></i> Add Event
    </button>
    @endcan
</div>

<div class="legend">
    <span class="legend-item"><span class="legend-dot" style="background:#E74C3C;"></span> Public Holiday</span>
    <span class="legend-item"><span class="legend-dot" style="background:#F39C12;"></span> College Holiday (Both)</span>
    <span class="legend-item"><span class="legend-dot" style="background:#F1C40F;"></span> Boys Only Holiday</span>
    <span class="legend-item"><span class="legend-dot" style="background:#9B59B6;"></span> Girls Only Holiday</span>
    <span class="legend-item"><span class="legend-dot" style="background:#3498DB;"></span> Exam Day</span>
    <span class="legend-item"><span class="legend-dot" style="background:#27AE60;"></span> College Event</span>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <div class="cal-nav">
            <button class="cal-nav-btn" id="btnPrevMonth"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="cal-month-label" id="monthLabel"></div>
            <button class="cal-nav-btn" id="btnNextMonth"><i class="fa-solid fa-chevron-right"></i></button>
        </div>

        <div class="cal-grid" id="calDowRow">
            <div class="cal-dow">Sun</div><div class="cal-dow">Mon</div><div class="cal-dow">Tue</div>
            <div class="cal-dow">Wed</div><div class="cal-dow">Thu</div><div class="cal-dow">Fri</div><div class="cal-dow">Sat</div>
        </div>
        <div class="cal-grid mt-2" id="calGrid"></div>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:14px 14px 0 0;">
                <h6 class="modal-title text-white">Add to Calendar</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-600">Date</label>
                        <input type="date" id="eDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Type</label>
                        <select id="eType" class="form-select form-select-sm">
                            <option value="holiday">Holiday</option><option value="event">College Event</option>
                        </select>
                    </div>
                    <div class="col-6" id="holidayTypeWrap">
                        <label class="form-label small fw-600">Holiday Type</label>
                        <select id="eHolidayType" class="form-select form-select-sm">
                            <option value="college">College Holiday</option><option value="public">Public Holiday</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Campus Scope</label>
                        <select id="eCampus" class="form-select form-select-sm">
                            <option value="both">Both Campuses</option><option value="boys">Boys Only</option><option value="girls">Girls Only</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-600">Title</label>
                        <input type="text" id="eTitle" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-600">Description (optional)</label>
                        <textarea id="eDescription" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSaveEvent" style="background:#27AE60;color:#fff;border-radius:8px;">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let curMonth = {{ $month }}, curYear = {{ $year }};
const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));

$('#eType').on('change', function () { $('#holidayTypeWrap').toggle($(this).val() === 'holiday'); });

function renderCalendar() {
    $('#monthLabel').text(monthNames[curMonth-1] + ' ' + curYear);

    const firstDay = new Date(curYear, curMonth-1, 1).getDay();
    const daysInMonth = new Date(curYear, curMonth, 0).getDate();
    const today = new Date();
    const todayStr = today.toISOString().slice(0,10);

    $.get('{{ route("calendar.events") }}', { month: curMonth, year: curYear }, function (res) {
        const eventsByDate = {};
        res.data.forEach(e => { (eventsByDate[e.date] = eventsByDate[e.date] || []).push(e); });

        let html = '';
        for (let i = 0; i < firstDay; i++) html += `<div class="cal-cell other-month"></div>`;

        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${curYear}-${String(curMonth).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const isToday = dateStr === todayStr;
            const dayEvents = eventsByDate[dateStr] || [];

            let pillsHtml = '';
            dayEvents.slice(0, 2).forEach(e => {
                pillsHtml += `<span class="cal-event-pill" style="background:${e.color};" title="${e.title}">${e.title}</span>`;
            });
            if (dayEvents.length > 2) pillsHtml += `<div class="cal-more">+${dayEvents.length - 2} more</div>`;

            html += `<div class="cal-cell ${isToday ? 'is-today' : ''}" data-date="${dateStr}">
                <div class="cal-date-num">${d}</div>
                ${pillsHtml}
            </div>`;
        }

        $('#calGrid').html(html);
    });
}

$('#btnPrevMonth').on('click', function () { curMonth--; if (curMonth < 1) { curMonth = 12; curYear--; } renderCalendar(); });
$('#btnNextMonth').on('click', function () { curMonth++; if (curMonth > 12) { curMonth = 1; curYear++; } renderCalendar(); });

@can('manage calendar')
$('#btnAddEvent').on('click', function () { $('#eDate').val(''); eventModal.show(); });
$(document).on('click', '.cal-cell:not(.other-month)', function () {
    $('#eDate').val($(this).data('date'));
    eventModal.show();
});
@endcan

$('#btnSaveEvent').on('click', function () {
    $.post('{{ route("calendar.event.store") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        date: $('#eDate').val(), title: $('#eTitle').val(), type: $('#eType').val(),
        holiday_type: $('#eHolidayType').val(), campus_scope: $('#eCampus').val(),
        description: $('#eDescription').val(),
    }).done(res => { toastr.success(res.message); eventModal.hide(); renderCalendar(); })
      .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to save.'));
});

renderCalendar();
</script>
@endpush
