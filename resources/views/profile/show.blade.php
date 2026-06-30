@extends('layouts.app')

@section('title', 'My Profile')

@section('breadcrumb')
    <span class="bc-current">My Profile</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-user-circle"></i></span>
        My Profile
    </h1>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card-custom">
            <div class="card-body-c">
                <h6 style="color:#1E3A5F;" class="mb-3">Profile Information</h6>
                <form id="profileForm" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center mb-3">
                        <img src="{{ $user->photo_url }}" id="profilePreview" style="width:90px;height:90px;border-radius:50%;object-fit:cover;cursor:pointer;">
                        <input type="file" name="photo" id="photoInput" accept="image/*" hidden>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-600">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-600">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-600">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-600">WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control" value="{{ $user->whatsapp }}">
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:#27AE60;color:#fff;border-radius:8px;">
                        <i class="fa-solid fa-save me-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card-custom" id="passwordForm">
            <div class="card-body-c">
                <h6 style="color:#1E3A5F;" class="mb-3">Change Password</h6>
                <form id="passwordChangeForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-600">Current Password</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-600">New Password</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-600">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:#1E3A5F;color:#fff;border-radius:8px;">
                        <i class="fa-solid fa-key me-1"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#profilePreview').on('click', () => $('#photoInput').click());
$('#photoInput').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => $('#profilePreview').attr('src', e.target.result);
    reader.readAsDataURL(file);
});

$('#profileForm').on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    $.ajax({ url: '{{ route("profile.update") }}', method: 'POST', data: formData, processData: false, contentType: false })
        .done(res => toastr.success(res.message))
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to update.'));
});

$('#passwordChangeForm').on('submit', function (e) {
    e.preventDefault();
    $.post('{{ route("profile.change-password") }}', $(this).serialize())
        .done(res => { toastr.success(res.message); $('#passwordChangeForm')[0].reset(); })
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to change password.'));
});
</script>
@endpush
