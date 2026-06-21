@extends('layouts.app')

@section('title', 'Students')

@section('breadcrumb')
    <span class="bc-current">Students</span>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<style>
    .filter-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 16px; }
    .filter-bar select {
        padding: 8px 12px; border: 2px solid #e9ecef; border-radius: 8px;
        font-size: 13px; min-width: 130px; background: #fff;
    }
    .bulk-bar {
        display: none; align-items: center; gap: 10px; padding: 10px 16px;
        background: rgba(30,58,95,0.06); border-radius: 10px; margin-bottom: 12px;
    }
    .bulk-bar.show { display: flex; }
    mark { background: #FFF3CD; padding: 0 2px; border-radius: 2px; }
    .row-highlighted { animation: highlightRow 2s ease; }
    @keyframes highlightRow { 0% { background: #FFF3CD; } 100% { background: transparent; } }
    .status-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .status-active { background: rgba(39,174,96,0.12); color: #27AE60; }
    .status-transferred { background: rgba(243,156,18,0.12); color: #F39C12; }
    .status-promoted { background: rgba(52,152,219,0.12); color: #3498DB; }
    .student-photo-sm { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
    table.dataTable thead th { text-align: center !important; background: #1E3A5F !important; color: #fff !important; }
    table.dataTable tbody tr:hover { background: #EBF5FB !important; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-users"></i></span>
        Students
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-sm" id="btnImport" style="background:#3498DB;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-file-import me-1"></i> Import Excel
        </button>
        <button class="btn btn-sm" id="btnExport" style="background:#27AE60;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-file-excel me-1"></i> Export
        </button>
        <a href="{{ route('students.create') }}" class="btn btn-sm" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-user-plus me-1"></i> Add Student
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card-custom mb-3">
    <div class="card-body-c py-3">
        <div class="filter-bar">
            <select id="fCampus"><option value="all">All Campuses</option><option value="boys">Boys</option><option value="girls">Girls</option></select>
            <select id="fYear"><option value="all">All Years</option><option value="first">First Year</option><option value="second">Second Year</option></select>
            <select id="fProgram"><option value="all">All Programs</option></select>
            <select id="fSection"><option value="all">All Sections</option></select>
            <select id="fStatus"><option value="all">All Status</option><option value="active">Active</option><option value="transferred">Transferred</option><option value="promoted">Promoted</option></select>
            <button class="btn btn-sm" id="btnRefresh" style="background:#3498DB;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-sync-alt"></i>
            </button>
        </div>
    </div>
</div>

{{-- Bulk Action Bar --}}
<div class="bulk-bar" id="bulkBar">
    <span style="font-size:13px;font-weight:600;color:#1E3A5F;"><span id="bulkCount">0</span> selected</span>
    <button class="btn btn-sm" id="btnBulkMove" style="background:#F39C12;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-exchange-alt me-1"></i> Move Selected
    </button>
    <button class="btn btn-sm" id="btnBulkExport" style="background:#27AE60;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-file-excel me-1"></i> Export Selected
    </button>
    <button class="btn btn-sm" id="btnBulkDelete" style="background:#E74C3C;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-trash-alt me-1"></i> Delete Selected
    </button>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <table class="table" id="studentsTable" style="width:100%">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>Roll No</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Father</th>
                    <th>Campus</th>
                    <th>Section</th>
                    <th>Program</th>
                    <th>WhatsApp</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header" style="background:#1E3A5F;border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white"><i class="fa-solid fa-file-import me-2"></i>Import Students from Excel</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <a href="{{ route('students.import.template') }}" class="btn btn-sm mb-3" style="background:#3498DB;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-download me-1"></i> Download Template
                </a>
                <div class="photo-dropzone" id="excelDropzone" style="border:2px dashed #dee2e6;border-radius:12px;padding:30px;text-align:center;cursor:pointer;">
                    <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2"></i>
                    <p class="mb-0 text-muted small">Click to select Excel/CSV file (max 5MB)</p>
                    <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" hidden>
                </div>
                <div id="importResultBox" class="mt-3" style="display:none;"></div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnDoImport" style="background:#1E3A5F;color:#fff;border-radius:8px;" disabled>
                    <i class="fa-solid fa-upload me-1"></i> Upload & Import
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Export Column Selection Modal --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header" style="background:#27AE60;border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white"><i class="fa-solid fa-file-excel me-2"></i>Export — Select Columns</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="roll_number" checked> <label>Roll No</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="name" checked> <label>Name</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="father_name" checked> <label>Father Name</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="campus" checked> <label>Campus</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="section" checked> <label>Section</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="program"> <label>Program</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="whatsapp"> <label>WhatsApp</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="ninth_obtained_marks"> <label>9th Marks</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="tenth_obtained_marks"> <label>10th Marks</label></div>
                <div class="form-check mb-2"><input class="form-check-input export-col" type="checkbox" value="status"> <label>Status</label></div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnConfirmExport" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-download me-1"></i> Export Now
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
$(function () {
    let exportSelectedIds = [];

    const table = $('#studentsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("students.list") }}',
            data: function (d) {
                d.campus     = $('#fCampus').val();
                d.year       = $('#fYear').val();
                d.program_id = $('#fProgram').val();
                d.section_id = $('#fSection').val();
                d.status     = $('#fStatus').val();
            },
            dataSrc: function (json) { return json.data.data; }
        },
        columns: [
            { data: 'id', orderable: false, render: id => `<input type="checkbox" class="row-check" value="${id}">` },
            { data: 'roll_number', render: r => `<strong>${r}</strong>` },
            { data: 'photo_url', render: url => `<img src="${url}" class="student-photo-sm">` },
            { data: 'name' },
            { data: 'father_name' },
            { data: 'campus', render: c => c.charAt(0).toUpperCase() + c.slice(1) },
            { data: 'section.code', defaultContent: '—' },
            { data: 'program.code', defaultContent: '—' },
            { data: 'whatsapp' },
            { data: 'status', render: s => `<span class="status-badge status-${s}">${s.charAt(0).toUpperCase()+s.slice(1)}</span>` },
            { data: 'id', orderable: false, render: id => `
                <button class="btn btn-sm action-view" data-id="${id}" title="View" style="color:#3498DB;"><i class="fa-solid fa-eye"></i></button>
                <button class="btn btn-sm action-edit" data-id="${id}" title="Edit" style="color:#F39C12;"><i class="fa-solid fa-edit"></i></button>
                <button class="btn btn-sm action-move" data-id="${id}" title="Move" style="color:#1E3A5F;"><i class="fa-solid fa-exchange-alt"></i></button>
                <a href="/fees/ledger/${id}" class="btn btn-sm" title="Fee Ledger" style="color:#27AE60;"><i class="fa-solid fa-money-bill-wave"></i></a>
                <button class="btn btn-sm action-delete" data-id="${id}" title="Delete" style="color:#E74C3C;"><i class="fa-solid fa-trash-alt"></i></button>
            ` }
        ],
        responsive: true,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        language: {
            emptyTable: '<div class="text-center py-4"><i class="fa fa-inbox fa-3x text-muted"></i><p class="mt-2">No records found</p></div>',
            processing: '<div class="spinner-border text-primary"></div>'
        }
    });

    // ── Filters trigger reload ──────────────────────────────────
    $('#fCampus, #fYear, #fProgram, #fSection, #fStatus').on('change', () => table.ajax.reload());
    $('#btnRefresh').on('click', () => { table.ajax.reload(); toastr.info('List refreshed.'); });

    // ── Checkbox bulk select ─────────────────────────────────────
    $(document).on('change', '.row-check, #checkAll', function () {
        if (this.id === 'checkAll') $('.row-check').prop('checked', this.checked);
        const checked = $('.row-check:checked');
        $('#bulkBar').toggleClass('show', checked.length > 0);
        $('#bulkCount').text(checked.length);
    });

    // ── Row Actions ────────────────────────────────────────────
    $(document).on('click', '.action-view', function () { window.location.href = `/students/${$(this).data('id')}`; });
    $(document).on('click', '.action-edit', function () { window.location.href = `/students/${$(this).data('id')}/edit`; });

    $(document).on('click', '.action-delete', function () {
        const id = $(this).data('id');
        Swal.fire({
            icon: 'warning', title: 'Are you sure?',
            text: 'This will permanently delete this student record. This action cannot be undone.',
            showCancelButton: true, confirmButtonColor: '#E74C3C', cancelButtonColor: '#6C757D',
            confirmButtonText: 'Yes, Delete', cancelButtonText: 'Cancel',
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({ url: `/students/${id}`, method: 'DELETE' }).done(res => {
                    toastr.success(res.message);
                    table.ajax.reload();
                }).fail(() => toastr.error('Could not delete student.'));
            }
        });
    });

    $('#btnBulkDelete').on('click', function () {
        const ids = $('.row-check:checked').map(function(){return this.value;}).get();
        Swal.fire({
            icon: 'warning', title: 'Bulk Delete Confirmation',
            text: `You have selected ${ids.length} record(s). This will delete them all permanently.`,
            showCancelButton: true, confirmButtonColor: '#E74C3C', confirmButtonText: 'Proceed',
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("students.bulk-delete") }}', method: 'POST', data: { ids }
                }).done(res => { toastr.success(res.message); table.ajax.reload(); $('#bulkBar').removeClass('show'); });
            }
        });
    });

    // ── Import Flow ───────────────────────────────────────────
    $('#btnImport').on('click', () => new bootstrap.Modal('#importModal').show());
    $('#excelDropzone').on('click', () => $('#excelFileInput').click());
    $('#excelFileInput').on('change', function () { $('#btnDoImport').prop('disabled', !this.files.length); });

    $('#btnDoImport').on('click', function () {
        const file = $('#excelFileInput')[0].files[0];
        if (!file) return;
        const fd = new FormData(); fd.append('file', file);
        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Importing...');

        $.ajax({ url: '{{ route("students.import.preview") }}', method: 'POST', data: fd, processData: false, contentType: false })
            .done(res => {
                toastr.success(res.message);
                $('#importResultBox').show().html(`
                    <div class="alert alert-success">${res.imported.length} imported successfully.</div>
                    ${res.skipped.length ? `<div class="alert alert-warning">${res.skipped.length} skipped: ${res.skipped.map(s=>`Row ${s.row} (${s.reason})`).join('; ')}</div>` : ''}
                `);
                table.ajax.reload();
            })
            .fail(() => toastr.error('Import failed. Please check your file format.'))
            .always(() => $('#btnDoImport').prop('disabled', false).html('<i class="fa-solid fa-upload me-1"></i> Upload & Import'));
    });

    // ── Export Flow ───────────────────────────────────────────
    $('#btnExport').on('click', function () { exportSelectedIds = []; new bootstrap.Modal('#exportModal').show(); });
    $('#btnBulkExport').on('click', function () {
        exportSelectedIds = $('.row-check:checked').map(function(){return this.value;}).get();
        new bootstrap.Modal('#exportModal').show();
    });

    $('#btnConfirmExport').on('click', function () {
        const columns = $('.export-col:checked').map(function(){return this.value;}).get();
        if (!columns.length) { toastr.warning('Select at least one column.'); return; }

        const form = $('<form>', { method: 'POST', action: '{{ route("students.export") }}' });
        form.append($('<input>', { type: 'hidden', name: '_token', value: $('meta[name=csrf-token]').attr('content') }));
        columns.forEach(c => form.append($('<input>', { type: 'hidden', name: 'columns[]', value: c })));
        exportSelectedIds.forEach(id => form.append($('<input>', { type: 'hidden', name: 'ids[]', value: id })));
        $('body').append(form);
        form.submit();
        bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
    });
});
</script>
@endpush
