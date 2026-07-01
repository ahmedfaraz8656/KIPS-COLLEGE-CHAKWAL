@extends('layouts.app')

@section('title', 'Notice Board')

@section('breadcrumb')
    <span class="bc-current">Notice Board</span>
@endsection

@push('styles')
<style>
    .notice-card {
        background:#fff; border-radius:14px; padding:18px 20px; margin-bottom:14px;
        border:1px solid #f0f0f0; border-left:4px solid #6C757D;
        transition:box-shadow .2s, border-left-color .2s;
    }
    .notice-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }
    .notice-card.prio-important { border-left-color:#F39C12; }
    .notice-card.prio-urgent    { border-left-color:#E74C3C; background:linear-gradient(135deg,rgba(231,76,60,.02),#fff); }
    .prio-tag { font-size:10px; font-weight:800; padding:3px 10px; border-radius:8px; text-transform:uppercase; }
    .prio-urgent   { background:rgba(231,76,60,.1); color:#E74C3C; }
    .prio-important{ background:rgba(243,156,18,.1); color:#F39C12; }
    .prio-normal   { background:rgba(108,117,125,.1); color:#6C757D; }
    .target-tag { font-size:10px; font-weight:600; padding:3px 10px; border-radius:8px; background:rgba(52,152,219,.1); color:#3498DB; }
    .notice-title { font-size:14.5px; font-weight:700; color:#1E3A5F; margin:8px 0 4px; }
    .notice-body { font-size:13px; color:#2C3E50; line-height:1.65; white-space:pre-line; }
    .notice-meta { font-size:11.5px; color:#adb5bd; margin-top:10px; display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .notice-meta i { color:#c0c9d4; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-bullhorn"></i></span>
        Notice Board
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ request()->has('archived') ? route('notices.index') : route('notices.index', ['archived' => 1]) }}"
           class="btn btn-sm" style="background:{{ request()->has('archived') ? '#1E3A5F' : '#6C757D' }};color:#fff;border-radius:8px;">
            <i class="fa-solid fa-{{ request()->has('archived') ? 'inbox' : 'archive' }} me-1"></i>
            {{ request()->has('archived') ? 'Active Notices' : 'Archived' }}
        </a>
        @can('manage notices')
        <button class="btn btn-sm" id="btnNewNotice" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-plus me-1"></i> Post Notice
        </button>
        @endcan
    </div>
</div>

@forelse($notices as $notice)
<div class="notice-card prio-{{ $notice->priority }}">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div>
            <span class="prio-tag prio-{{ $notice->priority }}">{{ ucfirst($notice->priority) }}</span>
            <span class="target-tag ms-1">{{ ucfirst($notice->target) }}</span>
            @if($notice->expiry_date)
                <span class="ms-1" style="font-size:10px;color:#adb5bd;">Expires {{ $notice->expiry_date->format('d M Y') }}</span>
            @endif
        </div>
        @can('manage notices')
        <div class="d-flex gap-2 flex-shrink-0">
            <button class="btn btn-sm btn-archive-notice" data-id="{{ $notice->id }}"
                    title="Archive" style="background:#6C757D;color:#fff;border-radius:6px;">
                <i class="fa-solid fa-box-archive"></i>
            </button>
            <button class="btn btn-sm btn-delete-notice" data-id="{{ $notice->id }}"
                    title="Delete" style="background:#E74C3C;color:#fff;border-radius:6px;">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
        @endcan
    </div>

    <div class="notice-title">{{ $notice->title }}</div>
    <div class="notice-body">{{ $notice->content }}</div>

    @if($notice->attachment)
    <div class="mt-2">
        <a href="{{ Storage::url($notice->attachment) }}" target="_blank"
           class="small" style="color:#1E3A5F;font-weight:600;">
            <i class="fa-solid fa-paperclip me-1"></i> View Attachment
        </a>
    </div>
    @endif

    <div class="notice-meta">
        <span><i class="fa-regular fa-user"></i> {{ $notice->createdBy?->name ?? 'System' }}</span>
        <span><i class="fa-regular fa-calendar"></i> {{ ($notice->post_date ?? $notice->created_at)->format('d M Y, h:i A') }}</span>
        <span><i class="fa-solid fa-globe"></i> {{ ucfirst($notice->campus_scope) }} Campus</span>
    </div>
</div>
@empty
<div class="empty-state-block" style="margin-top:40px;">
    <i class="fa-solid fa-bullhorn"></i>
    <p>{{ request()->has('archived') ? 'No archived notices found.' : 'No notices posted yet.' }}</p>
</div>
@endforelse

<div class="d-flex justify-content-center mt-3">{{ $notices->links() }}</div>

{{-- Post Notice Modal --}}
@can('manage notices')
<div class="modal fade" id="noticeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white"><i class="fa-solid fa-bullhorn me-2"></i>Post Notice</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noticeForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Content <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control form-control-sm" rows="4" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Target Audience</label>
                            <select name="target" class="form-select form-select-sm">
                                <option value="all">All Users</option>
                                <option value="teachers">Teachers Only</option>
                                <option value="students">Students Only</option>
                                <option value="parents">Parents Only</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Campus</label>
                            <select name="campus_scope" class="form-select form-select-sm">
                                <option value="both">Both Campuses</option>
                                <option value="boys">Boys Campus Only</option>
                                <option value="girls">Girls Campus Only</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Priority</label>
                            <select name="priority" class="form-select form-select-sm">
                                <option value="normal">Normal</option>
                                <option value="important">Important</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Expiry Date (optional)</label>
                            <input type="date" name="expiry_date" class="form-control form-control-sm" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Attachment (optional)</label>
                            <input type="file" name="attachment" class="form-control form-control-sm">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnPostNotice" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-paper-plane me-1"></i> <span id="btnPostText">Post Notice</span>
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
const noticeModal = new bootstrap.Modal(document.getElementById('noticeModal'));
$('#btnNewNotice').on('click', () => noticeModal.show());

$('#btnPostNotice').on('click', function () {
    const formData = new FormData(document.getElementById('noticeForm'));
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $(this).prop('disabled', true);
    $('#btnPostText').html('<span class="spinner-border spinner-border-sm"></span> Posting...');

    $.ajax({ url: '{{ route("notices.store") }}', method: 'POST', data: formData, processData: false, contentType: false })
        .done(res => { toastr.success(res.message); noticeModal.hide(); setTimeout(() => location.reload(), 600); })
        .fail(xhr => { toastr.error(xhr.responseJSON?.message || 'Failed to post.'); })
        .always(() => { $('#btnPostNotice').prop('disabled', false); $('#btnPostText').text('Post Notice'); });
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
@endcan
</script>
@endpush
