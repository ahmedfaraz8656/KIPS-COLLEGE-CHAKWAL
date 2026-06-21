@extends('layouts.app')

@section('title', 'Notifications')

@section('breadcrumb')
    <span class="bc-current">Notifications</span>
@endsection

@push('styles')
<style>
    .notif-type-badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 10px; text-transform: uppercase; }
    table.notif-history th { background: #1E3A5F; color: #fff; padding: 8px; font-size: 12px; text-align: center; }
    table.notif-history td { padding: 8px; text-align: center; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-bullhorn"></i></span>
        Notifications
    </h1>
    <button class="btn btn-sm" id="btnNewNotif" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-plus me-1"></i> New Notification
    </button>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <table class="notif-history w-100">
            <thead><tr><th>Title</th><th>Type</th><th>Target</th><th>Channel</th><th>Recipients</th><th>Sent</th><th>By</th></tr></thead>
            <tbody>
                @forelse($notifications as $n)
                <tr>
                    <td class="text-start">{{ $n->title }}</td>
                    <td><span class="notif-type-badge" style="background:rgba(30,58,95,.1);color:#1E3A5F;">{{ str_replace('_',' ',$n->type) }}</span></td>
                    <td>{{ ucfirst($n->target_type) }}{{ $n->target_value ? ': '.$n->target_value : '' }}</td>
                    <td>{{ str_replace('_',' ',ucfirst($n->channel)) }}</td>
                    <td>{{ $n->recipients_count }}</td>
                    <td>{{ $n->sent_at?->format('d-M-Y h:i A') ?? 'Scheduled' }}</td>
                    <td>{{ $n->createdBy?->name }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-muted py-4">No notifications sent yet</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $notifications->links() }}
    </div>
</div>

<div class="modal fade" id="notifModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:14px 14px 0 0;">
                <h6 class="modal-title text-white">New Notification</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-600">Title</label>
                        <input type="text" id="nTitle" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-600">Message</label>
                        <textarea id="nMessage" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-600">Type</label>
                        <select id="nType" class="form-select form-select-sm">
                            <option value="general">General</option>
                            <option value="result_published">Result Published</option>
                            <option value="low_attendance">Low Attendance</option>
                            <option value="fee_overdue">Fee Overdue</option>
                            <option value="notice">Notice</option>
                            <option value="exam_scheduled">Exam Scheduled</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-600">Target</label>
                        <select id="nTargetType" class="form-select form-select-sm">
                            <option value="all">All Users</option>
                            <option value="role">Specific Role</option>
                            <option value="campus">Specific Campus</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-600">Target Value</label>
                        <input type="text" id="nTargetValue" class="form-control form-control-sm" placeholder="e.g. Teacher / boys">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Delivery Channel</label>
                        <select id="nChannel" class="form-select form-select-sm">
                            <option value="in_app">In-App Only</option>
                            <option value="whatsapp">WhatsApp Only</option>
                            <option value="both">In-App + WhatsApp</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Schedule (optional)</label>
                        <input type="datetime-local" id="nSchedule" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSendNotif" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-paper-plane me-1"></i> <span id="btnSendNotifText">Send</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const notifModal = new bootstrap.Modal(document.getElementById('notifModal'));
$('#btnNewNotif').on('click', () => notifModal.show());

$('#btnSendNotif').on('click', function () {
    $('#btnSendNotifText').html('<span class="spinner-border spinner-border-sm"></span> Sending...');
    $.post('{{ route("notifications.store") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        title: $('#nTitle').val(), message: $('#nMessage').val(), type: $('#nType').val(),
        target_type: $('#nTargetType').val(), target_value: $('#nTargetValue').val(),
        channel: $('#nChannel').val(), scheduled_at: $('#nSchedule').val(),
    }).done(res => { toastr.success(res.message); location.reload(); })
      .fail(xhr => {
          toastr.error(xhr.responseJSON?.message || 'Failed to send.');
          $('#btnSendNotifText').text('Send');
      });
});
</script>
@endpush
