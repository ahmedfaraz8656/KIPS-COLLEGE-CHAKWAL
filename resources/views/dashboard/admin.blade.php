@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .chart-card { background: #fff; border-radius: 16px; padding: 18px; border: 1px solid #f0f0f0; box-shadow: 0 2px 12px rgba(0,0,0,0.05); }
    .chart-card h6 { font-size: 13px; font-weight: 700; color: #1E3A5F; margin-bottom: 14px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-gauge"></i></span>
        Dashboard
    </h1>
    <span class="text-muted" style="font-size:13px;">{{ now()->format('l, d M Y') }}</span>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="stat-card stat-primary"><div class="stat-icon"><i class="fa-solid fa-users"></i></div><div class="stat-value" id="statTotal">{{ $stats['total_students'] ?? 0 }}</div><div class="stat-label">Total Students</div><i class="fa-solid fa-users stat-bg-icon"></i></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-info"><div class="stat-icon"><i class="fa-solid fa-person"></i></div><div class="stat-value">{{ $stats['total_boys'] ?? 0 }}</div><div class="stat-label">Boys</div><i class="fa-solid fa-person stat-bg-icon"></i></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-danger"><div class="stat-icon"><i class="fa-solid fa-person-dress"></i></div><div class="stat-value">{{ $stats['total_girls'] ?? 0 }}</div><div class="stat-label">Girls</div><i class="fa-solid fa-person-dress stat-bg-icon"></i></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-success"><div class="stat-icon"><i class="fa-solid fa-chalkboard-teacher"></i></div><div class="stat-value">{{ $stats['total_teachers'] ?? 0 }}</div><div class="stat-label">Teachers</div><i class="fa-solid fa-chalkboard-teacher stat-bg-icon"></i></div></div>

    <div class="col-6 col-md-3"><div class="stat-card stat-warning"><div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div><div class="stat-value">{{ $stats['first_year'] ?? 0 }}</div><div class="stat-label">First Year</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-warning"><div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div><div class="stat-value">{{ $stats['second_year'] ?? 0 }}</div><div class="stat-label">Second Year</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-info"><div class="stat-icon"><i class="fa-solid fa-table-cells"></i></div><div class="stat-value">{{ $stats['active_sections'] ?? 0 }}</div><div class="stat-label">Sections</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-success"><div class="stat-icon"><i class="fa-solid fa-clipboard-check"></i></div><div class="stat-value">{{ $stats['today_attendance'] ?? 0 }}%</div><div class="stat-label">Today Attendance</div></div></div>
</div>

{{-- Quick Actions --}}
<div class="card-custom mb-4">
    <div class="card-header-c"><h6 class="card-title-c"><i class="fa-solid fa-bolt text-warning"></i> Quick Actions</h6></div>
    <div class="card-body-c">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="{{ route('students.create') }}" class="quick-action-btn d-block">
                    <div class="qa-icon mx-auto" style="background:rgba(30,58,95,0.1);color:#1E3A5F;"><i class="fa-solid fa-user-plus"></i></div>
                    New Student
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" class="quick-action-btn d-block">
                    <div class="qa-icon mx-auto" style="background:rgba(243,156,18,0.1);color:#F39C12;"><i class="fa-solid fa-file-circle-plus"></i></div>
                    Create Exam
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" class="quick-action-btn d-block">
                    <div class="qa-icon mx-auto" style="background:rgba(39,174,96,0.1);color:#27AE60;"><i class="fa-solid fa-clipboard-check"></i></div>
                    Mark Attendance
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" class="quick-action-btn d-block">
                    <div class="qa-icon mx-auto" style="background:rgba(52,152,219,0.1);color:#3498DB;"><i class="fa-solid fa-chart-bar"></i></div>
                    View Reports
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fa-solid fa-chart-pie me-2"></i>Boys vs Girls</h6>
            <canvas id="genderChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fa-solid fa-chart-bar me-2"></i>Students per Section</h6>
            <canvas id="sectionsChart" height="220"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="card-custom">
            <div class="card-header-c"><h6 class="card-title-c"><i class="fa-solid fa-clock-rotate-left text-info"></i> Recent Activity</h6></div>
            <div class="card-body-c" id="activityFeed">
                <div class="text-center text-muted py-3"><i class="fa-solid fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-custom">
            <div class="card-header-c"><h6 class="card-title-c"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Pending Alerts</h6></div>
            <div class="card-body-c" id="alertsBox">
                <div class="text-center text-muted py-3"><i class="fa-solid fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    // Gender Chart
    $.get('{{ url("dashboard/api/chart/gender") }}', function (res) {
        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: { labels: res.labels, datasets: [{ data: res.data, backgroundColor: res.colors }] },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    });

    // Sections Chart
    $.get('{{ url("dashboard/api/chart/sections") }}', function (res) {
        new Chart(document.getElementById('sectionsChart'), {
            type: 'bar',
            data: { labels: res.labels, datasets: [{ label: 'Students', data: res.data, backgroundColor: '#1E3A5F' }] },
            options: { indexAxis: 'y', plugins: { legend: { display: false } } }
        });
    });

    // Recent Activity
    $.get('{{ url("dashboard/api/activity") }}', function (res) {
        if (!res.data.length) {
            $('#activityFeed').html('<p class="text-muted text-center py-3" style="font-size:13px;">No recent activity yet.</p>');
            return;
        }
        let html = '';
        res.data.forEach(a => {
            html += `<div class="activity-item">
                <div class="activity-avatar">${a.name ? a.name.charAt(0) : 'U'}</div>
                <div><div class="activity-text"><strong>${a.name}</strong> ${a.description}</div>
                <div class="activity-time">${a.time_ago}</div></div>
            </div>`;
        });
        $('#activityFeed').html(html);
    });

    // Pending Alerts
    $.get('{{ url("dashboard/api/alerts") }}', function (res) {
        if (!res.data.length) {
            $('#alertsBox').html('<p class="text-muted text-center py-3" style="font-size:13px;"><i class="fa-solid fa-circle-check text-success me-1"></i> No pending alerts.</p>');
            return;
        }
        let html = '';
        res.data.forEach(a => html += `<div class="alert-item ${a.type}"><i class="fa-solid ${a.icon}"></i> ${a.message}</div>`);
        $('#alertsBox').html(html);
    });
});
</script>
@endpush
