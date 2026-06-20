@extends('layouts.app')

@section('title', $teacher->name)

@section('breadcrumb')
    <a href="{{ route('teachers.index') }}" class="bc-item">Teachers</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">{{ $teacher->name }}</span>
@endsection

@push('styles')
<style>
    .profile-card { display: flex; align-items: center; gap: 20px; padding: 20px; }
    .profile-photo { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #f0f0f0; }
    .tab-nav { display: flex; gap: 6px; border-bottom: 2px solid #f0f0f0; margin-bottom: 20px; }
    .tab-btn { padding: 10px 18px; font-size: 13px; font-weight: 600; color: #6C757D;
        border: none; background: none; border-bottom: 3px solid transparent; cursor: pointer; }
    .tab-btn.active { color: #1E3A5F; border-bottom-color: #1E3A5F; }
    .assign-row { display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        border: 1px solid #f0f0f0; border-radius: 10px; margin-bottom: 8px; }
    .assign-pill { background: rgba(30,58,95,.08); color: #1E3A5F; padding: 4px 10px;
        border-radius: 8px; font-size: 12px; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-chalkboard-teacher"></i></span>
        Teacher Profile
    </h1>
    <a href="{{ route('teachers.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card-custom mb-3">
    <div class="profile-card">
        <img src="{{ $teacher->photo_url }}" class="profile-photo">
        <div>
            <h4 class="mb-1" style="color:#1E3A5F;">{{ $teacher->name }}</h4>
            <div class="text-muted small">{{ $teacher->email }} &nbsp;|&nbsp; {{ $teacher->whatsapp }}</div>
            <div class="mt-1">
                <span class="status-badge {{ $teacher->status ? 'status-active' : 'status-inactive' }}"
                      style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;
                      background:{{ $teacher->status ? 'rgba(39,174,96,.12)' : 'rgba(231,76,60,.12)' }};
                      color:{{ $teacher->status ? '#27AE60' : '#E74C3C' }};">
                    {{ $teacher->status ? 'Active' : 'Inactive' }}
                </span>
                <span class="text-muted small ms-2">{{ ucfirst($teacher->campus_access) }} Campus</span>
            </div>
        </div>
        <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-sm ms-auto"
           style="background:#F39C12;color:#fff;border-radius:8px;height:fit-content;">
            <i class="fa-solid fa-edit me-1"></i> Edit Basic Info
        </a>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" data-tab="assignments">Teaching Assignments</button>
    <button class="tab-btn" data-tab="incharge">Class Incharge</button>
</div>

{{-- TAB: Teaching Assignments --}}
<div class="tab-pane" id="tab-assignments">
    <div class="card-custom">
        <div class="card-body-c">
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <select id="addCampus" class="form-select form-select-sm">
                        <option value="">Campus</option><option value="boys">Boys</option><option value="girls">Girls</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="addYear" class="form-select form-select-sm">
                        <option value="">Year</option><option value="first">First</option><option value="second">Second</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="addSection" class="form-select form-select-sm" disabled><option value="">Section</option></select>
                </div>
                <div class="col-md-2">
                    <select id="addSubject" class="form-select form-select-sm" disabled><option value="">Subject</option></select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm w-100" id="btnAddAssignment" style="background:#1E3A5F;color:#fff;">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>

            <div id="assignmentsList">
                @forelse($teacher->sectionAssignments as $a)
                    <div class="assign-row" data-id="{{ $a->id }}">
                        <span class="assign-pill">{{ $a->section->code }}</span>
                        <span>{{ $a->subject->name }}</span>
                        <button class="btn btn-sm ms-auto btn-remove-assignment" data-id="{{ $a->id }}"
                                style="background:#E74C3C;color:#fff;"><i class="fa-solid fa-trash-alt"></i></button>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No assignments yet. Add one above.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- TAB: Class Incharge --}}
<div class="tab-pane d-none" id="tab-incharge">
    <div class="card-custom">
        <div class="card-body-c">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <select id="inchargeCampus" class="form-select form-select-sm">
                        <option value="">Campus</option><option value="boys">Boys</option><option value="girls">Girls</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="inchargeYear" class="form-select form-select-sm">
                        <option value="">Year</option><option value="first">First</option><option value="second">Second</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="inchargeSection" class="form-select form-select-sm" disabled><option value="">Section</option></select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm w-100" id="btnAssignIncharge" style="background:#F39C12;color:#fff;">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>

            <div id="inchargeList">
                @forelse($teacher->inchargeOf as $i)
                    <div class="assign-row" data-id="{{ $i->id }}">
                        <span class="assign-pill">{{ $i->section->code }}</span>
                        <span class="text-muted small">Incharge</span>
                        @if($i->substituteTeacher)
                            <span class="text-muted small">Substitute: {{ $i->substituteTeacher->name }}</span>
                        @endif
                        <button class="btn btn-sm ms-auto btn-remove-incharge" data-id="{{ $i->id }}"
                                style="background:#E74C3C;color:#fff;"><i class="fa-solid fa-trash-alt"></i></button>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">Not assigned as Class Incharge anywhere yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const teacherId = {{ $teacher->id }};

$('.tab-btn').on('click', function () {
    $('.tab-btn').removeClass('active'); $(this).addClass('active');
    $('.tab-pane').addClass('d-none');
    $('#tab-' + $(this).data('tab')).removeClass('d-none');
});

function loadSections(campusSel, yearSel, sectionSel) {
    const campus = $(campusSel).val(), year = $(yearSel).val();
    if (!campus || !year) return;
    $.get('{{ route("students.transfer.sections") }}', { campus, year }, function (res) {
        const $s = $(sectionSel);
        $s.prop('disabled', false).empty().append('<option value="">Select Section</option>');
        res.data.forEach(s => $s.append(`<option value="${s.id}">${s.code} (${s.count})</option>`));
    });
}

$('#addCampus, #addYear').on('change', () => loadSections('#addCampus', '#addYear', '#addSection'));
$('#inchargeCampus, #inchargeYear').on('change', () => loadSections('#inchargeCampus', '#inchargeYear', '#inchargeSection'));

$('#addSection').on('change', function () {
    const sectionId = $(this).val();
    if (!sectionId) return;
    $.get('{{ route("teachers.sections.subjects") }}', { section_id: sectionId }, function (res) {
        const $sub = $('#addSubject');
        $sub.prop('disabled', false).empty().append('<option value="">Select Subject</option>');
        res.data.forEach(s => $sub.append(`<option value="${s.id}">${s.name}</option>`));
    });
});

$('#btnAddAssignment').on('click', function () {
    const section_id = $('#addSection').val(), subject_id = $('#addSubject').val();
    if (!section_id || !subject_id) { toastr.warning('Select section and subject first.'); return; }

    $.post(`/teachers/${teacherId}/assignments`, {
        _token: $('meta[name="csrf-token"]').attr('content'), section_id, subject_id,
    }).done(res => { toastr.success(res.message); location.reload(); })
      .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to add assignment.'));
});

$(document).on('click', '.btn-remove-assignment', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Remove this assignment?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Remove' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/teachers/assignments/${id}`, method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});

$('#btnAssignIncharge').on('click', function () {
    const section_id = $('#inchargeSection').val();
    if (!section_id) { toastr.warning('Select a section first.'); return; }

    $.get('{{ route("teachers.incharge.check") }}', { section_id }, function (check) {
        if (check.has_conflict) {
            Swal.fire({
                title: 'Replace Class Incharge?',
                html: `<b>${check.section_code}</b> already has <b>${check.current_teacher}</b> as incharge. Replace?`,
                icon: 'question', showCancelButton: true, confirmButtonColor: '#F39C12',
                confirmButtonText: 'Yes, Replace', cancelButtonText: 'Cancel',
            }).then(r => { if (r.isConfirmed) doAssignIncharge(section_id, true); });
        } else {
            doAssignIncharge(section_id, false);
        }
    });
});

function doAssignIncharge(section_id, confirm_replace) {
    $.post(`/teachers/${teacherId}/incharge`, {
        _token: $('meta[name="csrf-token"]').attr('content'), section_id, confirm_replace,
    }).done(res => { toastr.success(res.message); location.reload(); })
      .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
}

$(document).on('click', '.btn-remove-incharge', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Remove Class Incharge?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Remove' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/teachers/incharge/${id}`, method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
