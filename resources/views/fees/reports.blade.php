@extends('layouts.app')

@section('title', 'Fee Reports')

@section('breadcrumb')
    <a href="{{ route('fees.structure') }}" class="bc-item">Fees</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Reports</span>
@endsection

@push('styles')
<style>
    table.rpt-t th { background:#1E3A5F; color:#fff; padding:10px 12px; font-size:12px; font-weight:600; text-align:center; }
    table.rpt-t td { padding:10px 12px; text-align:center; font-size:13px; border-bottom:1px solid #f5f5f5; }
    table.rpt-t tr:hover td { background:#f8faff; }
    .collected-val { color:#27AE60; font-weight:700; }
    .pending-val { color:#E74C3C; font-weight:700; }
    .bar-spark { height:6px; background:#e9ecef; border-radius:20px; overflow:hidden; margin-top:6px; }
    .bar-spark-fill { height:100%; background:#27AE60; border-radius:20px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-chart-pie"></i></span>
        Fee Reports
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-sm" style="background:#E74C3C;color:#fff;border-radius:8px;" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print
        </button>
        <a href="{{ route('fees.structure') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-cog me-1"></i> Fee Setup
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
            <div class="stat-value">{{ number_format($collected) }}</div>
            <div class="stat-label">Collected (Rs.)</div>
            <i class="fa-solid fa-coins stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
            <div class="stat-value">{{ number_format($pending) }}</div>
            <div class="stat-label">Pending (Rs.)</div>
            <i class="fa-solid fa-hourglass-half stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="stat-value">{{ number_format($overdue) }}</div>
            <div class="stat-label">Overdue (Rs.)</div>
            <i class="fa-solid fa-triangle-exclamation stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="fa-solid fa-percent"></i></div>
            <div class="stat-value">{{ number_format($discounts) }}</div>
            <div class="stat-label">Discounts (Rs.)</div>
            <i class="fa-solid fa-percent stat-bg-icon"></i>
        </div>
    </div>
</div>

{{-- Section-wise table --}}
<div class="card-custom">
    <div class="card-header-c">
        <h6 class="card-title-c"><i class="fa-solid fa-table text-primary me-2"></i>Section-wise Collection</h6>
    </div>
    <div class="card-body-c">
        <div class="table-responsive">
            <table class="rpt-t w-100">
                <thead>
                    <tr>
                        <th class="text-start">Section</th>
                        <th>Collected (Rs.)</th>
                        <th>Pending (Rs.)</th>
                        <th>Recovery %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandCollected = $sectionSummary->sum('collected');
                        $grandPending   = $sectionSummary->sum('pending');
                    @endphp
                    @forelse($sectionSummary as $row)
                    @php
                        $total = $row->collected + $row->pending;
                        $pct   = $total > 0 ? round(($row->collected / $total) * 100) : 0;
                    @endphp
                    <tr>
                        <td class="text-start"><b>{{ $row->code }}</b></td>
                        <td class="collected-val">{{ number_format($row->collected) }}</td>
                        <td class="{{ $row->pending > 0 ? 'pending-val' : '' }}">{{ number_format($row->pending) }}</td>
                        <td>
                            <div>{{ $pct }}%</div>
                            <div class="bar-spark"><div class="bar-spark-fill" style="width:{{ $pct }}%;background:{{ $pct >= 80 ? '#27AE60' : ($pct >= 50 ? '#F39C12' : '#E74C3C') }};"></div></div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state-block">
                                <i class="fa-solid fa-table"></i>
                                <p>No fee data available yet. Record payments to see section-wise breakdown.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    @if($sectionSummary->count() > 1)
                    <tr style="background:#f8faff;">
                        <td class="text-start"><b>TOTAL</b></td>
                        <td class="collected-val"><b>{{ number_format($grandCollected) }}</b></td>
                        <td class="{{ $grandPending > 0 ? 'pending-val' : '' }}"><b>{{ number_format($grandPending) }}</b></td>
                        <td>
                            @php $grandPct = ($grandCollected + $grandPending) > 0 ? round($grandCollected / ($grandCollected + $grandPending) * 100) : 0 @endphp
                            <b>{{ $grandPct }}%</b>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
