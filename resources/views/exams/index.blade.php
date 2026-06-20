@extends('layouts.app')

@section('title', 'Examinations')

@section('breadcrumb')
    <span class="bc-current">Examinations</span>
@endsection

@push('styles')
<style>
    .exam-card { background: #fff; border: 1px solid #f0f0f0; border-radius: 14px;
        padding: 18px; transition: all .2s; position: relative; }
    .exam-card:hover { box-shadow: 0 8px 22px rgba(0,0,0,.08); transform: translateY(-2px); }
    .exam-type-badge { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 12px;
        background: rgba(30,58,95,.1); color: #1E3A5F; text-transform: uppercase; }
    .exam-date { font-size: 12px; color: #6C757D; margin-top: 6px; }
    .exam-stats { display: flex; gap: 14px; margin-top: 12px; font-size: 12px; color: #6C757D; }
    .due-warning { background: rgba(231,76,60,.1); color: #E74C3C; font-size: 11px;
        padding: 4px 10px; border-radius: 8px; margin-top: 10px; display: inline-block; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-file-alt"></i></span>
        Examinations
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('exams.marks-entry.index') }}" class="btn btn-sm" style="background:#3498DB;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-pen-to-square me-1"></i> Marks Entry
        </a>
        @can('create exam')
        <a href="{{ route('exams.create') }}" class="btn btn-sm" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-file-circle-plus me-1"></i> Create Exam
        </a>
        @endcan
    </div>
</div>

<div class="row g-3">
    @forelse($exams as $exam)
    <div class="col-md-4 col-lg-3">
        <div class="exam-card">
            <div class="d-flex justify-content-between align-items-start">
                <span class="exam-type-badge">{{ str_replace('_',' ',$exam->type) }}</span>
                @if($exam->is_locked)
                    <i class="fa-solid fa-lock text-danger" title="Locked"></i>
                @endif
            </div>
            <h6 class="mt-2 mb-0" style="color:#1E3A5F;font-weight:700;">{{ $exam->name }}</h6>
            <div class="exam-date"><i class="fa-regular fa-calendar me-1"></i>{{ $exam->exam_date->format('d M Y') }}</div>

            <div class="exam-stats">
                <span><i class="fa-solid fa-layer-group"></i> {{ $exam->sections_count }} Sections</span>
                <span><i class="fa-solid fa-building"></i> {{ ucfirst($exam->campus_scope) }}</span>
            </div>

            @if($exam->isPastDue())
                <span class="due-warning"><i class="fa-solid fa-triangle-exclamation"></i> Past Due</span>
            @endif

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('exams.show', $exam) }}" class="btn btn-sm flex-fill" style="background:#1E3A5F;color:#fff;border-radius:8px;font-size:12px;">
                    <i class="fa-solid fa-eye"></i> View
                </a>
                @can('create exam')
                <button class="btn btn-sm btn-delete-exam" data-id="{{ $exam->id }}" data-name="{{ $exam->name }}"
                        style="background:#E74C3C;color:#fff;border-radius:8px;font-size:12px;">
                    <i class="fa-solid fa-trash-alt"></i>
                </button>
                @endcan
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center text-muted py-5">
            <i class="fa-solid fa-inbox fa-3x mb-3 d-block opacity-25"></i>
            No exams created yet.
        </div>
    </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">{{ $exams->links() }}</div>
@endsection

@push('scripts')
<script>
$(document).on('click', '.btn-delete-exam', function () {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({
        title: 'Are you sure?', text: `This will permanently delete "${name}" and all its marks. This cannot be undone.`,
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#E74C3C',
        confirmButtonText: 'Yes, Delete', cancelButtonText: 'Cancel',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/exams/${id}`, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
