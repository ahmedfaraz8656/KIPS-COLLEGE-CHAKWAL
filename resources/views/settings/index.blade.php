@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
    <span class="bc-current">Settings</span>
@endsection

@push('styles')
<style>
    .stab-nav { display:flex; gap:4px; flex-wrap:wrap; margin-bottom:0;
        border-bottom:2px solid #f0f0f0; padding-bottom:0; }
    .stab-btn { padding:11px 18px; font-size:13px; font-weight:600; color:#6C757D;
        border:none; background:none; border-bottom:3px solid transparent;
        cursor:pointer; display:flex; align-items:center; gap:7px; transition:color .15s; }
    .stab-btn:hover { color:#1E3A5F; }
    .stab-btn.active { color:#1E3A5F; border-bottom-color:#1E3A5F; }
    .stab-pane { padding-top:22px; }
    .form-label { font-size:12px; font-weight:700; color:#6C757D; text-transform:uppercase; letter-spacing:.3px; }
    .form-control, .form-select { border:2px solid #e9ecef; border-radius:8px; font-size:13px; }
    .form-control:focus, .form-select:focus { border-color:#1E3A5F; box-shadow:0 0 0 3px rgba(30,58,95,.07); }
    .section-divider { font-size:11px; font-weight:700; color:#adb5bd; text-transform:uppercase; letter-spacing:.5px;
        display:flex; align-items:center; gap:8px; margin:20px 0 14px; }
    .section-divider::after { content:''; flex:1; height:1px; background:#f0f0f0; }
    .online-row { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; margin-bottom:8px; }
    .online-row:hover { background:#f8faff; }
    .online-dot { width:9px; height:9px; border-radius:50%; background:#27AE60; box-shadow:0 0 0 3px rgba(39,174,96,.2); flex-shrink:0; }
    .demo-section { background:rgba(231,76,60,.04); border:1px solid rgba(231,76,60,.15); border-radius:12px; padding:16px 20px; }
    .demo-section h6 { color:#c0392b; font-size:13px; font-weight:700; }
    .color-swatch-row { display:flex; gap:10px; flex-wrap:wrap; margin-top:8px; }
    .swatch { width:34px; height:34px; border-radius:8px; cursor:pointer; border:3px solid transparent; transition:transform .15s; }
    .swatch:hover, .swatch.active { transform:scale(1.15); border-color:#fff; box-shadow:0 0 0 2px #1E3A5F; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-cog"></i></span>
        System Settings
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('settings.users.page') }}" class="btn btn-sm" style="background:#3498DB;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-users-gear me-1"></i> User Management
        </a>
        <a href="{{ route('backup.index') }}" class="btn btn-sm" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-database me-1"></i> Backup & Restore
        </a>
    </div>
</div>

<div class="card-custom">
    <div class="card-body-c">

        {{-- Tab Navigation --}}
        <div class="stab-nav">
            <button class="stab-btn active" data-tab="general"><i class="fa-solid fa-school"></i> College Info</button>
            <button class="stab-btn" data-tab="attendance"><i class="fa-solid fa-clipboard-check"></i> Attendance</button>
            <button class="stab-btn" data-tab="security"><i class="fa-solid fa-shield-halved"></i> Security</button>
            <button class="stab-btn" data-tab="notif"><i class="fa-brands fa-whatsapp"></i> WhatsApp</button>
            <button class="stab-btn" data-tab="theme"><i class="fa-solid fa-palette"></i> Appearance</button>
            <button class="stab-btn" data-tab="online"><i class="fa-solid fa-users"></i> Online Users</button>
            <button class="stab-btn" data-tab="demo"><i class="fa-solid fa-flask"></i> Demo Data</button>
        </div>

        {{-- TAB: General --}}
        <div class="stab-pane" id="pane-general">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">College Name</label>
                    <input type="text" id="gName" class="form-control" value="{{ $settings['college_name'] }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Official Email</label>
                    <input type="email" id="gEmail" class="form-control" value="{{ $settings['college_email'] }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" id="gPhone" class="form-control" value="{{ $settings['college_phone'] }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Address</label>
                    <textarea id="gAddress" class="form-control" rows="2">{{ $settings['college_address'] }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">College Logo</label>
                    <input type="file" id="gLogo" class="form-control" accept="image/*">
                    @if($settings['college_logo'])
                    <div class="mt-2"><img src="{{ Storage::url($settings['college_logo']) }}" style="height:40px;border-radius:6px;"></div>
                    @endif
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-sm" id="btnSaveGeneral" style="background:#27AE60;color:#fff;border-radius:8px;padding:9px 22px;">
                    <i class="fa-solid fa-save me-1"></i> <span id="saveGenText">Save College Info</span>
                </button>
            </div>
        </div>

        {{-- TAB: Attendance --}}
        <div class="stab-pane d-none" id="pane-attendance">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Late Arrival Cutoff Time</label>
                    <input type="time" id="aLate" class="form-control" value="{{ $settings['attendance_late_time'] }}">
                    <small class="text-muted">Students marked after this time are flagged as Late.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Minimum Attendance %</label>
                    <input type="number" id="aMin" class="form-control" value="{{ $settings['attendance_min_percent'] }}" min="0" max="100">
                    <small class="text-muted">Below this triggers a shortage alert.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Warning Threshold %</label>
                    <input type="number" id="aWarn" class="form-control" value="{{ $settings['attendance_warning_percent'] }}" min="0" max="100">
                    <small class="text-muted">Warning shown before minimum is reached.</small>
                </div>
            </div>
            <div class="alert alert-info mt-3 p-3 rounded-3" style="font-size:13px;">
                <i class="fa-solid fa-circle-info me-1"></i>
                Changes apply immediately to new attendance markings. Existing records are not recalculated.
            </div>
            <button class="btn btn-sm mt-2" id="btnSaveAtt" style="background:#27AE60;color:#fff;border-radius:8px;padding:9px 22px;">
                <i class="fa-solid fa-save me-1"></i> <span id="saveAttText">Save Attendance Settings</span>
            </button>
        </div>

        {{-- TAB: Security --}}
        <div class="stab-pane d-none" id="pane-security">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Session Timeout (minutes)</label>
                    <input type="number" id="sTimeout" class="form-control" value="{{ $settings['session_timeout_minutes'] }}" min="5" max="240">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Login Attempts</label>
                    <input type="number" id="sMax" class="form-control" value="{{ $settings['login_max_attempts'] }}" min="3" max="10">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lockout Duration (minutes)</label>
                    <input type="number" id="sLock" class="form-control" value="{{ $settings['login_lockout_minutes'] }}" min="5" max="60">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="s2fa" {{ $settings['two_factor_enabled'] == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="s2fa" style="font-size:13px;">
                            Enable Two-Factor Authentication (all users, all logins)
                        </label>
                    </div>
                </div>
            </div>
            <button class="btn btn-sm mt-3" id="btnSaveSec" style="background:#27AE60;color:#fff;border-radius:8px;padding:9px 22px;">
                <i class="fa-solid fa-save me-1"></i> <span id="saveSecText">Save Security Settings</span>
            </button>
        </div>

        {{-- TAB: WhatsApp --}}
        <div class="stab-pane d-none" id="pane-notif">
            <p class="text-muted small mb-3">
                Enter your WhatsApp Business API token to enable WhatsApp notifications for results, fees, and alerts.
            </p>
            <div class="col-md-8">
                <label class="form-label">WhatsApp Business API Token</label>
                <div class="input-group">
                    <input type="password" id="nWA" class="form-control" value="{{ $settings['whatsapp_api_token'] }}" placeholder="Enter API token">
                    <button class="btn" type="button" id="toggleToken" style="border:2px solid #e9ecef;background:#f8f9fa;">
                        <i class="fa-solid fa-eye" id="tokenEyeIcon"></i>
                    </button>
                </div>
            </div>
            <button class="btn btn-sm mt-3" id="btnSaveNotif" style="background:#27AE60;color:#fff;border-radius:8px;padding:9px 22px;">
                <i class="fa-solid fa-save me-1"></i> Save Token
            </button>
        </div>

        {{-- TAB: Appearance --}}
        <div class="stab-pane d-none" id="pane-theme">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Primary Color</label>
                    <input type="color" id="tColor" class="form-control" value="{{ $settings['theme_primary_color'] }}" style="height:42px;cursor:pointer;">
                    <div class="color-swatch-row">
                        @foreach(['#1E3A5F','#1a252f','#154360','#117a65','#784212','#512e5f'] as $c)
                        <div class="swatch {{ $settings['theme_primary_color'] === $c ? 'active' : '' }}"
                             style="background:{{ $c }};" data-color="{{ $c }}"></div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Theme Mode</label>
                    <select id="tMode" class="form-select">
                        <option value="light" {{ $settings['theme_mode']==='light' ? 'selected' : '' }}>Light</option>
                        <option value="dark" {{ $settings['theme_mode']==='dark' ? 'selected' : '' }}>Dark</option>
                        <option value="high-contrast" {{ $settings['theme_mode']==='high-contrast' ? 'selected' : '' }}>High Contrast</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Font Size</label>
                    <select id="tFont" class="form-select">
                        <option value="normal" {{ $settings['font_size']==='normal' ? 'selected' : '' }}>Normal</option>
                        <option value="large" {{ $settings['font_size']==='large' ? 'selected' : '' }}>Large</option>
                        <option value="extra-large" {{ $settings['font_size']==='extra-large' ? 'selected' : '' }}>Extra Large</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-sm mt-3" id="btnSaveTheme" style="background:#27AE60;color:#fff;border-radius:8px;padding:9px 22px;">
                <i class="fa-solid fa-save me-1"></i> Save Appearance
            </button>
        </div>

        {{-- TAB: Online Users --}}
        <div class="stab-pane d-none" id="pane-online">
            <h6 class="section-divider"><i class="fa-solid fa-wifi"></i> Currently Active (last 15 min)</h6>
            @forelse($onlineUsers as $u)
            <div class="online-row">
                <span class="online-dot"></span>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#2C3E50;">{{ $u->name }}</div>
                    <div style="font-size:11.5px;color:#adb5bd;">{{ $u->primaryRole() }} &nbsp;|&nbsp; Last seen: {{ $u->last_login_at?->diffForHumans() ?? 'Unknown' }}</div>
                </div>
                <button class="btn btn-sm btn-force-logout ms-auto" data-id="{{ $u->id }}" data-name="{{ $u->name }}"
                        style="background:#E74C3C;color:#fff;border-radius:8px;font-size:12px;">
                    <i class="fa-solid fa-power-off me-1"></i> Force Logout
                </button>
            </div>
            @empty
            <div class="empty-state-block py-4">
                <i class="fa-solid fa-user-slash"></i>
                <p>No users currently online.</p>
            </div>
            @endforelse
        </div>

        {{-- TAB: Demo Data --}}
        <div class="stab-pane d-none" id="pane-demo">
            <div class="demo-section">
                <h6><i class="fa-solid fa-flask me-2"></i>Demo / Sample Data</h6>
                <p class="small text-muted mb-3">
                    Load sample teachers, students, exams, marks, attendance, and fee records for demonstration.
                    All demo records are flagged separately — your real data will NOT be affected.
                </p>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm" id="btnLoadDemo" style="background:#3498DB;color:#fff;border-radius:8px;">
                        <i class="fa-solid fa-flask me-1"></i> <span id="loadDemoText">Load Sample Data</span>
                    </button>
                    <button class="btn btn-sm" id="btnDeleteDemo" style="background:#E74C3C;color:#fff;border-radius:8px;">
                        <i class="fa-solid fa-trash-alt me-1"></i> Delete All Sample Data
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Tab navigation ──────────────────────────────────────────────
$('.stab-btn').on('click', function () {
    $('.stab-btn').removeClass('active');
    $(this).addClass('active');
    $('.stab-pane').addClass('d-none');
    $('#pane-' + $(this).data('tab')).removeClass('d-none');
});

// ── Colour swatches ─────────────────────────────────────────────
$('.swatch').on('click', function () {
    $('.swatch').removeClass('active'); $(this).addClass('active');
    $('#tColor').val($(this).data('color'));
});

// ── Token visibility toggle ─────────────────────────────────────
$('#toggleToken').on('click', function () {
    const $inp = $('#nWA');
    const isPass = $inp.attr('type') === 'password';
    $inp.attr('type', isPass ? 'text' : 'password');
    $('#tokenEyeIcon').toggleClass('fa-eye fa-eye-slash');
});

// ── Save helpers ────────────────────────────────────────────────
function saveSetting(url, data, btnId, textId, originalText) {
    $(`#${btnId}`).prop('disabled', true);
    $(`#${textId}`).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
    $.post(url, { _token: $('meta[name="csrf-token"]').attr('content'), ...data })
        .done(res => toastr.success(res.message))
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to save.'))
        .always(() => { $(`#${btnId}`).prop('disabled', false); $(`#${textId}`).text(originalText); });
}

$('#btnSaveGeneral').on('click', function () {
    const fd = new FormData();
    fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
    ['college_name','college_address','college_phone','college_email']
        .forEach(k => fd.append(k, $('#g'+k.split('_').map(w => w[0].toUpperCase()+w.slice(1)).join('').replace('CollegeName','Name').replace('CollegeAddress','Address').replace('CollegePhone','Phone').replace('CollegeEmail','Email')).val() || ''));
    fd.set('college_name', $('#gName').val()); fd.set('college_address', $('#gAddress').val());
    fd.set('college_phone', $('#gPhone').val()); fd.set('college_email', $('#gEmail').val());
    if ($('#gLogo')[0].files[0]) fd.append('logo', $('#gLogo')[0].files[0]);

    $('#btnSaveGeneral').prop('disabled', true);
    $('#saveGenText').html('<span class="spinner-border spinner-border-sm"></span> Saving...');
    $.ajax({ url: '{{ route("settings.general") }}', method: 'POST', data: fd, processData: false, contentType: false })
        .done(res => toastr.success(res.message))
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'))
        .always(() => { $('#btnSaveGeneral').prop('disabled', false); $('#saveGenText').text('Save College Info'); });
});

$('#btnSaveAtt').on('click', () => saveSetting('{{ route("settings.attendance") }}',
    { attendance_late_time: $('#aLate').val(), attendance_min_percent: $('#aMin').val(), attendance_warning_percent: $('#aWarn').val() },
    'btnSaveAtt', 'saveAttText', 'Save Attendance Settings'));

$('#btnSaveSec').on('click', () => saveSetting('{{ route("settings.security") }}',
    { session_timeout_minutes: $('#sTimeout').val(), login_max_attempts: $('#sMax').val(),
      login_lockout_minutes: $('#sLock').val(), two_factor_enabled: $('#s2fa').is(':checked') ? 1 : 0 },
    'btnSaveSec', 'saveSecText', 'Save Security Settings'));

$('#btnSaveNotif').on('click', function () {
    saveSetting('{{ route("settings.notifications") }}', { whatsapp_api_token: $('#nWA').val() }, 'btnSaveNotif', 'btnSaveNotif', 'Save Token');
});

$('#btnSaveTheme').on('click', function () {
    saveSetting('{{ route("settings.theme") }}',
        { theme_primary_color: $('#tColor').val(), theme_mode: $('#tMode').val(), font_size: $('#tFont').val() },
        'btnSaveTheme', 'btnSaveTheme', 'Save Appearance');
});

// ── Force logout ────────────────────────────────────────────────
$(document).on('click', '.btn-force-logout', function () {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({ title: `Force logout ${name}?`, text: 'This will immediately end all their active sessions.',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Logout' })
    .then(r => {
        if (!r.isConfirmed) return;
        $.post(`/settings/force-logout/${id}`, { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});

// ── Demo data ───────────────────────────────────────────────────
$('#btnLoadDemo').on('click', function () {
    Swal.fire({ title: 'Load sample data?', text: 'This adds demo records. Your real data will not be affected.',
        icon: 'question', showCancelButton: true, confirmButtonColor: '#3498DB', confirmButtonText: 'Yes, Load' })
    .then(r => {
        if (!r.isConfirmed) return;
        $(this).prop('disabled', true);
        $('#loadDemoText').html('<span class="spinner-border spinner-border-sm"></span> Loading...');
        $.post('{{ route("demo-data.load") }}', { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(res => Swal.fire('Done!', res.message, 'success'))
            .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'))
            .always(() => { $('#btnLoadDemo').prop('disabled', false); $('#loadDemoText').text('Load Sample Data'); });
    });
});

$('#btnDeleteDemo').on('click', function () {
    Swal.fire({ title: 'Delete all sample data?', text: 'All demo records will be permanently removed.',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Delete' })
    .then(r => {
        if (!r.isConfirmed) return;
        $.post('{{ route("demo-data.delete") }}', { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
