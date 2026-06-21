@extends('layouts.app')

@section('title', 'Roll Number Slips')

@section('breadcrumb')
    <span class="bc-current">Roll Slips</span>
@endsection

@push('styles')
<style>
    .student-check-row { display: flex; align-items: center; gap: 10px; padding: 7px 10px; font-size: 13px; border-radius: 6px; }
    .student-check-row:hover { background: #f8f9fa; }
    #studentsBox { max-height: 420px; overflow-y: auto; border: 1px solid #f0f0f0; border-radius: 10px; padding: 8px; }
    .preview-count { background: rgba(30,58,95,.08); color: #1E3A5F; padding: 10px 16px; border-radius: 10px; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-id-card"></i></span>
        Roll Number Slips
    </h1>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card-custom mb-3">
            <div class="card-body-c">
                <label class="form-label small fw-600">Select Exam</label>
                <select id="examSelect" class="form-select form-select-sm mb-3">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)<option value="{{ $exam->id }}">{{ $exam->name }}</option>@endforeach
                </select>

                <div class="row g-2 mb-2">
                    <div class="col-4">
                        <select id="fCampus" class="form-select form-select-sm">
                            <option value="both">All Campus</option><option value="boys">Boys</option><option value="girls">Girls</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <select id="fYear" class="form-select form-select-sm">
                            <option value="both">All Year</option><option value="first">First</option><option value="second">Second</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <select id="fSection" class="form-select form-select-sm"><option value="">All Sections</option></select>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Students</span>
                    <button class="btn btn-sm p-0 text-primary" id="btnSelectAllStudents" style="font-size:12px;">Select All</button>
                </div>
                <div id="studentsBox"><p class="text-muted text-center py-3 small">Select filters to load students</p></div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div id="previewSummary" class="preview-count mb-3 d-none"></div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn" id="btnGenerate" style="background:#1E3A5F;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-magic me-1"></i> <span id="btnGenerateText">Generate Slips</span>
            </button>
            <button class="btn" id="btnDownload" style="background:#27AE60;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-download me-1"></i> Download PDF
            </button>
            <button class="btn" id="btnPrint" style="background:#2C3E50;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-print me-1"></i> Print
            </button>
        </div>
        <p class="text-muted small mt-3">
            <i class="fa-solid fa-circle-info"></i>
            Slips are generated 2 per A4 page with a cut line, print-friendly black borders.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedStudents = new Set();

function loadSections() {
    const campus = $('#fCampus').val(), year = $('#fYear').val();
    if (campus === 'both' || year === 'both') { $('#fSection').html('<option value="">All Sections</option>'); return; }
    $.get('{{ route("students.transfer.sections") }}', { campus, year }, function (res) {
        const $s = $('#fSection');
        $s.empty().append('<option value="">All Sections</option>');
        res.data.forEach(s => $s.append(`<option value="${s.id}">${s.code}</option>`));
    });
}

function loadStudents() {
    $.get('{{ route("results.resolve-students") }}', {
        campus: $('#fCampus').val(), year: $('#fYear').val(), section_id: $('#fSection').val(),
    }, function (res) {
        if (!res.data.length) { $('#studentsBox').html('<p class="text-muted text-center py-3 small">No students found</p>'); return; }
        let html = '';
        res.data.forEach(s => {
            html += `<label class="student-check-row">
                <input type="checkbox" class="pick-student" value="${s.id}">
                <span class="text-muted" style="min-width:70px;">${s.roll_number}</span><span>${s.name}</span>
            </label>`;
        });
        $('#studentsBox').html(html);
    });
}

$('#fCampus, #fYear').on('change', () => { loadSections(); loadStudents(); });
$('#fSection').on('change', loadStudents);

$(document).on('change', '.pick-student', function () {
    const id = $(this).val();
    this.checked ? selectedStudents.add(id) : selectedStudents.delete(id);
    $('#previewSummary').removeClass('d-none').text(`${selectedStudents.size} student(s) selected for roll slip generation`);
});

$('#btnSelectAllStudents').on('click', () => $('.pick-student').prop('checked', true).trigger('change'));

function buildSlipForm(action) {
    if (!$('#examSelect').val()) { toastr.warning('Please select an exam first.'); return; }
    if (!selectedStudents.size) { toastr.warning('Please select at least one student.'); return; }

    const form = $('<form method="POST" target="_blank"></form>').attr('action', '{{ route("roll-slips.pdf") }}');
    form.append(`<input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">`);
    form.append(`<input type="hidden" name="exam_id" value="${$('#examSelect').val()}">`);
    Array.from(selectedStudents).forEach(id => form.append(`<input type="hidden" name="student_ids[]" value="${id}">`));
    if (action === 'download') form.append('<input type="hidden" name="download" value="1">');
    $('body').append(form);
    form.submit();
    form.remove();
}

$('#btnGenerate, #btnPrint').on('click', () => buildSlipForm('view'));
$('#btnDownload').on('click', () => buildSlipForm('download'));

loadStudents();
</script>
@endpush
