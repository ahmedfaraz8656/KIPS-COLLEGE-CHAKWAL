@extends('layouts.app')

@section('title', 'Promote to Second Year')

@section('breadcrumb')
    <a href="{{ route('students.index') }}" class="bc-item">Students</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Promotion</span>
@endsection

@push('styles')
<style>
    .promo-row { display: flex; align-items: center; gap: 14px; padding: 14px 16px;
        border: 1px solid #f0f0f0; border-radius: 12px; margin-bottom: 10px; background: #fff; }
    .promo-row:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
    .promo-section-pill { background: rgba(30,58,95,0.08); color: #1E3A5F; font-weight: 700;
        padding: 6px 14px; border-radius: 8px; font-size: 13px; min-width: 90px; text-align: center; }
    .promo-arrow { color: #27AE60; font-size: 18px; }
    .promo-count { font-size: 12px; color: #6C757D; }
    select.promo-target { padding: 6px 10px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-level-up-alt"></i></span>
        Promote to Second Year
    </h1>
    <a href="{{ route('students.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card-custom mb-3">
    <div class="card-body-c">
        <p class="small text-muted mb-0">
            <i class="fa-solid fa-circle-info text-info"></i>
            Default section mapping is shown below. You can change the target section per row
            before promoting. Only First Year students with <b>Active</b> status are included.
        </p>
    </div>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="small fw-600 text-muted">
                <input type="checkbox" id="selectAllRows"> Select All Sections
            </span>
            <button class="btn btn-sm" id="btnPromote" disabled
                style="background:#27AE60;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-level-up-alt me-1"></i>
                <span id="btnPromoteText">Promote Selected</span>
            </button>
        </div>

        @forelse($mapping as $row)
        <div class="promo-row" data-from="{{ $row['from_section']->id }}">
            <input type="checkbox" class="promo-check"
                   {{ $row['student_count'] == 0 ? 'disabled' : '' }}
                   data-from="{{ $row['from_section']->id }}">

            <span class="promo-section-pill">{{ $row['from_section']->code }}</span>
            <span class="promo-arrow"><i class="fa-solid fa-arrow-right"></i></span>

            <select class="promo-target" data-from="{{ $row['from_section']->id }}"></select>

            <span class="promo-count ms-auto">
                <i class="fa-solid fa-users"></i> {{ $row['student_count'] }} student(s)
            </span>
        </div>
        @empty
        <div class="text-center text-muted py-4">No First Year sections found.</div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
const mappingData = @json(collect($mapping)->map(fn($r) => [
    'from_id' => $r['from_section']->id,
    'from_code' => $r['from_section']->code,
    'campus' => $r['from_section']->campus,
    'to_id' => $r['to_section']?->id,
    'to_code' => $r['to_section']?->code,
]));

// Populate each "target" dropdown with Second Year sections of the SAME campus only
$.get('{{ route("students.transfer.sections") }}', { campus: 'boys', year: 'second' }, function (res) {
    populateTargets('boys', res.data);
});
$.get('{{ route("students.transfer.sections") }}', { campus: 'girls', year: 'second' }, function (res) {
    populateTargets('girls', res.data);
});

function populateTargets(campus, sections) {
    mappingData.filter(m => m.campus === campus).forEach(m => {
        const $sel = $(`.promo-target[data-from="${m.from_id}"]`);
        $sel.empty();
        sections.forEach(s => {
            const selected = (s.code === m.to_code) ? 'selected' : '';
            $sel.append(`<option value="${s.id}" ${selected}>${s.code} (${s.count})</option>`);
        });
    });
}

$('#selectAllRows').on('change', function () {
    $('.promo-check:not(:disabled)').prop('checked', this.checked);
    checkPromoteReady();
});

$(document).on('change', '.promo-check', checkPromoteReady);

function checkPromoteReady() {
    $('#btnPromote').prop('disabled', $('.promo-check:checked').length === 0);
}

$('#btnPromote').on('click', function () {
    const mappings = [];
    $('.promo-check:checked').each(function () {
        const fromId = $(this).data('from');
        const toId = $(`.promo-target[data-from="${fromId}"]`).val();
        if (toId) mappings.push({ from_section_id: fromId, to_section_id: toId });
    });

    if (!mappings.length) return;

    Swal.fire({
        title: 'Promote Students?',
        html: `You are about to promote students from <b>${mappings.length}</b> section(s) to Second Year.<br>Verify the section mappings before proceeding.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#27AE60',
        confirmButtonText: 'Yes, Promote',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (!result.isConfirmed) return;

        $('#btnPromote').prop('disabled', true);
        $('#btnPromoteText').html('<span class="spinner-border spinner-border-sm"></span> Promoting...');

        $.post('{{ route("students.promote.execute") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            mappings: mappings,
        }).done(function (res) {
            Swal.fire('Done!', res.message, 'success').then(() => location.reload());
        }).fail(function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Promotion failed.');
            $('#btnPromoteText').text('Promote Selected');
            checkPromoteReady();
        });
    });
});
</script>
@endpush
