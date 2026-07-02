@extends('layouts.app')

@section('title', 'Audit Trail')

@section('breadcrumb')
    <a href="{{ route('settings.index') }}" class="bc-item">Settings</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Audit Trail</span>
@endsection

@push('styles')
<style>
    .filter-row { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; }
    .fgrp { display:flex; flex-direction:column; gap:4px; }
    .fgrp label { font-size:11px; font-weight:700; color:#6C757D; text-transform:uppercase; letter-spacing:.3px; }
    .fgrp select, .fgrp input { padding:8px 12px; border:2px solid #e9ecef; border-radius:8px; font-size:13px; min-width:140px; background:#fff; }
    .fgrp input[type=date] { min-width:140px; }
    .fgrp input[type=text] { min-width:200px; }

    table.audit-t th { background:#1E3A5F; color:#fff; padding:10px 12px; font-size:12px; font-weight:600; text-align:center; }
    table.audit-t td { padding:9px 12px; text-align:center; font-size:12.5px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
    table.audit-t td.td-l { text-align:left; }
    table.audit-t tr:hover td { background:#f8faff; }

    .action-badge { font-size:10px; font-weight:800; padding:3px 9px; border-radius:8px; text-transform:uppercase; white-space:nowrap; }
    .act-CREATE { background:rgba(39,174,96,.12);  color:#27AE60; }
    .act-UPDATE { background:rgba(52,152,219,.12); color:#3498DB; }
    .act-DELETE { background:rgba(231,76,60,.12);  color:#E74C3C; }
    .act-LOGIN  { background:rgba(108,117,125,.1); color:#6C757D; }
    .act-LOGOUT { background:rgba(108,117,125,.1); color:#6C757D; }
    .act-IMPORT { background:rgba(155,89,182,.12); color:#9B59B6; }
    .act-ENABLE { background:rgba(39,174,96,.12);  color:#27AE60; }
    .act-DISABLE{ background:rgba(231,76,60,.12);  color:#E74C3C; }
    .act-GENERATE { background:rgba(243,156,18,.12); color:#F39C12; }
    .readonly-ribbon {
        display:inline-flex; align-items:center; gap:7px; padding:6px 14px;
        background:rgba(231,76,60,.08); color:#c0392b; border-radius:30px; font-size:12px; font-weight:600;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
        System Audit Trail
    </h1>
    <div class="d-flex align-items-center gap-3">
        <span class="readonly-ribbon"><i class="fa-solid fa-lock"></i> Read Only — Immutable Log</span>
        <a href="{{ route('audit-trail.pdf') }}" class="btn btn-sm" style="background:#E74C3C;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card-custom mb-3">
    <div class="card-body-c">
        <div class="filter-row">
            <div class="fgrp">
                <label>User</label>
                <select id="fUser">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgrp">
                <label>Action</label>
                <select id="fAction">
                    <option value="">All Actions</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgrp">
                <label>Module</label>
                <select id="fModule">
                    <option value="">All Modules</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgrp">
                <label>From Date</label>
                <input type="date" id="fFrom">
            </div>
            <div class="fgrp">
                <label>To Date</label>
                <input type="date" id="fTo">
            </div>
            <div class="fgrp">
                <label>Search Description</label>
                <input type="text" id="fSearch" placeholder="Search...">
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card-custom">
    <div class="card-body-c">
        <div id="auditCount" class="small text-muted mb-2"></div>
        <div class="table-responsive">
            <table class="audit-t w-100">
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th class="text-start" style="min-width:280px;">Description</th>
                    </tr>
                </thead>
                <tbody id="auditBody">
                    <tr><td colspan="5" class="py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let debounceTimer;

function loadAudit() {
    $.get('{{ route("audit-trail.index") }}', {
        user_id: $('#fUser').val(),
        action:  $('#fAction').val(),
        module:  $('#fModule').val(),
        from:    $('#fFrom').val(),
        to:      $('#fTo').val(),
        search:  $('#fSearch').val(),
    }, function (res) {
        if (!res.data.length) {
            $('#auditBody').html('<tr><td colspan="5"><div class="empty-state-block py-4"><i class="fa-solid fa-inbox"></i><p>No audit records match the selected filters.</p></div></td></tr>');
            $('#auditCount').text('');
            return;
        }

        const actionClass = a => ({
            CREATE:'act-CREATE', UPDATE:'act-UPDATE', DELETE:'act-DELETE',
            LOGIN:'act-LOGIN',   LOGOUT:'act-LOGOUT', IMPORT:'act-IMPORT',
            ENABLE:'act-ENABLE', DISABLE:'act-DISABLE', GENERATE:'act-GENERATE',
        }[a] || 'act-LOGIN');

        let html = '';
        res.data.forEach(r => {
            html += `<tr>
                <td style="white-space:nowrap;font-size:12px;">${r.date}</td>
                <td style="font-weight:600;">${r.user}</td>
                <td><span class="action-badge ${actionClass(r.action)}">${r.action}</span></td>
                <td><span style="font-size:12px;color:#6C757D;">${r.module}</span></td>
                <td class="td-l" style="font-size:12.5px;">${r.description}</td>
            </tr>`;
        });
        $('#auditBody').html(html);
        $('#auditCount').text(`Showing ${res.data.length} of ${res.total ?? res.data.length} records`);
    });
}

$('#fUser, #fAction, #fModule, #fFrom, #fTo').on('change', loadAudit);
$('#fSearch').on('input', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(loadAudit, 300);
});

loadAudit();
</script>
@endpush
