@extends('layouts.app')

@section('title', 'Attendance Reports')

@section('breadcrumb')
    <span class="bc-current">Attendance Reports</span>
@endsection

@push('styles')
<style>
    .att-filters { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:16px; }
    .att-filters select, .att-filters input { padding:9px 12px; border:2px solid #e9ecef;
        border-radius:8px; font-size:13px; background:#fff; }
    .quick-filter { padding:6px 12px; border:1px solid #e9ecef; border-radius:20px;
        font-size:12px; cursor:pointer; background:#fff; }
    .quick-filter.active { background:#1E3A5F; color:#fff; border-color:#1E3A5F; }
    table.report-table thead th { background:#1E3A5F; color:#fff; text-align:center; padding:10px; font-weight:600; }
    table.report-table tbody td { padding:9px; text-align:center; vertical-align:middle; }
    table.report-table tbody tr:nth-child(even) { background:#F8F9FA; }
    table.report-table tbody tr:hover { background:#EBF5FB; }
    .pct-good { color:#27AE60; font-weight:700; }
    .pct-warning { color:#F39C12; font-weight:700; }
    .pct-low { color:#E74C3C; font-weight:700; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-chart-line"></i></span>
        Attendance Reports
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-sm" id="btnExportExcel" style="background:#27AE60;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-file-excel me-1"></i> Export Excel
        </button>
        <button class="btn btn-sm" id="btnExportPdf" style="background:#E74C3C;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
        </button>
    </div>
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
            <input type="date" id="fromDate">
            <span>to</span>
            <input type="date" id="toDate" max="{{ now()->format('Y-m-d') }}">
            <button class="btn btn-sm" id="btnRun" style="background:#1E3A5F;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Run Report
            </button>
        </div>
        <div class="d-flex gap-2 mt-2">
            <span class="quick-filter" data-days="7">1 Week</span>
            <span class="quick-filter" data-days="30">1 Month</span>
            <span class="quick-filter" data-days="60">2 Months</span>
            <span class="quick-filter" data-days="90">3 Months</span>
            <span class="quick-filter" data-days="180">6 Months</span>
        </div>
    </div>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <div class="table-responsive">
            <table class="report-table w-100">
                <thead>
                    <tr>
                        <th>Roll</th><th>Name</th><th>Working Days</th><th>Present</th>
                        <th>Absent</th><th>Leave</th><th>Late</th><th>%</th><th>Status</th>
                    </tr>
                </thead>
                <tbody id="reportBody">
                    <tr><td colspan="9" class="text-center py-4 text-muted">Select a section and date range, then click "Run Report"</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('.quick-filter').on('click', function () {
    $('.quick-filter').removeClass('active'); $(this).addClass('active');
    const days = $(this).data('days');
    const today = new Date();
    const from = new Date(today); from.setDate(from.getDate() - days);
    $('#toDate').val(today.toISOString().split('T')[0]);
    $('#fromDate').val(from.toISOString().split('T')[0]);
});

$('#btnRun').on('click', function () {
    const sectionId = $('#sectionSelect').val(), from = $('#fromDate').val(), to = $('#toDate').val();
    if (!sectionId || !from || !to) { toastr.warning('Select section and both dates.'); return; }

    $('#reportBody').html('<tr><td colspan="9" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading...</td></tr>');

    $.get('{{ route("attendance.reports.section") }}', { section_id: sectionId, from, to }, function (res) {
        if (!res.data.length) {
            $('#reportBody').html('<tr><td colspan="9" class="text-center py-4 text-muted">No Records Found</td></tr>');
            return;
        }
        let html = '';
        res.data.forEach(r => {
            const pctClass = r.status === 'good' ? 'pct-good' : (r.status === 'warning' ? 'pct-warning' : 'pct-low');
            html += `<tr>
                <td>${r.roll_number}</td><td class="text-start">${r.name}</td>
                <td>${r.working_days}</td><td>${r.present}</td><td>${r.absent}</td>
                <td>${r.leave}</td><td>${r.late}</td>
                <td class="${pctClass}">${r.percent}%</td>
                <td>${r.status === 'good' ? '✅ Good' : (r.status === 'warning' ? '⚠️ Warning' : '🔴 Low')}</td>
            </tr>`;
        });
        $('#reportBody').html(html);
    });
});
</script>
@endpush
