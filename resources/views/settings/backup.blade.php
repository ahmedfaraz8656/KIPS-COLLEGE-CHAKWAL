@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('breadcrumb')
    <span class="bc-current">Backup & Restore</span>
@endsection

@push('styles')
<style>
    table.backup-table th { background: #1E3A5F; color: #fff; padding: 8px; font-size: 12px; text-align: center; }
    table.backup-table td { padding: 8px; text-align: center; font-size: 13px; }
    .type-badge { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-database"></i></span>
        Backup & Restore
    </h1>
    <button class="btn btn-sm" id="btnCreateBackup" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-plus me-1"></i> <span id="btnCreateBackupText">Create Backup Now</span>
    </button>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <table class="backup-table w-100">
            <thead><tr><th>Backup Name</th><th>Date</th><th>Size</th><th>Type</th><th>Created By</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($backups as $b)
                <tr>
                    <td>{{ $b->label ?? $b->filename }}</td>
                    <td>{{ $b->created_at->format('d-M-Y h:i A') }}</td>
                    <td>{{ $b->size_human }}</td>
                    <td><span class="type-badge" style="background:{{ ['auto'=>'rgba(52,152,219,.12)','manual'=>'rgba(30,58,95,.1)','snapshot'=>'rgba(243,156,18,.12)'][$b->type] }};
                        color:{{ ['auto'=>'#3498DB','manual'=>'#1E3A5F','snapshot'=>'#F39C12'][$b->type] }};">{{ ucfirst($b->type) }}</span></td>
                    <td>{{ $b->createdBy?->name ?? 'System' }}</td>
                    <td>
                        <a href="{{ route('backup.download', $b) }}" class="btn btn-sm" style="background:#27AE60;color:#fff;" title="Download"><i class="fa-solid fa-download"></i></a>
                        <button class="btn btn-sm btn-restore" data-id="{{ $b->id }}" data-date="{{ $b->created_at->format('d M Y') }}" style="background:#F39C12;color:#fff;" title="Restore"><i class="fa-solid fa-rotate-left"></i></button>
                        <button class="btn btn-sm btn-delete-backup" data-id="{{ $b->id }}" style="background:#E74C3C;color:#fff;" title="Delete"><i class="fa-solid fa-trash-alt"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-muted py-4">No backups yet. Click "Create Backup Now" to make the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-custom mt-3">
    <div class="card-body-c">
        <p class="small text-muted mb-1"><i class="fa-solid fa-circle-info"></i> Automatic backups run daily at 2:00 AM, last 30 retained.</p>
        <p class="small text-muted mb-0"><i class="fa-solid fa-circle-info"></i> Snapshots are created automatically before any bulk-delete action, and expire after 7 days.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnCreateBackup').on('click', function () {
    $('#btnCreateBackupText').html('<span class="spinner-border spinner-border-sm"></span> Creating Backup...');
    $.post('{{ route("backup.create") }}', { _token: $('meta[name="csrf-token"]').attr('content') })
        .done(res => { toastr.success(res.message); location.reload(); })
        .fail(xhr => { toastr.error(xhr.responseJSON?.message || 'Failed.'); $('#btnCreateBackupText').text('Create Backup Now'); });
});

$(document).on('click', '.btn-restore', function () {
    const id = $(this).data('id'), date = $(this).data('date');
    Swal.fire({
        title: 'Restore this backup?',
        html: `Restoring will overwrite current data. All changes since <b>${date}</b> will be lost. This cannot be undone.<br><br>
               Type <b>CONFIRM</b> to proceed:`,
        input: 'text', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Restore',
        preConfirm: (value) => {
            if (value !== 'CONFIRM') { Swal.showValidationMessage('You must type CONFIRM exactly.'); return false; }
            return value;
        },
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(`/backup/${id}/restore`, { _token: $('meta[name="csrf-token"]').attr('content'), confirm: r.value })
            .done(res => { toastr.success(res.message); setTimeout(() => location.reload(), 1500); });
    });
});

$(document).on('click', '.btn-delete-backup', function () {
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
