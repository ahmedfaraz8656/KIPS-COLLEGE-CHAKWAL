@extends('layouts.app')

@section('title', 'Add Teacher')

@section('breadcrumb')
    <a href="{{ route('teachers.index') }}" class="bc-item">Teachers</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Add Teacher</span>
@endsection

@push('styles')
<style>
    .form-label { font-size: 13px; font-weight: 600; color: #2C3E50; margin-bottom: 6px; }
    .form-control, .form-select { border: 2px solid #e9ecef; border-radius: 8px; font-size: 13px; padding: 9px 12px; }
    .form-control:focus, .form-select:focus { border-color: #1E3A5F; box-shadow: 0 0 0 3px rgba(30,58,95,.08); }
    .req { color: #E74C3C; }
    .photo-drop { border: 2px dashed #e9ecef; border-radius: 12px; padding: 24px; text-align: center;
        cursor: pointer; transition: all .2s; }
    .photo-drop:hover, .photo-drop.dragover { border-color: #1E3A5F; background: rgba(30,58,95,.03); }
    .role-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px;
        border: 2px solid #e9ecef; border-radius: 20px; font-size: 13px; cursor: pointer; margin: 3px; }
    .role-chip.selected { background: #1E3A5F; color: #fff; border-color: #1E3A5F; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-user-plus"></i></span>
        Add Teacher
    </h1>
    <a href="{{ route('teachers.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <form id="teacherForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">

                <div class="col-md-3 text-center">
                    <label class="form-label d-block">Photo</label>
                    <div class="photo-drop" id="photoDrop">
                        <img id="photoPreview" src="" style="max-width:100px;border-radius:50%;display:none;">
                        <div id="photoPlaceholder">
                            <i class="fa-solid fa-camera fa-2x text-muted mb-2 d-block"></i>
                            <span class="small text-muted">Click or drag photo</span>
                        </div>
                        <input type="file" name="photo" id="photoInput" accept="image/*" hidden>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="father_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender <span class="req">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp <span class="req">*</span></label>
                            <input type="text" name="whatsapp" class="form-control" placeholder="03XXXXXXXXX" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" name="alternate_phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email (for login) <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNIC</label>
                            <input type="text" name="cnic" class="form-control" placeholder="00000-0000000-0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Joining</label>
                            <input type="date" name="date_of_joining" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Campus Access <span class="req">*</span></label>
                            <select name="campus_access" class="form-select" required>
                                <option value="both">Both Campuses</option>
                                <option value="boys">Boys Only</option>
                                <option value="girls">Girls Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">System Role(s) <span class="req">*</span></label>
                    <div>
                        @foreach(['Teacher', 'Class Incharge', 'Exam Controller'] as $role)
                            <span class="role-chip" data-role="{{ $role }}">
                                <i class="fa-solid fa-check d-none check-icon"></i> {{ $role }}
                            </span>
                        @endforeach
                    </div>
                    <div id="rolesContainer"></div>
                </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('teachers.index') }}" class="btn" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</a>
                <button type="submit" class="btn" id="btnSave" style="background:#1E3A5F;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-save me-1"></i> <span id="btnSaveText">Save Teacher</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedRoles = ['Teacher'];
$('.role-chip[data-role="Teacher"]').addClass('selected').find('.check-icon').removeClass('d-none');

$('.role-chip').on('click', function () {
    const role = $(this).data('role');
    $(this).toggleClass('selected');
    $(this).find('.check-icon').toggleClass('d-none');
    if (selectedRoles.includes(role)) selectedRoles = selectedRoles.filter(r => r !== role);
    else selectedRoles.push(role);
});

$('#photoDrop').on('click', () => $('#photoInput').click());
$('#photoInput').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        $('#photoPreview').attr('src', e.target.result).show();
        $('#photoPlaceholder').hide();
    };
    reader.readAsDataURL(file);
});

$('#teacherForm').on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    selectedRoles.forEach(r => formData.append('roles[]', r));

    $('#btnSave').prop('disabled', true);
    $('#btnSaveText').html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.ajax({
        url: '{{ route("teachers.store") }}', method: 'POST', data: formData,
        processData: false, contentType: false,
    }).done(function (res) {
        toastr.success(res.message);
        setTimeout(() => window.location.href = res.redirect, 800);
    }).fail(function (xhr) {
        const errors = xhr.responseJSON?.errors;
        if (errors) {
            let msg = Object.values(errors).map(e => e[0]).join('<br>');
            toastr.error(msg);
        } else {
            toastr.error(xhr.responseJSON?.message || 'Something went wrong.');
        }
        $('#btnSave').prop('disabled', false);
        $('#btnSaveText').text('Save Teacher');
    });
});
</script>
@endpush
