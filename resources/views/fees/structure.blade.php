@extends('layouts.app')

@section('title', 'Fee Management')

@section('breadcrumb')
    <span class="bc-current">Fee Management</span>
@endsection

@push('styles')
<style>
    .cat-card { background:#fff; border:1px solid #f0f0f0; border-radius:16px; margin-bottom:16px; overflow:hidden; }
    .cat-card-header {
        display:flex; align-items:center; justify-content:space-between;
        padding:14px 18px; background:linear-gradient(135deg,rgba(30,58,95,.04),rgba(30,58,95,.02));
        border-bottom:1px solid #f5f5f5;
    }
    .cat-name { font-size:14px; font-weight:700; color:#1E3A5F; display:flex; align-items:center; gap:8px; }
    .recurring-tag { font-size:10px; font-weight:700; padding:2px 8px; border-radius:8px;
        background:rgba(52,152,219,.1); color:#3498DB; text-transform:uppercase; }

    table.struct-t th { background:#1E3A5F; color:#fff; padding:9px 12px; font-size:12px; font-weight:600; text-align:center; }
    table.struct-t td { padding:9px 12px; text-align:center; font-size:13px; border-bottom:1px solid #f5f5f5; }
    table.struct-t tr:last-child td { border-bottom:none; }
    table.struct-t tr:hover td { background:#f8faff; }
    .amount-cell { font-weight:700; color:#27AE60; }
    .empty-cat { padding:20px; text-align:center; color:#adb5bd; font-size:13px; }
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
        @can('manage fees')
        <button class="btn btn-sm" id="btnNewStructure" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-plus me-1"></i> Add Fee Rate
        </button>
        @endcan
    </div>
</div>

@forelse($categories as $cat)
<div class="cat-card">
    <div class="cat-card-header">
        <div class="cat-name">
            <i class="fa-solid fa-tag" style="color:#1E3A5F;"></i>
            {{ $cat->name }}
            @if($cat->is_recurring)<span class="recurring-tag">Recurring</span>@endif
        </div>
        <span class="small text-muted">{{ $cat->structures->count() }} rate(s) configured</span>
    </div>
    @if($cat->structures->isEmpty())
        <div class="empty-cat"><i class="fa-solid fa-circle-info me-1"></i> No rates configured for this category yet.</div>
    @else
    <table class="struct-t w-100">
        <thead><tr><th>Program</th><th>Campus</th><th>Year</th><th>Amount (Rs.)</th><th>Installments</th>@can('manage fees')<th>Action</th>@endcan</tr></thead>
        <tbody>
            @foreach($cat->structures as $s)
            <tr>
                <td>{{ $s->program?->code ?? 'All Programs' }}</td>
                <td>{{ ucfirst($s->campus) }}</td>
                <td>{{ ucfirst($s->year) }}</td>
                <td class="amount-cell">{{ number_format($s->amount, 0) }}</td>
                <td>{{ $s->installment_plan === 'full' ? 'Full Payment' : $s->installment_plan.' Installments' }}</td>
                @can('manage fees')
                <td>
                    <button class="btn btn-sm btn-delete-structure" data-id="{{ $s->id }}"
                            style="background:#E74C3C;color:#fff;border-radius:6px;">
                        <i class="fa-solid fa-trash-alt"></i>
                    </button>
                </td>
                @endcan
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@empty
<div class="empty-state-block">
    <i class="fa-solid fa-money-bill-wave"></i>
    <p>No fee categories found. Run <code>php artisan db:seed --class=FeeCategorySeeder</code> to create default categories.</p>
</div>
@endforelse

{{-- Add Fee Rate Modal --}}
@can('manage fees')
<div class="modal fade" id="structureModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white"><i class="fa-solid fa-plus me-2"></i>Add Fee Rate</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Fee Category</label>
                        <select id="mCategory" class="form-select form-select-sm">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Program (optional)</label>
                        <select id="mProgram" class="form-select form-select-sm">
                            <option value="">All Programs</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Campus</label>
                        <select id="mCampus" class="form-select form-select-sm">
                            <option value="both">Both Campuses</option>
                            <option value="boys">Boys Only</option>
                            <option value="girls">Girls Only</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Year</label>
                        <select id="mYear" class="form-select form-select-sm">
                            <option value="both">Both Years</option>
                            <option value="first">First Year Only</option>
                            <option value="second">Second Year Only</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Amount (Rs.)</label>
                        <input type="number" id="mAmount" class="form-control form-control-sm" min="0" placeholder="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Installment Plan</label>
                        <select id="mInstallment" class="form-select form-select-sm">
                            <option value="full">Full Payment (single)</option>
                            <option value="2">2 Installments</option>
                            <option value="3">3 Installments</option>
                            <option value="4">4 Installments</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSaveStructure" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-save me-1"></i> Save Rate
                </button>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
@can('manage fees')
const structModal = new bootstrap.Modal(document.getElementById('structureModal'));
$('#btnNewStructure').on('click', () => structModal.show());

$('#btnSaveStructure').on('click', function () {
    const amount = parseFloat($('#mAmount').val());
    if (!amount || amount <= 0) { toastr.warning('Please enter a valid amount.'); return; }

    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.post('{{ route("fees.structure.store") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        fee_category_id:  $('#mCategory').val(),
        program_id:       $('#mProgram').val(),
        campus:           $('#mCampus').val(),
        year:             $('#mYear').val(),
        amount:           amount,
        installment_plan: $('#mInstallment').val(),
    }).done(res => {
        toastr.success(res.message);
        structModal.hide();
        setTimeout(() => location.reload(), 600);
    }).fail(xhr => {
        toastr.error(xhr.responseJSON?.message || 'Failed to save.');
        $('#btnSaveStructure').prop('disabled', false).html('<i class="fa-solid fa-save me-1"></i> Save Rate');
    });
});

$(document).on('click', '.btn-delete-structure', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Remove this fee rate?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Remove',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/fees/structure/${id}`, method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
@endcan
</script>
@endpush
