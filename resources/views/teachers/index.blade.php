@extends('layouts.app')

@section('title', 'Teachers')

@section('breadcrumb')
    <span class="bc-current">Teachers</span>
@endsection

@push('styles')
<style>
    .filter-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 16px; }
    .filter-bar select, .filter-bar input { padding: 8px 12px; border: 2px solid #e9ecef;
        border-radius: 8px; font-size: 13px; min-width: 130px; background: #fff; }
    .teacher-photo-sm { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
    .status-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .status-active { background: rgba(39,174,96,0.12); color: #27AE60; }
    .status-inactive { background: rgba(231,76,60,0.12); color: #E74C3C; }
    .bulk-bar { display: none; align-items: center; gap: 10px; padding: 10px 16px;
        background: rgba(30,58,95,0.06); border-radius: 10px; margin-bottom: 12px; }
    .bulk-bar.show { display: flex; }
    table.simple-table thead th { text-align: center; background: #1E3A5F; color: #fff;
        font-weight: 600; padding: 10px; }
    table.simple-table tbody td { padding: 10px; vertical-align: middle; text-align: center; }
    table.simple-table tbody tr:nth-child(even) { background: #F8F9FA; }
    table.simple-table tbody tr:hover { background: #EBF5FB; transition: background .2s ease; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-chalkboard-teacher"></i></span>
        Teachers
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-sm" id="btnAssignSubject" style="background:#3498DB;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-book me-1"></i> Assign Subject
        </button>
        <button class="btn btn-sm" id="btnAssignClass" style="background:#F39C12;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-chalkboard me-1"></i> Assign Class
        </button>
        <a href="{{ route('teachers.create') }}" class="btn btn-sm" style="background:#1E3A5F;color:#fff;border-radius:8px;">
            <i class="fa-solid fa-user-plus me-1"></i> Add Teacher
        </a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-2">
        <div class="stat-card stat-primary"><div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stat-value">{{ $summary['total'] }}</div><div class="stat-label">Total Teachers</div></div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-info"><div class="stat-icon"><i class="fa-solid fa-person"></i></div>
            <div class="stat-value">{{ $summary['male'] }}</div><div class="stat-label">Male</div></div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-warning"><div class="stat-icon"><i class="fa-solid fa-person-dress"></i></div>
            <div class="stat-value">{{ $summary['female'] }}</div><div class="stat-label">Female</div></div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-primary"><div class="stat-icon"><i class="fa-solid fa-building"></i></div>
            <div class="stat-value">{{ $summary['boys_campus'] }}</div><div class="stat-label">Boys Campus</div></div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-danger"><div class="stat-icon"><i class="fa-solid fa-building"></i></div>
            <div class="stat-value">{{ $summary['girls_campus'] }}</div><div class="stat-label">Girls Campus</div></div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-success"><div class="stat-icon"><i class="fa-solid fa-id-badge"></i></div>
            <div class="stat-value">{{ $summary['incharges'] }}</div><div class="stat-label">Class Incharges</div></div>
    </div>
</div>

{{-- Filters --}}
<div class="card-custom mb-3">
    <div class="card-body-c py-3">
        <div class="filter-bar">
            <select id="fGender"><option value="all">All Genders</option><option value="male">Male</option><option value="female">Female</option></select>
            <select id="fCampus"><option value="all">All Campuses</option><option value="boys">Boys</option><option value="girls">Girls</option></select>
            <select id="fStatus"><option value="all">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
            <input type="text" id="searchInput" placeholder="Search teacher..." style="flex:1;min-width:180px;">
            <button class="btn btn-sm" id="btnRefresh" style="background:#3498DB;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-sync-alt"></i>
            </button>
        </div>
    </div>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <div class="table-responsive">
            <table class="simple-table w-100">
                <thead>
                    <tr>
                        <th>Photo</th><th>Name</th><th>Subject(s)</th><th>Phone</th>
                        <th>Classes</th><th>Incharge Of</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="teacherTableBody">
                    <tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <div id="paginationBox" class="d-flex justify-content-center mt-3 gap-2"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;

function loadTeachers(page = 1) {
    currentPage = page;
    $.get('{{ route("teachers.list") }}', {
        gender: $('#fGender').val(), campus: $('#fCampus').val(),
        status: $('#fStatus').val(), search: $('#searchInput').val(), page,
    }, function (res) {
        if (!res.data.length) {
            $('#teacherTableBody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>No Records Found</td></tr>');
            return;
        }
        let html = '';
        res.data.forEach(t => {
            html += `<tr>
                <td><img src="${t.photo_url}" class="teacher-photo-sm"></td>
                <td><a href="/teachers/${t.id}">${t.name}</a></td>
                <td>${t.subjects_count} subject(s)</td>
                <td>${t.whatsapp}</td>
                <td>${t.sections_count} section(s)</td>
                <td>${t.incharge_of}</td>
                <td><span class="status-badge ${t.status ? 'status-active' : 'status-inactive'}">${t.status ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <a href="/teachers/${t.id}/edit" class="btn btn-sm" style="background:#F39C12;color:#fff;" title="Edit"><i class="fa-solid fa-edit"></i></a>
                    <button class="btn btn-sm btn-toggle-status" data-id="${t.id}" data-status="${t.status}" style="background:${t.status ? '#E74C3C' : '#27AE60'};color:#fff;" title="${t.status ? 'Disable' : 'Enable'}">
                        <i class="fa-solid ${t.status ? 'fa-ban' : 'fa-check-circle'}"></i>
                    </button>
                    <button class="btn btn-sm btn-delete-teacher" data-id="${t.id}" data-name="${t.name}" style="background:#E74C3C;color:#fff;" title="Delete"><i class="fa-solid fa-trash-alt"></i></button>
                </td>
            </tr>`;
        });
        $('#teacherTableBody').html(html);
    });
}

$('#fGender, #fCampus, #fStatus').on('change', () => loadTeachers(1));
$('#searchInput').on('input', () => { clearTimeout(window._t); window._t = setTimeout(() => loadTeachers(1), 300); });
$('#btnRefresh').on('click', () => loadTeachers(currentPage));

$(document).on('click', '.btn-toggle-status', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?', text: 'This will change the teacher\'s login access immediately.',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#F39C12',
        confirmButtonText: 'Yes, Proceed', cancelButtonText: 'Cancel',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(`/teachers/${id}/toggle-status`, { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(res => { toastr.success(res.message); loadTeachers(currentPage); });
    });
});

$(document).on('click', '.btn-delete-teacher', function () {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({
        title: 'Are you sure?', text: `This will permanently delete ${name}. This action cannot be undone.`,
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#E74C3C',
        confirmButtonText: 'Yes, Delete', cancelButtonText: 'Cancel',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/teachers/${id}`, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); loadTeachers(currentPage); });
    });
});

loadTeachers();
</script>
@endpush
