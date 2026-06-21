@extends('layouts.app')

@section('title', 'Fee Management')

@section('breadcrumb')
    <span class="bc-current">Fee Management</span>
@endsection

@push('styles')
<style>
    .cat-card { background: #fff; border: 1px solid #f0f0f0; border-radius: 14px; padding: 16px; margin-bottom: 14px; }
    .cat-title { font-weight: 700; color: #1E3A5F; font-size: 14px; }
    table.struct-table th { background: #1E3A5F; color: #fff; padding: 8px; font-size: 12px; text-align: center; }
    table.struct-table td { padding: 8px; text-align: center; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-money-bill-wave"></i></span>
        Fee Management
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('fees.reports') }}" class="btn btn-sm" style="background:#3498DB;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-chart-pie me-1"></i> Reports
        </a>
        <button class="btn btn-sm" id="btnNewStructure" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-plus me-1"></i> Add Fee Rate
        </button>
    </div>
</div>

@foreach($categories as $cat)
<div class="cat-card">
    <div class="cat-title">{{ $cat->name }} @if($cat->is_recurring)<span class="badge bg-info" style="font-size:10px;">Recurring</span>@endif</div>
    <table class="struct-table w-100 mt-2">
        <thead><tr><th>Program</th><th>Campus</th><th>Year</th><th>Amount (PKR)</th><th>Installments</th><th>Action</th></tr></thead>
        <tbody>
            @forelse($cat->structures as $s)
            <tr>
                <td>{{ $s->program?->code ?? 'All Programs' }}</td>
                <td>{{ ucfirst($s->campus) }}</td>
                <td>{{ ucfirst($s->year) }}</td>
                <td>Rs. {{ number_format($s->amount, 0) }}</td>
                <td>{{ $s->installment_plan === 'full' ? 'Full Payment' : $s->installment_plan.' Installments' }}</td>
                <td><button class="btn btn-sm btn-delete-structure" data-id="{{ $s->id }}" style="background:#E74C3C;color:#fff;"><i class="fa-solid fa-trash-alt"></i></button></td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted">No rate configured yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endforeach

<div class="modal fade" id="structureModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:14px 14px 0 0;">
                <h6 class="modal-title text-white">Add Fee Rate</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-600">Fee Category</label>
                        <select id="mCategory" class="form-select form-select-sm">
                            @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Program (optional)</label>
                        <select id="mProgram" class="form-select form-select-sm">
                            <option value="">All Programs</option>
                            @foreach($programs as $p)<option value="{{ $p->id }}">{{ $p->code }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Campus</label>
                        <select id="mCampus" class="form-select form-select-sm">
                            <option value="both">Both</option><option value="boys">Boys</option><option value="girls">Girls</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Year</label>
                        <select id="mYear" class="form-select form-select-sm">
                            <option value="both">Both</option><option value="first">First</option><option value="second">Second</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Amount (PKR)</label>
                        <input type="number" id="mAmount" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-600">Installment Plan</label>
                        <select id="mInstallment" class="form-select form-select-sm">
                            <option value="full">Full Payment</option><option value="2">2 Installments</option>
                            <option value="3">3 Installments</option><option value="4">4 Installments</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSaveStructure" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const structureModal = new bootstrap.Modal(document.getElementById('structureModal'));
$('#btnNewStructure').on('click', () => structureModal.show());

$('#btnSaveStructure').on('click', function () {
    $.post('{{ route("fees.structure.store") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        fee_category_id: $('#mCategory').val(), program_id: $('#mProgram').val(),
        campus: $('#mCampus').val(), year: $('#mYear').val(),
        amount: $('#mAmount').val(), installment_plan: $('#mInstallment').val(),
    }).done(res => { toastr.success(res.message); location.reload(); })
      .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to save.'));
});

$(document).on('click', '.btn-delete-structure', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Remove this fee rate?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Remove' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/fees/structure/${id}`, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
