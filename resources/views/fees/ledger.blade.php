@extends('layouts.app')

@section('title', 'Fee Ledger — '.$student->name)

@section('breadcrumb')
    <a href="{{ route('students.index') }}" class="bc-item">Students</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <a href="{{ route('students.show', $student) }}" class="bc-item">{{ $student->name }}</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Fee Ledger</span>
@endsection

@push('styles')
<style>
    /* Student identity strip */
    .ledger-student-strip {
        display:flex; align-items:center; gap:16px; padding:18px 20px;
        background:#fff; border-radius:14px; border:1px solid #f0f0f0; margin-bottom:20px;
    }
    .ledger-student-strip img { width:52px; height:52px; border-radius:50%; object-fit:cover; }
    .ledger-student-strip .info h6 { font-size:15px; font-weight:700; color:#1E3A5F; margin:0 0 2px; }
    .ledger-student-strip .info p { font-size:12.5px; color:#6C757D; margin:0; }

    /* KPI summary cards */
    .fee-kpi { background:#fff; border-radius:14px; padding:16px 18px; border:1px solid #f0f0f0; text-align:center; }
    .fee-kpi .fk-val { font-size:20px; font-weight:800; color:#2C3E50; line-height:1; }
    .fee-kpi .fk-lbl { font-size:11.5px; color:#6C757D; margin-top:4px; }

    /* Ledger table */
    table.lt th { background:#1E3A5F; color:#fff; padding:10px 12px; font-size:12px; font-weight:600; text-align:center; }
    table.lt td { padding:10px 12px; text-align:center; font-size:13px; vertical-align:middle; border-bottom:1px solid #f5f5f5; }
    table.lt td.td-left { text-align:left; }
    table.lt tr:hover td { background:#f8faff; }
    .due-red { color:#E74C3C; font-weight:700; }
    .paid-green { color:#27AE60; font-weight:700; }

    /* Payment mode badge */
    .mode-badge { font-size:10px; font-weight:700; padding:3px 8px; border-radius:8px; text-transform:uppercase; }
    .mode-cash      { background:rgba(39,174,96,.1);  color:#27AE60; }
    .mode-bank      { background:rgba(52,152,219,.1); color:#3498DB; }
    .mode-jazzcash  { background:rgba(255,100,0,.1);  color:#FF6400; }
    .mode-easypaisa { background:rgba(0,176,80,.1);   color:#00B050; }
</style>
@endpush

@section('content')

{{-- Student identity strip --}}
<div class="ledger-student-strip">
    <img src="{{ $student->photo_url }}">
    <div class="info">
        <h6>{{ $student->name }} <span style="font-size:12px;color:#6C757D;font-weight:400;">— {{ $student->roll_number }}</span></h6>
        <p>{{ ucfirst($student->campus) }} Campus &nbsp;|&nbsp; {{ ucfirst($student->year) }} Year &nbsp;|&nbsp; {{ $student->section->code ?? '—' }} &nbsp;|&nbsp; {{ $student->program->code ?? '—' }}</p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('students.show', $student) }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        @can('manage fees')
        <button class="btn btn-sm" id="btnAddPayment" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-plus me-1"></i> Add Payment
        </button>
        @endcan
    </div>
</div>

{{-- KPI Summary --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="fee-kpi">
            <div class="fk-val">Rs. {{ number_format($totalDue) }}</div>
            <div class="fk-lbl">Total Due</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="fee-kpi">
            <div class="fk-val paid-green">Rs. {{ number_format($totalPaid) }}</div>
            <div class="fk-lbl">Total Paid</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="fee-kpi">
            <div class="fk-val" style="color:#F39C12;">Rs. {{ number_format($totalWaived) }}</div>
            <div class="fk-lbl">Discounts / Waivers</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="fee-kpi">
            <div class="fk-val {{ $balance > 0 ? 'due-red' : 'paid-green' }}">
                Rs. {{ number_format(abs($balance)) }}
            </div>
            <div class="fk-lbl">{{ $balance > 0 ? 'Balance Due' : 'Fully Paid' }}</div>
        </div>
    </div>
</div>

{{-- Ledger Table --}}
<div class="card-custom">
    <div class="card-body-c">
        <div class="table-responsive">
            <table class="lt w-100">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Due (Rs.)</th>
                        <th>Paid (Rs.)</th>
                        <th>Waived (Rs.)</th>
                        <th>Balance (Rs.)</th>
                        <th>Mode</th>
                        <th>Receipt</th>
                        @can('manage fees')<th>Actions</th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $fee)
                    <tr>
                        <td>{{ $fee->payment_date->format('d M Y') }}</td>
                        <td class="td-left">{{ $fee->category->name }}</td>
                        <td>{{ number_format($fee->amount_due) }}</td>
                        <td class="paid-green">{{ number_format($fee->amount_paid) }}</td>
                        <td style="color:#F39C12;">{{ number_format($fee->waiver_amount) }}</td>
                        <td class="{{ $fee->balance > 0 ? 'due-red' : 'paid-green' }}">
                            {{ number_format($fee->balance) }}
                        </td>
                        <td>
                            <span class="mode-badge mode-{{ $fee->payment_mode }}">
                                {{ ucfirst($fee->payment_mode) }}
                            </span>
                        </td>
                        <td><code style="font-size:11px;">{{ $fee->receipt_number }}</code></td>
                        @can('manage fees')
                        <td>
                            @if($fee->balance > 0)
                            <button class="btn btn-sm btn-waiver" data-id="{{ $fee->id }}" data-due="{{ $fee->amount_due }}"
                                    title="Apply Waiver" style="background:#F39C12;color:#fff;border-radius:6px;">
                                <i class="fa-solid fa-percent"></i>
                            </button>
                            @endif
                            <button class="btn btn-sm btn-del-fee" data-id="{{ $fee->id }}"
                                    title="Delete" style="background:#E74C3C;color:#fff;border-radius:6px;">
                                <i class="fa-solid fa-trash-alt"></i>
                            </button>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state-block">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                <p>No payment records found for this student.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Payment Modal --}}
@can('manage fees')
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white"><i class="fa-solid fa-plus me-2"></i>Add Payment</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Category</label>
                        <select id="pCategory" class="form-select form-select-sm">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Date</label>
                        <input type="date" id="pDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Amount Due (Rs.)</label>
                        <input type="number" id="pDue" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Amount Paid (Rs.)</label>
                        <input type="number" id="pPaid" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Payment Mode</label>
                        <select id="pMode" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="jazzcash">JazzCash</option>
                            <option value="easypaisa">EasyPaisa</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Remarks (optional)</label>
                        <textarea id="pRemarks" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSavePayment" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-save me-1"></i> <span id="btnSavePayText">Save Payment</span>
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
const payModal = new bootstrap.Modal(document.getElementById('paymentModal'));
$('#btnAddPayment').on('click', () => payModal.show());

$('#btnSavePayment').on('click', function () {
    const due = parseFloat($('#pDue').val()), paid = parseFloat($('#pPaid').val());
    if (!due || !paid) { toastr.warning('Please enter Amount Due and Amount Paid.'); return; }
    if (paid > due)    { toastr.warning('Amount Paid cannot exceed Amount Due.'); return; }

    $(this).prop('disabled', true);
    $('#btnSavePayText').html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.post('{{ route("fees.ledger.store", $student) }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        fee_category_id: $('#pCategory').val(),
        payment_date:    $('#pDate').val(),
        amount_due:      due,
        amount_paid:     paid,
        payment_mode:    $('#pMode').val(),
        remarks:         $('#pRemarks').val(),
    }).done(res => {
        toastr.success(res.message);
        payModal.hide();
        setTimeout(() => location.reload(), 600);
    }).fail(xhr => {
        toastr.error(xhr.responseJSON?.message || 'Failed to save.');
        $('#btnSavePayment').prop('disabled', false);
        $('#btnSavePayText').text('Save Payment');
    });
});

$(document).on('click', '.btn-waiver', function () {
    const id = $(this).data('id'), due = $(this).data('due');
    Swal.fire({
        title: 'Apply Discount / Waiver',
        html: `<div class="mb-3"><label class="form-label small">Waiver Amount (Rs. max ${due})</label>
               <input id="swalAmt" class="form-control" type="number" min="1" max="${due}"></div>
               <div><label class="form-label small">Reason (required)</label>
               <input id="swalReason" class="form-control" type="text"></div>`,
        showCancelButton: true, confirmButtonColor: '#F39C12', confirmButtonText: 'Apply Waiver',
        preConfirm: () => {
            const amt = document.getElementById('swalAmt').value;
            const reason = document.getElementById('swalReason').value;
            if (!amt || !reason) { Swal.showValidationMessage('Both amount and reason are required.'); return false; }
            return { waiver_amount: amt, waiver_reason: reason };
        },
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(`/fees/waiver/${id}`, { _token: $('meta[name="csrf-token"]').attr('content'), ...r.value })
            .done(res => { toastr.success(res.message); location.reload(); })
            .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
    });
});

$(document).on('click', '.btn-del-fee', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Delete this record?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Delete',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/fees/payment/${id}`, method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
@endcan
</script>
@endpush
