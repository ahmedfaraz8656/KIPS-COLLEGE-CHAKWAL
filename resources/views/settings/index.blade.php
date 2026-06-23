@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
    <span class="bc-current">Settings</span>
@endsection

@push('styles')
<style>
    .settings-tab-nav { display: flex; gap: 6px; border-bottom: 2px solid #f0f0f0; margin-bottom: 20px; flex-wrap: wrap; }
    .settings-tab-btn { padding: 10px 16px; font-size: 13px; font-weight: 600; color: #6C757D;
        border: none; background: none; border-bottom: 3px solid transparent; cursor: pointer; }
    .settings-tab-btn.active { color: #1E3A5F; border-bottom-color: #1E3A5F; }
    .form-label { font-size: 13px; font-weight: 600; color: #2C3E50; margin-bottom: 6px; }
    .form-control, .form-select { border: 2px solid #e9ecef; border-radius: 8px; font-size: 13px; padding: 9px 12px; }
    .online-dot { width: 8px; height: 8px; border-radius: 50%; background: #27AE60; display: inline-block; margin-right: 4px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-cog"></i></span>
        Settings
    </h1>
</div>

<div class="settings-tab-nav">
    <button class="settings-tab-btn active" data-tab="general">General</button>
    <button class="settings-tab-btn" data-tab="attendance">Attendance</button>
    <button class="settings-tab-btn" data-tab="security">Security</button>
    <button class="settings-tab-btn" data-tab="notif">SMS / WhatsApp</button>
    <button class="settings-tab-btn" data-tab="theme">Theme</button>
    <button class="settings-tab-btn" data-tab="online">Online Users</button>
    <a href="{{ route('settings.users.page') }}" class="settings-tab-btn">Users <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i></a>
    <a href="{{ route('backup.index') }}" class="settings-tab-btn">Backup <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i></a>
</div>

{{-- GENERAL --}}
<div class="settings-pane card-custom" id="pane-general">
    <div class="card-body-c">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">College Name</label>
                <input type="text" id="gName" class="form-control" value="{{ $settings['college_name'] }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">College Logo</label>
                <input type="file" id="gLogo" class="form-control" accept="image/*">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" id="gPhone" class="form-control" value="{{ $settings['college_phone'] }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" id="gEmail" class="form-control" value="{{ $settings['college_email'] }}">
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <textarea id="gAddress" class="form-control" rows="2">{{ $settings['college_address'] }}</textarea>
            </div>
        </div>
        <button class="btn btn-sm mt-3" id="btnSaveGeneral" style="background:#27AE60;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-save me-1"></i> Save General Settings
        </button>
    </div>
</div>

{{-- ATTENDANCE --}}
<div class="settings-pane card-custom d-none" id="pane-attendance">
    <div class="card-body-c">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Late Arrival Cutoff Time</label>
                <input type="time" id="aLateTime" class="form-control" value="{{ $settings['attendance_late_time'] }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Minimum Attendance %</label>
                <input type="number" id="aMinPercent" class="form-control" value="{{ $settings['attendance_min_percent'] }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Warning Attendance %</label>
                <input type="number" id="aWarnPercent" class="form-control" value="{{ $settings['attendance_warning_percent'] }}">
            </div>
        </div>
        <p class="text-muted small mt-2"><i class="fa-solid fa-circle-info"></i> Changes apply immediately to new attendance markings — existing records are not affected.</p>
        <button class="btn btn-sm mt-2" id="btnSaveAttendance" style="background:#27AE60;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-save me-1"></i> Save Attendance Settings
        </button>
    </div>
</div>

{{-- SECURITY --}}
<div class="settings-pane card-custom d-none" id="pane-security">
    <div class="card-body-c">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Session Timeout (minutes)</label>
                <input type="number" id="sTimeout" class="form-control" value="{{ $settings['session_timeout_minutes'] }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Max Login Attempts</label>
                <input type="number" id="sMaxAttempts" class="form-control" value="{{ $settings['login_max_attempts'] }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Lockout Duration (minutes)</label>
                <input type="number" id="sLockout" class="form-control" value="{{ $settings['login_lockout_minutes'] }}">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="sTwoFactor" {{ $settings['two_factor_enabled'] == '1' ? 'checked' : '' }}>
                    <label class="form-check-label small">Enable Two-Factor Authentication (global)</label>
                </div>
            </div>
        </div>
        <button class="btn btn-sm mt-3" id="btnSaveSecurity" style="background:#27AE60;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-save me-1"></i> Save Security Settings
        </button>
    </div>
</div>

{{-- NOTIFICATIONS --}}
<div class="settings-pane card-custom d-none" id="pane-notif">
    <div class="card-body-c">
        <label class="form-label">WhatsApp Business API Token</label>
        <input type="password" id="nWhatsapp" class="form-control" value="{{ $settings['whatsapp_api_token'] }}" placeholder="Enter API token">
        <button class="btn btn-sm mt-3" id="btnSaveNotif" style="background:#27AE60;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-save me-1"></i> Save
        </button>
    </div>
</div>

{{-- THEME --}}
<div class="settings-pane card-custom d-none" id="pane-theme">
    <div class="card-body-c">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Primary Color</label>
                <input type="color" id="tColor" class="form-control" value="{{ $settings['theme_primary_color'] }}" style="height:42px;">
            </div>
            <div class="col-md-4">
                <label class="form-label">Theme Mode</label>
                <select id="tMode" class="form-select">
                    <option value="light" {{ $settings['theme_mode']=='light'?'selected':'' }}>Light</option>
                    <option value="dark" {{ $settings['theme_mode']=='dark'?'selected':'' }}>Dark</option>
                    <option value="high-contrast" {{ $settings['theme_mode']=='high-contrast'?'selected':'' }}>High Contrast</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Font Size</label>
                <select id="tFontSize" class="form-select">
                    <option value="normal" {{ $settings['font_size']=='normal'?'selected':'' }}>Normal</option>
                    <option value="large" {{ $settings['font_size']=='large'?'selected':'' }}>Large</option>
                    <option value="extra-large" {{ $settings['font_size']=='extra-large'?'selected':'' }}>Extra Large</option>
                </select>
            </div>
        </div>
        <button class="btn btn-sm mt-3" id="btnSaveTheme" style="background:#27AE60;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-save me-1"></i> Save Theme
        </button>
    </div>
</div>

{{-- ONLINE USERS --}}
<div class="settings-pane card-custom d-none" id="pane-online">
    <div class="card-body-c">
        <h6 style="color:#1E3A5F;">Currently Online (active in last 15 minutes)</h6>
        <table class="simple-table w-100 mt-2">
            <thead><tr><th>User</th><th>Role</th><th>Last Active</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($onlineUsers as $u)
                <tr>
                    <td><span class="online-dot"></span>{{ $u->name }}</td>
                    <td>{{ $u->primaryRole() }}</td>
                    <td>{{ $u->last_login_at->diffForHumans() }}</td>
                    <td><button class="btn btn-sm btn-force-logout" data-id="{{ $u->id }}" data-name="{{ $u->name }}" style="background:#E74C3C;color:#fff;">Force Logout</button></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted py-3">No users currently online</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('.settings-tab-btn[data-tab]').on('click', function () {
    $('.settings-tab-btn[data-tab]').removeClass('active'); $(this).addClass('active');
    $('.settings-pane').addClass('d-none');
    $('#pane-' + $(this).data('tab')).removeClass('d-none');
});

$('#btnSaveGeneral').on('click', function () {
    const fd = new FormData();
    fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
    fd.append('college_name', $('#gName').val()); fd.append('college_address', $('#gAddress').val());
    fd.append('college_phone', $('#gPhone').val()); fd.append('college_email', $('#gEmail').val());
    if ($('#gLogo')[0].files[0]) fd.append('logo', $('#gLogo')[0].files[0]);

    $.ajax({ url: '{{ route("settings.general") }}', method: 'POST', data: fd, processData: false, contentType: false })
        .done(res => toastr.success(res.message)).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
});

$('#btnSaveAttendance').on('click', function () {
    $.post('{{ route("settings.attendance") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        attendance_late_time: $('#aLateTime').val(), attendance_min_percent: $('#aMinPercent').val(),
        attendance_warning_percent: $('#aWarnPercent').val(),
    }).done(res => toastr.success(res.message)).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
});

$('#btnSaveSecurity').on('click', function () {
    $.post('{{ route("settings.security") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        session_timeout_minutes: $('#sTimeout').val(), login_max_attempts: $('#sMaxAttempts').val(),
        login_lockout_minutes: $('#sLockout').val(), two_factor_enabled: $('#sTwoFactor').is(':checked') ? 1 : 0,
    }).done(res => toastr.success(res.message)).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
});

$('#btnSaveNotif').on('click', function () {
    $.post('{{ route("settings.notifications") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'), whatsapp_api_token: $('#nWhatsapp').val(),
    }).done(res => toastr.success(res.message));
});

$('#btnSaveTheme').on('click', function () {
    $.post('{{ route("settings.theme") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'), theme_primary_color: $('#tColor').val(),
        theme_mode: $('#tMode').val(), font_size: $('#tFontSize').val(),
    }).done(res => toastr.success(res.message));
});

$(document).on('click', '.btn-force-logout', function () {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({ title: `Force logout ${name}?`, icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Logout' }).then(r => {
        if (!r.isConfirmed) return;
        $.post(`/settings/force-logout/${id}`, { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
