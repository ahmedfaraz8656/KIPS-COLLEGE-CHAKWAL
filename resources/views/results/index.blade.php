@extends('layouts.app')

@section('title', 'Progress Reports')

@section('breadcrumb')
    <span class="bc-current">Results</span>
@endsection

@push('styles')
<style>
    .exam-chip { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
        border: 2px solid #e9ecef; border-radius: 20px; font-size: 13px; cursor: pointer; margin: 3px; }
    .exam-chip.selected { background: #1E3A5F; color: #fff; border-color: #1E3A5F; }
    .student-check-row { display: flex; align-items: center; gap: 10px; padding: 7px 10px; font-size: 13px; border-radius: 6px; }
    .student-check-row:hover { background: #f8f9fa; }
    #studentsBox { max-height: 320px; overflow-y: auto; border: 1px solid #f0f0f0; border-radius: 10px; padding: 8px; }
    .mini-card { background: #fff; border: 1px solid #f0f0f0; border-radius: 12px; padding: 14px; font-size: 12px; }
    .mini-card h6 { color: #1E3A5F; font-size: 13px; margin-bottom: 6px; }
    .preview-count { background: rgba(30,58,95,.08); color: #1E3A5F; padding: 10px 16px; border-radius: 10px; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-chart-bar"></i></span>
        Progress Reports
    </h1>
</div>

<div class="row g-3">
    {{-- LEFT: Selection panel --}}
    <div class="col-md-5">
        <div class="card-custom mb-3">
            <div class="card-body-c">
                <label class="form-label small fw-600 d-block mb-2">Select Exam(s)</label>
                <div id="examChips">
                    @foreach($exams as $exam)
                        <span class="exam-chip" data-id="{{ $exam->id }}">{{ $exam->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card-custom mb-3">
            <div class="card-body-c">
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
                <input type="text" id="studentSearch" class="form-control form-control-sm mb-2" placeholder="Search student...">

                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Students</span>
                    <button class="btn btn-sm p-0 text-primary" id="btnSelectAllStudents" style="font-size:12px;">Select All</button>
                </div>
                <div id="studentsBox">
                    <p class="text-muted text-center py-3 small">Loading students...</p>
                </div>
            </div>
        </div>

        <button class="btn w-100" id="btnPreview" style="background:#1E3A5F;color:#fff;border-radius:8px;font-weight:600;">
            <i class="fa-solid fa-eye me-1"></i> <span id="btnPreviewText">Preview Results</span>
        </button>
    </div>

    {{-- RIGHT: Preview + Actions --}}
    <div class="col-md-7">
        <div id="previewSummary" class="preview-count mb-3 d-none"></div>

        <div id="previewGrid" class="row g-2"></div>

        <div id="actionButtons" class="d-none mt-3 d-flex gap-2 flex-wrap">
            <button class="btn btn-sm" id="btnGeneratePdf" style="background:#E74C3C;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-file-pdf me-1"></i> Generate PDF
            </button>
            <button class="btn btn-sm" id="btnDownloadPdf" style="background:#27AE60;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-download me-1"></i> Download PDF
            </button>
            <button class="btn btn-sm" id="btnShareWhatsapp" style="background:#25D366;color:#fff;border-radius:8px;">
                <i class="fa-brands fa-whatsapp me-1"></i> Share via WhatsApp
            </button>
            <button class="btn btn-sm" id="btnPrint" style="background:#2C3E50;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-print me-1"></i> Print
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedExams = new Set(), selectedStudents = new Set();

$(document).on('click', '.exam-chip', function () {
    $(this).toggleClass('selected');
    const id = $(this).data('id');
    selectedExams.has(id) ? selectedExams.delete(id) : selectedExams.add(id);
});

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
    $.get('{{ route("exams.results.resolve-students") }}', {
        campus: $('#fCampus').val(), year: $('#fYear').val(),
        section_id: $('#fSection').val(), search: $('#studentSearch').val(),
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
$('#studentSearch').on('input', () => { clearTimeout(window._s); window._s = setTimeout(loadStudents, 300); });

$(document).on('change', '.pick-student', function () {
    const id = $(this).val();
    this.checked ? selectedStudents.add(id) : selectedStudents.delete(id);
});

$('#btnSelectAllStudents').on('click', function () {
    $('.pick-student').prop('checked', true).trigger('change');
});

$('#btnPreview').on('click', function () {
    if (!selectedExams.size) { toastr.warning('Select at least one exam.'); return; }
    if (!selectedStudents.size) { toastr.warning('Select at least one student.'); return; }

    $('#btnPreviewText').html('<span class="spinner-border spinner-border-sm"></span> Loading...');

    $.post('{{ route("exams.results.preview") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        exam_ids: Array.from(selectedExams), student_ids: Array.from(selectedStudents),
    }).done(function (res) {
        $('#previewSummary').removeClass('d-none').html(
            `Generating results for <b>${res.data.length}</b> student(s) across <b>${selectedExams.size}</b> exam(s)`
        );

        let html = '';
        res.data.forEach(r => {
            html += `<div class="col-md-4"><div class="mini-card">
                <h6>${r.student.name}</h6>
                <div class="text-muted">${r.student.roll_number} | ${r.student.section}</div>
                ${r.cumulative ? `<div class="mt-2"><b>${r.cumulative.percent}%</b> — Grade ${r.cumulative.grade}</div>`
                    : (r.exams[0] ? `<div class="mt-2"><b>${r.exams[0].percent}%</b> — Grade ${r.exams[0].grade}</div>` : '')}
                <div class="text-muted small mt-1">Attendance: ${r.attendance.percent}%</div>
            </div></div>`;
        });
        $('#previewGrid').html(html);
        $('#actionButtons').removeClass('d-none');
    }).always(function () {
        $('#btnPreviewText').text('Preview Results');
    });
});

function buildPdfForm(download) {
    const form = $('<form method="POST" target="_blank"></form>').attr('action', '{{ route("exams.results.pdf") }}');
    form.append(`<input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">`);
    Array.from(selectedExams).forEach(id => form.append(`<input type="hidden" name="exam_ids[]" value="${id}">`));
    Array.from(selectedStudents).forEach(id => form.append(`<input type="hidden" name="student_ids[]" value="${id}">`));
    if (download) form.append('<input type="hidden" name="download" value="1">');
    $('body').append(form);
    form.submit();
    form.remove();
}

$('#btnGeneratePdf').on('click', () => buildPdfForm(false));
$('#btnDownloadPdf').on('click', () => buildPdfForm(true));
$('#btnPrint').on('click', () => buildPdfForm(false));

$('#btnShareWhatsapp').on('click', function () {
    $.post('{{ route("exams.results.share-link") }}', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        exam_ids: Array.from(selectedExams), student_ids: Array.from(selectedStudents),
    }).done(function (res) {
        window.open(`https://wa.me/?text=${encodeURIComponent('Result Card: ' + res.url)}`, '_blank');
    });
});

loadStudents();
</script>
@endpush
