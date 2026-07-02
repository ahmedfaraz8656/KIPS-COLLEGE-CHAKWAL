@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('breadcrumb')
    <a href="{{ route('settings.index') }}" class="bc-item">Settings</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Backup & Restore</span>
@endsection

@push('styles')
<style>
    table.bk-t th { background:#1E3A5F; color:#fff; padding:10px 12px; font-size:12px; font-weight:600; text-align:center; }
    table.bk-t td { padding:10px 12px; text-align:center; font-size:13px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
    table.bk-t td.td-l { text-align:left; }
    table.bk-t tr:hover td { background:#f8faff; }
    .btype-badge { font-size:10px; font-weight:700; padding:3px 9px; border-radius:8px; text-transform:uppercase; }
    .btype-manual   { background:rgba(30,58,95,.1);    color:#1E3A5F; }
    .btype-auto     { background:rgba(52,152,219,.12); color:#3498DB; }
    .btype-snapshot { background:rgba(243,156,18,.12); color:#F39C12; }
    .info-strip { background:rgba(52,152,219,.06); border:1px solid rgba(52,152,219,.2); border-radius:10px; padding:12px 16px; font-size:13px; color:#1a5276; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-database"></i></span>
        Backup & Restore
    </h1>
    <button class="btn btn-sm" id="btnCreateBackup" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-cloud-arrow-up me-1"></i> <span id="createBkText">Create Backup Now</span>
    </button>
</div>

{{-- Info strip --}}
<div class="info-strip mb-3">
    <i class="fa-solid fa-circle-info me-2"></i>
    <b>Auto backups</b> run nightly at 2:00 AM (last 30 retained).
    &nbsp;|&nbsp; <b>Snapshots</b> are created automatically before bulk-delete operations and expire after 7 days.
    &nbsp;|&nbsp; Restore requires typing <code>CONFIRM</code> exactly to prevent accidental data loss.
</div>

{{-- Backups table --}}
<div class="card-custom">
    <div class="card-body-c">
        @if($backups->isEmpty())
        <div class="empty-state-block py-5">
            <i class="fa-solid fa-database"></i>
            <p>No backups yet. Click <b>Create Backup Now</b> to create the first one.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="bk-t w-100">
                <thead>
                    <tr>
                        <th class="text-start">Backup Name / Label</th>
                        <th>Date</th>
                        <th>Size</th>
                        <th>Type</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $b)
                    <tr>
                        <td class="td-l">
                            @if($b->label)
                            <div style="font-weight:600;">{{ $b->label }}</div>
                            <div style="font-size:11px;color:#adb5bd;">{{ $b->filename }}</div>
                            @else
                            <code style="font-size:11.5px;">{{ $b->filename }}</code>
                            @endif
                        </td>
                        <td style="font-size:12px;white-space:nowrap;">
                            {{ $b->created_at->format('d M Y') }}<br>
                            <span style="color:#adb5bd;">{{ $b->created_at->format('h:i A') }}</span>
                        </td>
                        <td style="font-weight:600;">{{ $b->size_human }}</td>
                        <td><span class="btype-badge btype-{{ $b->type }}">{{ ucfirst($b->type) }}</span></td>
                        <td>{{ $b->createdBy?->name ?? 'System' }}</td>
                        <td>
                            <a href="{{ route('backup.download', $b) }}" class="btn btn-sm" title="Download"
                               style="background:#27AE60;color:#fff;border-radius:6px;">
                                <i class="fa-solid fa-download"></i>
                            </a>
                            <button class="btn btn-sm btn-restore" data-id="{{ $b->id }}" data-date="{{ $b->created_at->format('d M Y, h:i A') }}"
                                    title="Restore from this backup" style="background:#F39C12;color:#fff;border-radius:6px;">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                            <button class="btn btn-sm btn-del-backup" data-id="{{ $b->id }}"
                                    title="Delete" style="background:#E74C3C;color:#fff;border-radius:6px;">
                                <i class="fa-solid fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnCreateBackup').on('click', function () {
    $(this).prop('disabled', true);
    $('#createBkText').html('<span class="spinner-border spinner-border-sm"></span> Creating...');
    $.post('{{ route("backup.create") }}', { _token: $('meta[name="csrf-token"]').attr('content') })
        .done(res => { toastr.success(res.message); setTimeout(() => location.reload(), 800); })
        .fail(xhr => { toastr.error(xhr.responseJSON?.message || 'Backup failed.'); })
        .always(() => { $('#btnCreateBackup').prop('disabled', false); $('#createBkText').text('Create Backup Now'); });
});

$(document).on('click', '.btn-restore', function () {
    const id = $(this).data('id'), date = $(this).data('date');
    Swal.fire({
        title: '⚠️ Restore System?',
        html: `This will overwrite ALL current data with the backup from:<br><b>${date}</b><br><br>
               Type <b>CONFIRM</b> below to proceed:`,
        input: 'text', inputPlaceholder: 'Type CONFIRM here',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Restore',
        inputValidator: v => v !== 'CONFIRM' ? 'You must type CONFIRM exactly.' : null,
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(`/backup/${id}/restore`, { _token: $('meta[name="csrf-token"]').attr('content'), confirm: r.value })
            .done(res => { toastr.success(res.message); })
            .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Restore failed.'));
    });
});

$(document).on('click', '.btn-del-backup', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Delete this backup?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Delete' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/backup/${id}`, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
