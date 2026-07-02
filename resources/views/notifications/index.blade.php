@extends('layouts.app')

@section('title', 'Notifications')

@section('breadcrumb')
    <span class="bc-current">Notifications</span>
@endsection

@push('styles')
<style>
    table.notif-t th { background:#1E3A5F; color:#fff; padding:10px 12px; font-size:12px; font-weight:600; text-align:center; }
    table.notif-t td { padding:10px 12px; font-size:13px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
    table.notif-t tr:hover td { background:#f8faff; }
    table.notif-t td.td-l { text-align:left; }
    .ntype-badge { font-size:10px; font-weight:700; padding:3px 9px; border-radius:8px; text-transform:uppercase; white-space:nowrap; }
    .ntype-general         { background:rgba(108,117,125,.1); color:#6C757D; }
    .ntype-result_published{ background:rgba(39,174,96,.1);   color:#27AE60; }
    .ntype-low_attendance  { background:rgba(231,76,60,.1);   color:#E74C3C; }
    .ntype-fee_overdue     { background:rgba(231,76,60,.1);   color:#E74C3C; }
    .ntype-notice          { background:rgba(30,58,95,.1);    color:#1E3A5F; }
    .ntype-exam_scheduled  { background:rgba(52,152,219,.1);  color:#3498DB; }
    .channel-pill { font-size:10px; padding:2px 8px; border-radius:8px; background:rgba(52,152,219,.1); color:#3498DB; font-weight:600; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-bullhorn"></i></span>
        Notifications
    </h1>
    @can('manage notices')
    <button class="btn btn-sm" id="btnNewNotif" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-plus me-1"></i> New Notification
    </button>
    @endcan
</div>

<div class="card-custom">
    <div class="card-body-c">
        @if($notifications->isEmpty())
        <div class="empty-state-block py-5">
            <i class="fa-solid fa-bell-slash"></i>
            <p>No notifications sent yet.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="notif-t w-100">
                <thead>
                    <tr>
                        <th class="text-start">Title</th>
                        <th>Type</th>
                        <th>Target</th>
                        <th>Channel</th>
                        <th>Recipients</th>
                        <th>Sent</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $n)
                    <tr>
                        <td class="td-l">
                            <div style="font-weight:600;color:#2C3E50;">{{ $n->title }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;margin-top:2px;">{{ Str::limit($n->message, 60) }}</div>
                        </td>
                        <td><span class="ntype-badge ntype-{{ $n->type }}">{{ str_replace('_',' ', $n->type) }}</span></td>
                        <td>
                            <span style="font-size:12.5px;">{{ ucfirst($n->target_type) }}</span>
                            @if($n->target_value)
                            <span style="font-size:11px;color:#adb5bd;"> — {{ $n->target_value }}</span>
                            @endif
                        </td>
                        <td><span class="channel-pill">{{ str_replace('_',' ', ucfirst($n->channel)) }}</span></td>
                        <td>
                            <span style="font-weight:700;color:#1E3A5F;">{{ $n->recipients_count }}</span>
                        </td>
                        <td style="font-size:12px;">
                            @if($n->sent_at)
                                {{ $n->sent_at->format('d M Y') }}<br>
                                <span style="color:#adb5bd;">{{ $n->sent_at->format('h:i A') }}</span>
                            @else
                                <span class="ntype-badge ntype-notice">Scheduled</span>
                            @endif
                        </td>
                        <td style="font-size:12.5px;">{{ $n->createdBy?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>

{{-- New Notification Modal --}}
@can('manage notices')
<div class="modal fade" id="notifModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white"><i class="fa-solid fa-bullhorn me-2"></i>New Notification</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Title <span class="text-danger">*</span></label>
                        <input type="text" id="nTitle" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Message <span class="text-danger">*</span></label>
                        <textarea id="nMessage" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Type</label>
                        <select id="nType" class="form-select form-select-sm">
                            <option value="general">General</option>
                            <option value="result_published">Result Published</option>
                            <option value="low_attendance">Low Attendance</option>
                            <option value="fee_overdue">Fee Overdue</option>
                            <option value="notice">Notice</option>
                            <option value="exam_scheduled">Exam Scheduled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Target</label>
                        <select id="nTargetType" class="form-select form-select-sm">
                            <option value="all">All Users</option>
                            <option value="role">Specific Role</option>
                            <option value="campus">Specific Campus</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Target Value</label>
                        <input type="text" id="nTargetValue" class="form-control form-control-sm" placeholder="e.g. Teacher / boys">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Delivery Channel</label>
                        <select id="nChannel" class="form-select form-select-sm">
                            <option value="in_app">In-App Only</option>
                            <option value="whatsapp">WhatsApp Only</option>
                            <option value="both">In-App + WhatsApp</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Schedule (optional — leave blank to send now)</label>
                        <input type="datetime-local" id="nSchedule" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSendNotif" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-paper-plane me-1"></i> <span id="btnSendText">Send Now</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
@can('manage notices')
const notifModal = new bootstrap.Modal(document.getElementById('notifModal'));
$('#btnNewNotif').on('click', () => notifModal.show());

$('#nSchedule').on('change', function () {
    $('#btnSendText').text($(this).val() ? 'Schedule' : 'Send Now');
});

$('#btnSendNotif').on('click', function () {
    const title = $('#nTitle').val().trim(), message = $('#nMessage').val().trim();
    if (!title || !message) { toastr.warning('Title and message are required.'); return; }

    $(this).prop('disabled', true);
    $('#btnSendText').html('<span class="spinner-border spinner-border-sm"></span> Sending...');

    $.post('{{ route("notifications.store") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        title, message,
        type:         $('#nType').val(),
        target_type:  $('#nTargetType').val(),
        target_value: $('#nTargetValue').val(),
        channel:      $('#nChannel').val(),
        scheduled_at: $('#nSchedule').val() || null,
    }).done(res => {
        toastr.success(res.message);
        notifModal.hide();
        setTimeout(() => location.reload(), 600);
    }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'))
      .always(() => {
          $('#btnSendNotif').prop('disabled', false);
          $('#btnSendText').text($('#nSchedule').val() ? 'Schedule' : 'Send Now');
      });
});
@endcan
</script>
@endpush
