@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ── Welcome banner ───────────────────────────────────────── */
    .welcome-banner {
        background: linear-gradient(120deg, #1E3A5F 0%, #2C3E50 100%);
        border-radius: 18px; padding: 26px 28px; margin-bottom: 22px;
        position: relative; overflow: hidden; color: #fff;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;
    }
    .welcome-banner::before {
        content: ''; position: absolute; top: -60px; right: -60px;
        width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,0.06);
    }
    .welcome-banner::after {
        content: ''; position: absolute; bottom: -80px; right: 80px;
        width: 160px; height: 160px; border-radius: 50%; background: rgba(255,255,255,0.04);
    }
    .welcome-banner h2 { font-size: 19px; font-weight: 700; margin: 0 0 4px; position: relative; z-index: 1; }
    .welcome-banner p { font-size: 13px; color: rgba(255,255,255,0.7); margin: 0; position: relative; z-index: 1; }
    .welcome-date-pill {
        background: rgba(255,255,255,0.12); padding: 8px 16px; border-radius: 30px;
        font-size: 12.5px; font-weight: 600; position: relative; z-index: 1;
        display: flex; align-items: center; gap: 8px;
    }

    /* ── Section labels above each KPI/widget group ───────────── */
    .dash-section-title {
        font-size: 12px; font-weight: 700; color: #6C757D; text-transform: uppercase;
        letter-spacing: 0.6px; margin: 22px 0 10px;
        display: flex; align-items: center; gap: 8px;
    }
    .dash-section-title::after { content: ''; flex: 1; height: 1px; background: #ecf0f1; }
    .dash-section-title:first-of-type { margin-top: 0; }

    /* ── Refined KPI card (extends existing .stat-card system) ── */
    .kpi-row .stat-card { cursor: default; }
    .stat-card .stat-trend-up   { color: #27AE60; }
    .stat-card .stat-trend-down { color: #E74C3C; }

    /* ── Mini summary widgets (Fee / Attendance) ──────────────── */
    .mini-widget {
        background: #fff; border: 1px solid #f0f0f0; border-radius: 16px;
        padding: 18px; height: 100%;
    }
    .mini-widget .mw-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .mini-widget .mw-head h6 { font-size: 13px; font-weight: 700; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px; }
    .mini-widget .mw-icon {
        width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;
    }
    .mw-big-number { font-size: 24px; font-weight: 700; color: #2C3E50; line-height: 1; }
    .mw-sub-label { font-size: 11.5px; color: #6C757D; margin-top: 3px; }
    .mw-progress { height: 7px; background: #f0f2f5; border-radius: 20px; overflow: hidden; margin-top: 12px; }
    .mw-progress-fill { height: 100%; border-radius: 20px; transition: width .6s ease; }

    /* ── Charts ────────────────────────────────────────────────── */
    .chart-card { background: #fff; border-radius: 16px; padding: 18px; border: 1px solid #f0f0f0; box-shadow: 0 2px 12px rgba(0,0,0,0.05); height: 100%; }
    .chart-card h6 { font-size: 13px; font-weight: 700; color: #1E3A5F; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .chart-empty { text-align: center; padding: 40px 10px; color: #adb5bd; font-size: 12.5px; }

    /* ── System status strip ──────────────────────────────────── */
    .status-strip { display: flex; gap: 18px; flex-wrap: wrap; align-items: center; }
    .status-pill { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #2C3E50; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; background: #27AE60; box-shadow: 0 0 0 3px rgba(39,174,96,.15); }
</style>
@endpush

@section('content')

{{-- WELCOME BANNER --}}
<div class="welcome-banner">
    <div>
        <h2><i class="fa-solid fa-graduation-cap me-2"></i>Welcome back, {{ $user->name }}</h2>
        <p>{{ $role }} &nbsp;•&nbsp; KIPS College Chakwal Management Console</p>
    </div>
    <div class="welcome-date-pill">
        <i class="fa-regular fa-calendar"></i> {{ now()->format('l, d M Y') }}
    </div>
</div>

{{-- KPI CARDS --}}
<div class="dash-section-title"><i class="fa-solid fa-chart-simple"></i> Key Statistics</div>
<div class="row g-3 kpi-row mb-2">
    <div class="col-6 col-md-3"><div class="stat-card stat-primary"><div class="stat-icon"><i class="fa-solid fa-users"></i></div><div class="stat-value" id="statTotal">{{ $stats['total_students'] ?? 0 }}</div><div class="stat-label">Total Students</div><i class="fa-solid fa-users stat-bg-icon"></i></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-info"><div class="stat-icon"><i class="fa-solid fa-person"></i></div><div class="stat-value">{{ $stats['total_boys'] ?? 0 }}</div><div class="stat-label">Boys</div><i class="fa-solid fa-person stat-bg-icon"></i></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-danger"><div class="stat-icon"><i class="fa-solid fa-person-dress"></i></div><div class="stat-value">{{ $stats['total_girls'] ?? 0 }}</div><div class="stat-label">Girls</div><i class="fa-solid fa-person-dress stat-bg-icon"></i></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-success"><div class="stat-icon"><i class="fa-solid fa-chalkboard-teacher"></i></div><div class="stat-value">{{ $stats['total_teachers'] ?? 0 }}</div><div class="stat-label">Teachers</div><i class="fa-solid fa-chalkboard-teacher stat-bg-icon"></i></div></div>

    <div class="col-6 col-md-3"><div class="stat-card stat-warning"><div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div><div class="stat-value">{{ $stats['first_year'] ?? 0 }}</div><div class="stat-label">First Year</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-warning"><div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div><div class="stat-value">{{ $stats['second_year'] ?? 0 }}</div><div class="stat-label">Second Year</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-info"><div class="stat-icon"><i class="fa-solid fa-table-cells"></i></div><div class="stat-value">{{ $stats['active_sections'] ?? 0 }}</div><div class="stat-label">Active Sections</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card stat-success"><div class="stat-icon"><i class="fa-solid fa-clipboard-check"></i></div><div class="stat-value">{{ $stats['today_attendance'] ?? 0 }}%</div><div class="stat-label">Today's Attendance</div></div></div>
</div>

{{-- SUMMARY WIDGETS: Fee + Attendance --}}
<div class="dash-section-title"><i class="fa-solid fa-gauge-high"></i> Summary</div>
<div class="row g-3 mb-2">
    <div class="col-md-4">
        <div class="mini-widget">
            <div class="mw-head">
                <h6><i class="fa-solid fa-money-bill-wave"></i> Fee Collection (This Month)</h6>
                <div class="mw-icon" style="background:rgba(39,174,96,.1);color:#27AE60;"><i class="fa-solid fa-arrow-trend-up"></i></div>
            </div>
            <div class="mw-big-number">Rs. {{ number_format($stats['fees_this_month'] ?? 0) }}</div>
            <div class="mw-sub-label">Collected so far this month</div>
            <div class="mw-progress"><div class="mw-progress-fill" style="width:{{ min(100, ($stats['fees_this_month'] ?? 0) > 0 ? 70 : 0) }}%;background:#27AE60;"></div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-widget">
            <div class="mw-head">
                <h6><i class="fa-solid fa-file-invoice-dollar"></i> Pending Dues</h6>
                <div class="mw-icon" style="background:rgba(231,76,60,.1);color:#E74C3C;"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
            <div class="mw-big-number">Rs. {{ number_format($stats['pending_fees'] ?? 0) }}</div>
            <div class="mw-sub-label">Outstanding across all students</div>
            <a href="{{ route('fees.reports') }}" class="d-block mt-2 small" style="color:#1E3A5F;font-weight:600;">View Fee Reports <i class="fa-solid fa-arrow-right fa-xs"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-widget">
            <div class="mw-head">
                <h6><i class="fa-solid fa-file-alt"></i> Exams on Record</h6>
                <div class="mw-icon" style="background:rgba(52,152,219,.1);color:#3498DB;"><i class="fa-solid fa-list-check"></i></div>
            </div>
            <div class="mw-big-number">{{ $stats['total_exams'] ?? 0 }}</div>
            <div class="mw-sub-label">Total exams created</div>
            <a href="{{ route('exams.index') }}" class="d-block mt-2 small" style="color:#1E3A5F;font-weight:600;">View All Exams <i class="fa-solid fa-arrow-right fa-xs"></i></a>
        </div>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="dash-section-title"><i class="fa-solid fa-bolt"></i> Quick Actions</div>
<div class="card-custom mb-2">
    <div class="card-body-c">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="{{ route('students.create') }}" class="quick-action-btn d-block">
                    <div class="qa-icon mx-auto" style="background:rgba(30,58,95,0.1);color:#1E3A5F;"><i class="fa-solid fa-user-plus"></i></div>
                    New Student
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('exams.create') }}" class="quick-action-btn d-block">
                    <div class="qa-icon mx-auto" style="background:rgba(243,156,18,0.1);color:#F39C12;"><i class="fa-solid fa-file-circle-plus"></i></div>
                    Create Exam
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('attendance.mark') }}" class="quick-action-btn d-block">
                    <div class="qa-icon mx-auto" style="background:rgba(39,174,96,0.1);color:#27AE60;"><i class="fa-solid fa-clipboard-check"></i></div>
                    Mark Attendance
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('exams.results.index') }}" class="quick-action-btn d-block">
                    <div class="qa-icon mx-auto" style="background:rgba(52,152,219,0.1);color:#3498DB;"><i class="fa-solid fa-chart-bar"></i></div>
                    View Reports
                </a>
            </div>
        </div>
    </div>
</div>

{{-- CHARTS --}}
<div class="dash-section-title"><i class="fa-solid fa-chart-pie"></i> Analytics</div>
<div class="row g-3 mb-2">
    <div class="col-md-4">
        <div class="chart-card">
            <h6><i class="fa-solid fa-venus-mars text-primary"></i> Boys vs Girls</h6>
            <canvas id="genderChart" height="200"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <h6><i class="fa-solid fa-layer-group text-warning"></i> Students per Section</h6>
            <canvas id="sectionsChart" height="200"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <h6><i class="fa-solid fa-book text-success"></i> Program Distribution</h6>
            <canvas id="programsChart" height="200"></canvas>
        </div>
    </div>
</div>

{{-- ACTIVITY + ALERTS --}}
<div class="dash-section-title"><i class="fa-solid fa-list-check"></i> Activity &amp; Alerts</div>
<div class="row g-3 mb-2">
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

{{-- SYSTEM STATUS --}}
<div class="card-custom mt-1">
    <div class="card-body-c py-3">
        <div class="status-strip">
            <span class="status-pill"><span class="status-dot"></span> System Operational</span>
            <span class="status-pill"><i class="fa-solid fa-database text-muted"></i> Database Connected</span>
            <span class="status-pill"><i class="fa-solid fa-shield-halved text-muted"></i> Session Secure</span>
            <span class="status-pill ms-auto text-muted"><i class="fa-regular fa-clock"></i> Last refreshed: <span id="lastRefreshed">just now</span></span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    const chartFont = { family: "'Poppins', sans-serif", size: 11 };

    // Gender Chart
    $.get('{{ url("dashboard/api/chart/gender") }}', function (res) {
        if (!res.labels || !res.labels.length || res.data.every(d => d == 0)) {
            $('#genderChart').replaceWith('<div class="chart-empty"><i class="fa-solid fa-chart-pie fa-2x mb-2 d-block opacity-25"></i>No student data yet</div>');
            return;
        }
        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: { labels: res.labels, datasets: [{ data: res.data, backgroundColor: res.colors, borderWidth: 0 }] },
            options: { plugins: { legend: { position: 'bottom', labels: { font: chartFont, padding: 14 } } }, cutout: '68%' }
        });
    });

    // Sections Chart
    $.get('{{ url("dashboard/api/chart/sections") }}', function (res) {
        if (!res.labels || !res.labels.length) {
            $('#sectionsChart').replaceWith('<div class="chart-empty"><i class="fa-solid fa-layer-group fa-2x mb-2 d-block opacity-25"></i>No section data yet</div>');
            return;
        }
        new Chart(document.getElementById('sectionsChart'), {
            type: 'bar',
            data: { labels: res.labels, datasets: [{ label: 'Students', data: res.data, backgroundColor: '#1E3A5F', borderRadius: 4 }] },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { font: chartFont } }, y: { ticks: { font: chartFont } } } }
        });
    });

    // Programs Chart (endpoint already existed in DashboardController, was never wired to a view)
    $.get('{{ url("dashboard/api/chart/programs") }}', function (res) {
        if (!res.labels || !res.labels.length) {
            $('#programsChart').replaceWith('<div class="chart-empty"><i class="fa-solid fa-book fa-2x mb-2 d-block opacity-25"></i>No program data yet</div>');
            return;
        }
        new Chart(document.getElementById('programsChart'), {
            type: 'pie',
            data: { labels: res.labels, datasets: [{ data: res.data, backgroundColor: res.colors, borderWidth: 0 }] },
            options: { plugins: { legend: { position: 'bottom', labels: { font: chartFont, padding: 12 } } } }
        });
    });

    // Recent Activity
    $.get('{{ url("dashboard/api/activity") }}', function (res) {
        if (!res.data.length) {
            $('#activityFeed').html('<div class="empty-state-block"><i class="fa-solid fa-inbox"></i><p>No recent activity yet.</p></div>');
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
            $('#alertsBox').html('<div class="empty-state-block"><i class="fa-solid fa-circle-check" style="color:#27AE60;opacity:.6;"></i><p>No pending alerts &mdash; everything looks good.</p></div>');
            return;
        }
        let html = '';
        res.data.forEach(a => {
            const clickable = a.link ? `style="cursor:pointer" onclick="window.location.href='${a.link}'"` : '';
            html += `<div class="alert-item ${a.type}" ${clickable}><i class="fa-solid ${a.icon}"></i> ${a.message}</div>`;
        });
        $('#alertsBox').html(html);
    });

    // Footer "last refreshed" ticker
    $('#lastRefreshed').text(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
});
</script>
@endpush
