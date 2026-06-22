@extends('layouts.app')

@section('title', 'Notice Board')

@section('breadcrumb')
    <span class="bc-current">Notice Board</span>
@endsection

@push('styles')
<style>
    .notice-card { background: #fff; border-radius: 14px; padding: 18px; margin-bottom: 14px;
        border-left: 5px solid #6C757D; box-shadow: 0 2px 10px rgba(0,0,0,.04); }
    .notice-card.priority-important { border-left-color: #F39C12; }
    .notice-card.priority-urgent { border-left-color: #E74C3C; background: #fff8f7; }
    .priority-tag { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 10px; text-transform: uppercase; }
    .notice-meta { font-size: 11px; color: #6C757D; margin-top: 6px; }
    .notice-content { font-size: 13px; color: #2C3E50; margin-top: 8px; white-space: pre-line; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-bullhorn"></i></span>
        Notice Board
    </h1>
    @can('manage notices')
    <button class="btn btn-sm" id="btnNewNotice" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-plus me-1"></i> Post Notice
    </button>
    @endcan
</div>

@forelse($notices as $notice)
<div class="notice-card priority-{{ $notice->priority }}">
    <div class="d-flex justify-content-between align-items-start">
        <h6 class="mb-0" style="color:#1E3A5F;font-weight:700;">{{ $notice->title }}</h6>
        <span class="priority-tag" style="background:{{ ['urgent'=>'rgba(231,76,60,.12)','important'=>'rgba(243,156,18,.12)','normal'=>'rgba(108,117,125,.12)'][$notice->priority] }};
            color:{{ ['urgent'=>'#E74C3C','important'=>'#F39C12','normal'=>'#6C757D'][$notice->priority] }};">
            {{ $notice->priority }}
        </span>
    </div>
    <div class="notice-content">{{ $notice->content }}</div>
    @if($notice->attachment)
        <a href="{{ Storage::url($notice->attachment) }}" target="_blank" class="small"><i class="fa-solid fa-paperclip"></i> Attachment</a>
    @endif
    <div class="notice-meta">
        <i class="fa-solid fa-user"></i> {{ $notice->createdBy?->name }} &nbsp;|&nbsp;
        <i class="fa-regular fa-calendar"></i> {{ ($notice->post_date ?? $notice->created_at)->format('d M Y, h:i A') }} &nbsp;|&nbsp;
        Target: {{ ucfirst($notice->target) }}
    </div>
    @can('manage notices')
    <div class="mt-2">
        <button class="btn btn-sm btn-archive-notice" data-id="{{ $notice->id }}" style="background:#6C757D;color:#fff;font-size:11px;">Archive</button>
        <button class="btn btn-sm btn-delete-notice" data-id="{{ $notice->id }}" style="background:#E74C3C;color:#fff;font-size:11px;">Delete</button>
    </div>
    @endcan
</div>
@empty
<div class="text-center text-muted py-5">
    <i class="fa-solid fa-bullhorn fa-3x mb-3 d-block opacity-25"></i>
    No notices posted yet.
</div>
@endforelse

<div class="d-flex justify-content-center mt-3">{{ $notices->links() }}</div>

<div class="modal fade" id="noticeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:14px 14px 0 0;">
                <h6 class="modal-title text-white">Post Notice</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noticeForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label small fw-600">Title</label>
                            <input type="text" name="title" class="form-control form-control-sm"></div>
                        <div class="col-12"><label class="form-label small fw-600">Content</label>
                            <textarea name="content" class="form-control" rows="4"></textarea></div>
                        <div class="col-4"><label class="form-label small fw-600">Target</label>
                            <select name="target" class="form-select form-select-sm">
                                <option value="all">All Users</option><option value="teachers">Teachers</option>
                                <option value="students">Students</option><option value="parents">Parents</option>
                            </select></div>
                        <div class="col-4"><label class="form-label small fw-600">Campus</label>
                            <select name="campus_scope" class="form-select form-select-sm">
                                <option value="both">Both</option><option value="boys">Boys</option><option value="girls">Girls</option>
                            </select></div>
                        <div class="col-4"><label class="form-label small fw-600">Priority</label>
                            <select name="priority" class="form-select form-select-sm">
                                <option value="normal">Normal</option><option value="important">Important</option><option value="urgent">Urgent</option>
                            </select></div>
                        <div class="col-6"><label class="form-label small fw-600">Expiry Date (optional)</label>
                            <input type="date" name="expiry_date" class="form-control form-control-sm"></div>
                        <div class="col-6"><label class="form-label small fw-600">Attachment (optional)</label>
                            <input type="file" name="attachment" class="form-control form-control-sm"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnPostNotice" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-paper-plane me-1"></i> Post
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const noticeModal = new bootstrap.Modal(document.getElementById('noticeModal'));
$('#btnNewNotice').on('click', () => noticeModal.show());

$('#btnPostNotice').on('click', function () {
    const formData = new FormData(document.getElementById('noticeForm'));
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $.ajax({ url: '{{ route("notices.store") }}', method: 'POST', data: formData, processData: false, contentType: false })
        .done(res => { toastr.success(res.message); location.reload(); })
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to post.'));
});

$(document).on('click', '.btn-archive-notice', function () {
    $.post(`/notices/${$(this).data('id')}/archive`, { _token: $('meta[name="csrf-token"]').attr('content') })
        .done(res => { toastr.success(res.message); location.reload(); });
});

$(document).on('click', '.btn-delete-notice', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Delete this notice?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Delete' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/notices/${id}`, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
