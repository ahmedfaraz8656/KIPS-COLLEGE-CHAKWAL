@extends('layouts.app')

@section('title', 'Edit Student')

@section('breadcrumb')
    <a href="{{ route('students.index') }}" class="bc-item">Students</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Edit</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title"><span class="page-icon"><i class="fa-solid fa-user-edit"></i></span> Edit Student</h1>
    <a href="{{ route('students.show', $student) }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <form id="editForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="field-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control-custom" name="name" value="{{ $student->name }}">
                </div>
                <div class="col-md-6">
                    <label class="field-label">Father's Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control-custom" name="father_name" value="{{ $student->father_name }}">
                </div>
                <div class="col-md-6">
                    <label class="field-label">WhatsApp Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control-custom" name="whatsapp" value="{{ $student->whatsapp }}">
                </div>
                <div class="col-md-6">
                    <label class="field-label">Alternate Phone</label>
                    <input type="text" class="form-control-custom" name="alternate_phone" value="{{ $student->alternate_phone }}">
                </div>
                <div class="col-md-6">
                    <label class="field-label">CNIC / B-Form</label>
                    <input type="text" class="form-control-custom" name="cnic_bform" value="{{ $student->cnic_bform }}">
                </div>
                <div class="col-md-6">
                    <label class="field-label">Address</label>
                    <input type="text" class="form-control-custom" name="address" value="{{ $student->address }}">
                </div>
                <div class="col-md-12">
                    <div class="alert alert-info" style="border-radius:10px;font-size:13px;background:#f0f8ff;border:none;color:#1a5276;">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Roll Number, Campus, Year, Section, and Program cannot be changed here. Use <strong>Move/Transfer</strong> for section changes.
                    </div>
                </div>
            </div>
            <div class="text-end mt-3 pt-3" style="border-top:1px solid #f0f0f0;">
                <button type="submit" class="btn" id="btnSave" style="background:#27AE60;color:#fff;border-radius:8px;padding:10px 24px;">
                    <i class="fa-solid fa-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#btnSave');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: '{{ route("students.update", $student) }}',
            method: 'PUT',
            data: $(this).serialize(),
        }).done(res => {
            toastr.success(res.message);
            setTimeout(() => window.location.href = '{{ route("students.show", $student) }}', 1000);
        }).fail(() => {
            toastr.error('Could not update student.');
            $btn.prop('disabled', false).html('<i class="fa-solid fa-save me-1"></i> Save Changes');
        });
    });
});
</script>
@endpush
