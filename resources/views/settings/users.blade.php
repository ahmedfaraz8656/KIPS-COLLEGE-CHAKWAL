@extends('layouts.app')

@section('title', 'User Management')

@section('breadcrumb')
    <a href="{{ route('settings.index') }}" class="bc-item">Settings</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Users</span>
@endsection

@push('styles')
<style>
    .filter-row { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; }
    .fgrp { display:flex; flex-direction:column; gap:4px; }
    .fgrp label { font-size:11px; font-weight:700; color:#6C757D; text-transform:uppercase; letter-spacing:.3px; }
    .fgrp select { padding:8px 12px; border:2px solid #e9ecef; border-radius:8px; font-size:13px; min-width:150px; background:#fff; }

    table.um-t th { background:#1E3A5F; color:#fff; padding:10px 12px; font-size:12px; font-weight:600; text-align:center; }
    table.um-t td { padding:10px 12px; text-align:center; font-size:13px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
    table.um-t td.td-l { text-align:left; }
    table.um-t tr:hover td { background:#f8faff; }
    .udot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:5px; }
    .udot-online  { background:#27AE60; box-shadow:0 0 0 2px rgba(39,174,96,.2); }
    .udot-offline { background:#dee2e6; }
    .role-pill { font-size:10px; font-weight:700; padding:2px 8px; border-radius:8px;
        background:rgba(30,58,95,.1); color:#1E3A5F; }
    .temp-pwd-box { background:#1E3A5F; color:#fff; font-family:monospace; font-size:16px; font-weight:700;
        padding:10px 20px; border-radius:10px; letter-spacing:2px; text-align:center; margin:10px 0; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-users-gear"></i></span>
        User Management
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('settings.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        <button class="btn btn-sm" id="btnNewUser" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-user-plus me-1"></i> Create User
        </button>
    </div>
</div>

<div class="card-custom mb-3">
    <div class="card-body-c">
        <div class="filter-row">
            <div class="fgrp">
                <label>Role</label>
                <select id="fRole">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach
                </select>
            </div>
            <div class="fgrp">
                <label>Status</label>
                <select id="fStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <table class="um-t w-100">
            <thead>
                <tr>
                    <th class="text-start">User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersBody">
                <tr><td colspan="5" class="py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Create User Modal --}}
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white"><i class="fa-solid fa-user-plus me-2"></i>Create User</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Full Name</label>
                        <input type="text" id="uName" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Email (Login)</label>
                        <input type="email" id="uEmail" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">WhatsApp</label>
                        <input type="text" id="uWhatsapp" class="form-control form-control-sm" placeholder="03XXXXXXXXX">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Gender</label>
                        <select id="uGender" class="form-select form-select-sm">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Role(s) — hold Ctrl to select multiple</label>
                        <select id="uRoles" class="form-select form-select-sm" multiple size="5">
                            @foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#6C757D;">Access Expiry (optional — leave blank for permanent)</label>
                        <input type="datetime-local" id="uExpiry" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSaveUser" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-user-check me-1"></i> <span id="saveUserText">Create User</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const userModal = new bootstrap.Modal(document.getElementById('userModal'));
$('#btnNewUser').on('click', () => userModal.show());

function loadUsers() {
    $.get('{{ route("settings.users.list") }}', { role: $('#fRole').val(), status: $('#fStatus').val() }, function (res) {
        if (!res.data.length) {
            $('#usersBody').html('<tr><td colspan="5"><div class="empty-state-block py-4"><i class="fa-solid fa-users-slash"></i><p>No users found.</p></div></td></tr>');
            return;
        }
        let html = '';
        res.data.forEach(u => {
            const isActive = u.status;
            html += `<tr>
                <td class="td-l">
                    <div style="font-weight:600;">
                        <span class="udot ${u.is_online ? 'udot-online' : 'udot-offline'}"></span>
                        ${u.name}
                    </div>
                    <div style="font-size:11.5px;color:#adb5bd;">${u.email}</div>
                </td>
                <td><span class="role-pill">${u.role}</span></td>
                <td>
                    <span style="color:${isActive ? '#27AE60' : '#E74C3C'};font-weight:700;font-size:12px;">
                        ${isActive ? '● Active' : '○ Inactive'}
                    </span>
                </td>
                <td style="font-size:12px;">${u.last_login}</td>
                <td>
                    <button class="btn btn-sm btn-reset-pwd" data-id="${u.id}" title="Reset Password" style="background:#F39C12;color:#fff;border-radius:6px;">
                        <i class="fa-solid fa-key"></i>
                    </button>
                    <button class="btn btn-sm btn-toggle-user" data-id="${u.id}" title="${isActive ? 'Disable' : 'Enable'}"
                            style="background:${isActive ? '#E74C3C' : '#27AE60'};color:#fff;border-radius:6px;">
                        <i class="fa-solid ${isActive ? 'fa-ban' : 'fa-check'}"></i>
                    </button>
                </td>
            </tr>`;
        });
        $('#usersBody').html(html);
    });
}

$('#fRole, #fStatus').on('change', loadUsers);

$('#btnSaveUser').on('click', function () {
    const roles = Array.from(document.getElementById('uRoles').selectedOptions).map(o => o.value);
    if (!$('#uName').val() || !$('#uEmail').val() || !roles.length) {
        toastr.warning('Name, email, and at least one role are required.'); return;
    }
    $(this).prop('disabled', true);
    $('#saveUserText').html('<span class="spinner-border spinner-border-sm"></span> Creating...');
    $.post('{{ route("settings.users.store") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        name: $('#uName').val(), email: $('#uEmail').val(),
        whatsapp: $('#uWhatsapp').val(), gender: $('#uGender').val(),
        roles, access_expires_at: $('#uExpiry').val() || null,
    }).done(res => {
        userModal.hide();
        Swal.fire({
            title: '✅ User Created!',
            html: `Share this temporary password with <b>${$('#uName').val()}</b>. They must change it on first login.<br>
                   <div class="temp-pwd-box">${res.temp_password}</div>`,
            icon: 'success', confirmButtonColor: '#1E3A5F',
        });
        loadUsers();
    }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to create user.'))
      .always(() => { $('#btnSaveUser').prop('disabled', false); $('#saveUserText').text('Create User'); });
});

$(document).on('click', '.btn-reset-pwd', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Reset password?', text: 'A new temporary password will be generated.',
        icon: 'question', showCancelButton: true, confirmButtonColor: '#F39C12', confirmButtonText: 'Yes, Reset' })
    .then(r => {
        if (!r.isConfirmed) return;
        $.post(`/settings/users/${id}/reset-password`, { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(res => Swal.fire({
                title: 'Password Reset!',
                html: `New temporary password:<br><div class="temp-pwd-box">${res.temp_password}</div>`,
                icon: 'success', confirmButtonColor: '#1E3A5F',
            }));
    });
});

$(document).on('click', '.btn-toggle-user', function () {
    const id = $(this).data('id');
    $.post(`/settings/users/${id}/toggle-status`, { _token: $('meta[name="csrf-token"]').attr('content') })
        .done(res => { toastr.success(res.message); loadUsers(); });
});

loadUsers();
</script>
@endpush
