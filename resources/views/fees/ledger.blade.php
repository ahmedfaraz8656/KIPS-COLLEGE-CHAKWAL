@extends('layouts.app')

@section('title', 'Fee Ledger')

@section('breadcrumb')
    <a href="{{ route('fees.structure') }}" class="bc-item">Fees</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">{{ $student->name }}</span>
@endsection

@push('styles')
<style>
    .ledger-stat { background: #fff; border: 1px solid #f0f0f0; border-radius: 12px; padding: 14px; text-align: center; }
    .ledger-stat .v { font-size: 20px; font-weight: 700; }
    .ledger-stat .l { font-size: 11px; color: #6C757D; text-transform: uppercase; }
    table.fee-table th { background: #1E3A5F; color: #fff; padding: 8px; font-size: 12px; text-align: center; }
    table.fee-table td { padding: 8px; text-align: center; font-size: 13px; }
    .balance-red { color: #E74C3C; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
        {{ $student->name }} — Fee Ledger
    </h1>
    <button class="btn btn-sm" id="btnAddPayment" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-plus me-1"></i> Add Payment
    </button>
</div>

<div class="row g-3 mb-3">
    <div class="col-3"><div class="ledger-stat"><div class="v">Rs. {{ number_format($totalDue) }}</div><div class="l">Total Due</div></div></div>
    <div class="col-3"><div class="ledger-stat"><div class="v" style="color:#27AE60;">Rs. {{ number_format($totalPaid) }}</div><div class="l">Paid</div></div></div>
    <div class="col-3"><div class="ledger-stat"><div class="v" style="color:#F39C12;">Rs. {{ number_format($totalWaived) }}</div><div class="l">Waived</div></div></div>
    <div class="col-3"><div class="ledger-stat"><div class="v {{ $balance > 0 ? 'balance-red' : '' }}">Rs. {{ number_format($balance) }}</div><div class="l">Balance</div></div></div>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <table class="fee-table w-100">
            <thead><tr><th>Date</th><th>Category</th><th>Due</th><th>Paid</th><th>Waived</th><th>Balance</th><th>Mode</th><th>Receipt</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($fees as $fee)
                <tr>
                    <td>{{ $fee->payment_date->format('d-M-Y') }}</td>
                    <td>{{ $fee->category->name }}</td>
                    <td>{{ number_format($fee->amount_due) }}</td>
                    <td>{{ number_format($fee->amount_paid) }}</td>
                    <td>{{ number_format($fee->waiver_amount) }}</td>
                    <td class="{{ $fee->balance > 0 ? 'balance-red' : '' }}">{{ number_format($fee->balance) }}</td>
                    <td>{{ ucfirst($fee->payment_mode) }}</td>
                    <td>{{ $fee->receipt_number }}</td>
                    <td>
                        @if($fee->balance > 0)
                        <button class="btn btn-sm btn-waiver" data-id="{{ $fee->id }}" data-due="{{ $fee->amount_due }}" style="background:#F39C12;color:#fff;" title="Apply Discount/Waiver"><i class="fa-solid fa-percent"></i></button>
                        @endif
                        <button class="btn btn-sm btn-delete-fee" data-id="{{ $fee->id }}" style="background:#E74C3C;color:#fff;" title="Delete"><i class="fa-solid fa-trash-alt"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-muted py-4">No payment records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:14px 14px 0 0;">
                <h6 class="modal-title text-white">Add Payment Record</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-600">Category</label>
                        <select id="pCategory" class="form-select form-select-sm">
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Payment Date</label>
                        <input type="date" id="pDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Amount Due</label>
                        <input type="number" id="pDue" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Amount Paid</label>
                        <input type="number" id="pPaid" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Payment Mode</label>
                        <select id="pMode" class="form-select form-select-sm">
                            <option value="cash">Cash</option><option value="bank">Bank</option>
                            <option value="jazzcash">JazzCash</option><option value="easypaisa">EasyPaisa</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-600">Remarks</label>
                        <textarea id="pRemarks" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSavePayment" style="background:#27AE60;color:#fff;border-radius:8px;">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
$('#btnAddPayment').on('click', () => paymentModal.show());

$('#btnSavePayment').on('click', function () {
    $.post('{{ route("fees.ledger.store", $student) }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        fee_category_id: $('#pCategory').val(), payment_date: $('#pDate').val(),
        amount_due: $('#pDue').val(), amount_paid: $('#pPaid').val(),
        payment_mode: $('#pMode').val(), remarks: $('#pRemarks').val(),
    }).done(res => { toastr.success(res.message); location.reload(); })
      .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
});

$(document).on('click', '.btn-waiver', function () {
    const id = $(this).data('id'), due = $(this).data('due');
    Swal.fire({
        title: 'Apply Discount / Waiver',
        html: `<input type="number" id="swalWaiverAmt" class="swal2-input" placeholder="Waiver amount (max ${due})">
               <input type="text" id="swalWaiverReason" class="swal2-input" placeholder="Reason (required)">`,
        confirmButtonColor: '#F39C12', confirmButtonText: 'Apply', showCancelButton: true,
        preConfirm: () => ({
            amount: document.getElementById('swalWaiverAmt').value,
            reason: document.getElementById('swalWaiverReason').value,
        }),
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(`/fees/waiver/${id}`, {
            _token: $('meta[name="csrf-token"]').attr('content'),
            waiver_amount: r.value.amount, waiver_reason: r.value.reason,
        }).done(res => { toastr.success(res.message); location.reload(); })
          .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
    });
});

$(document).on('click', '.btn-delete-fee', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Delete this record?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Delete' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/fees/payment/${id}`, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
