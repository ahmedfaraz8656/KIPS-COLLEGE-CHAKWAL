@extends('layouts.app')

@section('title', 'Holiday Management')

@section('breadcrumb')
    <span class="bc-current">Holidays</span>
@endsection

@push('styles')
<style>
    .type-badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .type-public { background:rgba(231,76,60,.12); color:#E74C3C; }
    .type-college { background:rgba(243,156,18,.12); color:#F39C12; }
    .scope-badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;
        background:rgba(52,152,219,.12); color:#3498DB; }
    table.h-table thead th { background:#1E3A5F; color:#fff; text-align:center; padding:10px; font-weight:600; }
    table.h-table tbody td { padding:10px; text-align:center; vertical-align:middle; }
    table.h-table tbody tr:nth-child(even) { background:#F8F9FA; }
    table.h-table tbody tr:hover { background:#EBF5FB; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-calendar-xmark"></i></span>
        Holiday Management
    </h1>
    <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addHolidayModal"
        style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-plus-circle me-1"></i> Add Holiday
    </button>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <div class="table-responsive">
            <table class="h-table w-100">
                <thead>
                    <tr><th>Date</th><th>Name</th><th>Type</th><th>Campus Scope</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($holidays as $h)
                    <tr data-id="{{ $h->id }}">
                        <td>{{ $h->date->format('d M Y') }}</td>
                        <td>{{ $h->name }}</td>
                        <td><span class="type-badge type-{{ $h->type }}">{{ ucfirst($h->type) }}</span></td>
                        <td><span class="scope-badge">{{ ucfirst($h->campus_scope) }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-delete-holiday" data-id="{{ $h->id }}" data-name="{{ $h->name }}"
                                style="background:#E74C3C;color:#fff;"><i class="fa-solid fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No holidays added yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $holidays->links() }}
    </div>
</div>

{{-- Add Holiday Modal --}}
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0" style="background:#1E3A5F;border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white"><i class="fa-solid fa-calendar-plus me-2"></i>Add Holiday</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="holidayForm">
                    <div class="mb-3">
                        <label class="form-label small fw-600">Holiday Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Eid-ul-Fitr" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-600">From Date</label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-600">To Date (optional)</label>
                            <input type="date" name="date_to" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-600">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="public">Public Holiday</option>
                                <option value="college">College Holiday</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-600">Campus Scope</label>
                            <select name="campus_scope" class="form-select" required>
                                <option value="both">Both Campuses</option>
                                <option value="boys">Boys Only</option>
                                <option value="girls">Girls Only</option>
                            </select>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">
                        <i class="fa-solid fa-circle-info"></i> Marked days are excluded from working-day calculations — not counted as Present or Absent.
                    </p>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button class="btn rounded-3 text-white" id="btnSaveHoliday" style="background:#1E3A5F;">
                    <i class="fa-solid fa-save me-1"></i> <span id="btnSaveHolidayText">Save Holiday</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnSaveHoliday').on('click', function () {
    const form = $('#holidayForm')[0];
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const formData = $('#holidayForm').serializeArray().reduce((o, f) => { o[f.name] = f.value; return o; }, {});
    formData._token = $('meta[name="csrf-token"]').attr('content');

    $('#btnSaveHoliday').prop('disabled', true);
    $('#btnSaveHolidayText').html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.post('{{ route("attendance.holidays.store") }}', formData)
        .done(res => { toastr.success(res.message); location.reload(); })
        .fail(xhr => {
            toastr.error(xhr.responseJSON?.message || 'Failed to add holiday.');
            $('#btnSaveHoliday').prop('disabled', false);
            $('#btnSaveHolidayText').text('Save Holiday');
        });
});

$(document).on('click', '.btn-delete-holiday', function () {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({
        title: 'Are you sure?', text: `This will permanently delete "${name}". This action cannot be undone.`,
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#E74C3C',
        confirmButtonText: 'Yes, Delete', cancelButtonText: 'Cancel',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/attendance/holidays/${id}`, method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
