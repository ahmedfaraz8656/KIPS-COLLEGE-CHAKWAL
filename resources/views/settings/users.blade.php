@extends('layouts.app')

@section('title', 'User Management')

@section('breadcrumb')
    <a href="{{ route('settings.index') }}" class="bc-item">Settings</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Users</span>
@endsection

@push('styles')
<style>
    table.user-table th { background: #1E3A5F; color: #fff; padding: 8px; font-size: 12px; text-align: center; }
    table.user-table td { padding: 8px; text-align: center; font-size: 13px; }
    .online-dot { width: 8px; height: 8px; border-radius: 50%; background: #27AE60; display: inline-block; margin-right: 4px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-users-gear"></i></span>
        User Management
    </h1>
    <button class="btn btn-sm" id="btnNewUser" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-user-plus me-1"></i> Create User
    </button>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <div class="d-flex gap-2 mb-3">
            <select id="fRole" class="form-select form-select-sm" style="max-width:180px;">
                <option value="">All Roles</option>
                @foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach
            </select>
            <select id="fStatus" class="form-select form-select-sm" style="max-width:150px;">
                <option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option>
            </select>
        </div>
        <table class="user-table w-100">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody id="usersBody"></tbody>
        </table>
    </div>
</div>

{{-- CREATE MODAL --}}
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:14px 14px 0 0;">
                <h6 class="modal-title text-white">Create User</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6"><label class="form-label small fw-600">Name</label><input type="text" id="uName" class="form-control form-control-sm"></div>
                    <div class="col-6"><label class="form-label small fw-600">Email</label><input type="email" id="uEmail" class="form-control form-control-sm"></div>
                    <div class="col-6"><label class="form-label small fw-600">WhatsApp</label><input type="text" id="uWhatsapp" class="form-control form-control-sm"></div>
                    <div class="col-6"><label class="form-label small fw-600">Gender</label>
                        <select id="uGender" class="form-select form-select-sm"><option value="male">Male</option><option value="female">Female</option></select></div>
                    <div class="col-12"><label class="form-label small fw-600">Role(s)</label>
                        <select id="uRoles" class="form-select form-select-sm" multiple>
                            @foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach
                        </select></div>
                    <div class="col-12"><label class="form-label small fw-600">Access Expiry (optional)</label>
                        <input type="datetime-local" id="uExpiry" class="form-control form-control-sm"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSaveUser" style="background:#27AE60;color:#fff;border-radius:8px;">Create</button>
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
        let html = '';
        res.data.forEach(u => {
            html += `<tr>
                <td>${u.is_online ? '<span class="online-dot"></span>' : ''}${u.name}</td>
                <td>${u.email}</td><td>${u.role}</td>
                <td><span style="color:${u.status ? '#27AE60' : '#E74C3C'};font-weight:600;">${u.status ? 'Active' : 'Inactive'}</span></td>
                <td>${u.last_login}</td>
                <td>
                    <button class="btn btn-sm btn-reset-pwd" data-id="${u.id}" title="Reset Password" style="background:#F39C12;color:#fff;"><i class="fa-solid fa-key"></i></button>
                    <button class="btn btn-sm btn-toggle-user" data-id="${u.id}" title="${u.status?'Disable':'Enable'}" style="background:${u.status?'#E74C3C':'#27AE60'};color:#fff;"><i class="fa-solid ${u.status?'fa-ban':'fa-check'}"></i></button>
                </td>
            </tr>`;
        });
        $('#usersBody').html(html || '<tr><td colspan="6" class="text-muted py-3">No users found</td></tr>');
    });
}

$('#fRole, #fStatus').on('change', loadUsers);

$('#btnSaveUser').on('click', function () {
    $.post('{{ route("settings.users.store") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        name: $('#uName').val(), email: $('#uEmail').val(), whatsapp: $('#uWhatsapp').val(),
        gender: $('#uGender').val(), roles: $('#uRoles').val(), access_expires_at: $('#uExpiry').val(),
    }).done(function (res) {
        userModal.hide();
        Swal.fire('User Created!', `Temporary password: <b>${res.temp_password}</b><br>Share this with the user.`, 'success');
        loadUsers();
    }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
});

$(document).on('click', '.btn-reset-pwd', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Reset Password?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#F39C12', confirmButtonText: 'Yes, Reset' }).then(r => {
        if (!r.isConfirmed) return;
        $.post(`/settings/users/${id}/reset-password`, { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(res => Swal.fire('Password Reset', `New temporary password: <b>${res.temp_password}</b>`, 'success'));
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
