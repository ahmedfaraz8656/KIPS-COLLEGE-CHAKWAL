@extends('layouts.app')

@section('title', 'Edit Teacher')

@section('breadcrumb')
    <a href="{{ route('teachers.index') }}" class="bc-item">Teachers</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Edit {{ $teacher->name }}</span>
@endsection

@push('styles')
<style>
    .form-label { font-size: 13px; font-weight: 600; color: #2C3E50; margin-bottom: 6px; }
    .form-control, .form-select { border: 2px solid #e9ecef; border-radius: 8px; font-size: 13px; padding: 9px 12px; }
    .req { color: #E74C3C; }
    .role-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px;
        border: 2px solid #e9ecef; border-radius: 20px; font-size: 13px; cursor: pointer; margin: 3px; }
    .role-chip.selected { background: #1E3A5F; color: #fff; border-color: #1E3A5F; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-user-edit"></i></span>
        Edit Teacher
    </h1>
    <a href="{{ route('teachers.show', $teacher) }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <form id="teacherEditForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-3 text-center">
                    <label class="form-label d-block">Photo</label>
                    <img src="{{ $teacher->photo_url }}" id="photoPreview" style="width:100px;height:100px;border-radius:50%;object-fit:cover;cursor:pointer;">
                    <input type="file" name="photo" id="photoInput" accept="image/*" hidden>
                </div>
                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $teacher->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="father_name" class="form-control" value="{{ $teacher->father_name }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender <span class="req">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male" {{ $teacher->gender == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $teacher->gender == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp <span class="req">*</span></label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ $teacher->whatsapp }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" name="alternate_phone" class="form-control" value="{{ $teacher->alternate_phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ $teacher->email }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNIC</label>
                            <input type="text" name="cnic" class="form-control" value="{{ $teacher->cnic }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Joining</label>
                            <input type="date" name="date_of_joining" class="form-control" value="{{ $teacher->date_of_joining?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control" value="{{ $teacher->qualification }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Campus Access <span class="req">*</span></label>
                            <select name="campus_access" class="form-select" required>
                                <option value="both" {{ $teacher->campus_access == 'both' ? 'selected' : '' }}>Both Campuses</option>
                                <option value="boys" {{ $teacher->campus_access == 'boys' ? 'selected' : '' }}>Boys Only</option>
                                <option value="girls" {{ $teacher->campus_access == 'girls' ? 'selected' : '' }}>Girls Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">System Role(s)</label>
                    <div>
                        @foreach(['Teacher', 'Class Incharge', 'Exam Controller'] as $role)
                            <span class="role-chip {{ in_array($role, $currentRoles) ? 'selected' : '' }}" data-role="{{ $role }}">
                                <i class="fa-solid fa-check {{ in_array($role, $currentRoles) ? '' : 'd-none' }} check-icon"></i> {{ $role }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('teachers.show', $teacher) }}" class="btn" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</a>
                <button type="submit" class="btn" id="btnSave" style="background:#1E3A5F;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-save me-1"></i> <span id="btnSaveText">Update Teacher</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedRoles = [@foreach($currentRoles as $r) '{{ $r }}', @endforeach];

$('.role-chip').on('click', function () {
    const role = $(this).data('role');
    $(this).toggleClass('selected');
    $(this).find('.check-icon').toggleClass('d-none');
    if (selectedRoles.includes(role)) selectedRoles = selectedRoles.filter(r => r !== role);
    else selectedRoles.push(role);
});

$('#photoPreview').on('click', () => $('#photoInput').click());
$('#photoInput').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => $('#photoPreview').attr('src', e.target.result);
    reader.readAsDataURL(file);
});

$('#teacherEditForm').on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('_method', 'PUT');
    selectedRoles.forEach(r => formData.append('roles[]', r));

    $('#btnSave').prop('disabled', true);
    $('#btnSaveText').html('<span class="spinner-border spinner-border-sm"></span> Updating...');

    $.ajax({
        url: '{{ route("teachers.update", $teacher) }}', method: 'POST', data: formData,
        processData: false, contentType: false,
    }).done(function (res) {
        toastr.success(res.message);
        setTimeout(() => window.location.href = '{{ route("teachers.show", $teacher) }}', 800);
    }).fail(function (xhr) {
        toastr.error(xhr.responseJSON?.message || 'Update failed.');
        $('#btnSave').prop('disabled', false);
        $('#btnSaveText').text('Update Teacher');
    });
});
</script>
@endpush
