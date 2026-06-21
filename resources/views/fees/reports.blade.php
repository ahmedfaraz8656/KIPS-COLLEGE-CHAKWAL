@extends('layouts.app')

@section('title', 'Fee Reports')

@section('breadcrumb')
    <a href="{{ route('fees.structure') }}" class="bc-item">Fees</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Reports</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-chart-pie"></i></span>
        Fee Reports
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-sm" style="background:#E74C3C;color:#fff;border-radius:8px;"><i class="fa-solid fa-file-pdf me-1"></i> Export PDF</button>
        <button class="btn btn-sm" style="background:#27AE60;color:#fff;border-radius:8px;"><i class="fa-solid fa-file-excel me-1"></i> Export Excel</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="stat-card stat-success"><div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
        <div class="stat-value">{{ number_format($collected) }}</div><div class="stat-label">Collected (PKR)</div></div></div>
    <div class="col-md-3"><div class="stat-card stat-warning"><div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="stat-value">{{ number_format($pending) }}</div><div class="stat-label">Pending (PKR)</div></div></div>
    <div class="col-md-3"><div class="stat-card stat-danger"><div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-value">{{ number_format($overdue) }}</div><div class="stat-label">Overdue (PKR)</div></div></div>
    <div class="col-md-3"><div class="stat-card stat-info"><div class="stat-icon"><i class="fa-solid fa-percent"></i></div>
        <div class="stat-value">{{ number_format($discounts) }}</div><div class="stat-label">Discounts (PKR)</div></div></div>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <h6 style="color:#1E3A5F;" class="mb-3">Section-wise Collection Summary</h6>
        <table class="simple-table w-100">
            <thead><tr><th>Section</th><th>Collected</th><th>Pending</th></tr></thead>
            <tbody>
                @forelse($sectionSummary as $row)
                <tr><td>{{ $row->code }}</td><td>Rs. {{ number_format($row->collected) }}</td>
                    <td class="{{ $row->pending > 0 ? 'text-danger fw-bold' : '' }}">Rs. {{ number_format($row->pending) }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-muted py-3">No fee data yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
