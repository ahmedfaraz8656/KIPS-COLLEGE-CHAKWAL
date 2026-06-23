@extends('layouts.app')

@section('title', 'Audit Trail')

@section('breadcrumb')
    <span class="bc-current">Audit Trail</span>
@endsection

@push('styles')
<style>
    table.audit-table th { background: #1E3A5F; color: #fff; padding: 8px; font-size: 12px; text-align: center; }
    table.audit-table td { padding: 8px; text-align: center; font-size: 12.5px; }
    .action-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 8px; }
    .readonly-badge { background: rgba(108,117,125,.15); color: #6C757D; font-size: 11px; padding: 3px 10px; border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
        System Audit Logs
        <span class="readonly-badge ms-2">Read Only</span>
    </h1>
    <a href="{{ route('audit-trail.pdf') }}" class="btn btn-sm" style="background:#E74C3C;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
    </a>
</div>

<div class="card-custom mb-3">
    <div class="card-body-c">
        <div class="d-flex gap-2 flex-wrap">
            <select id="fUser" class="form-select form-select-sm" style="max-width:160px;">
                <option value="">All Users</option>
                @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
            </select>
            <select id="fAction" class="form-select form-select-sm" style="max-width:140px;">
                <option value="">All Actions</option>
                @foreach($actions as $a)<option value="{{ $a }}">{{ $a }}</option>@endforeach
            </select>
            <select id="fModule" class="form-select form-select-sm" style="max-width:160px;">
                <option value="">All Modules</option>
                @foreach($modules as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
            </select>
            <input type="date" id="fFrom" class="form-control form-control-sm" style="max-width:150px;">
            <input type="date" id="fTo" class="form-control form-control-sm" style="max-width:150px;">
            <input type="text" id="fSearch" class="form-control form-control-sm" placeholder="Search..." style="flex:1;min-width:160px;">
        </div>
    </div>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <table class="audit-table w-100">
            <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Module</th><th>Reference</th></tr></thead>
            <tbody id="auditBody"><tr><td colspan="5" class="text-muted py-3">Loading...</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadLogs() {
    $.get('{{ route("audit-trail.index") }}', {
        user_id: $('#fUser').val(), action: $('#fAction').val(), module: $('#fModule').val(),
        from: $('#fFrom').val(), to: $('#fTo').val(), search: $('#fSearch').val(),
    }, function (res) {
        if (!res.data.length) {
            $('#auditBody').html('<tr><td colspan="5" class="text-muted py-4"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>No Records Found</td></tr>');
            return;
        }
        let html = '';
        const colors = { CREATE:'#27AE60', UPDATE:'#3498DB', DELETE:'#E74C3C', MOVE:'#F39C12', PROMOTE:'#9B59B6', LOGIN:'#6C757D', LOGOUT:'#6C757D' };
        res.data.forEach(l => {
            html += `<tr><td>${l.date}</td><td>${l.user}</td>
                <td><span class="action-badge" style="background:${colors[l.action]||'#6C757D'}22;color:${colors[l.action]||'#6C757D'};">${l.action}</span></td>
                <td>${l.module}</td><td class="text-start">${l.description}</td></tr>`;
        });
        $('#auditBody').html(html);
    });
}

$('#fUser, #fAction, #fModule, #fFrom, #fTo').on('change', loadLogs);
$('#fSearch').on('input', () => { clearTimeout(window._a); window._a = setTimeout(loadLogs, 300); });

loadLogs();
</script>
@endpush
