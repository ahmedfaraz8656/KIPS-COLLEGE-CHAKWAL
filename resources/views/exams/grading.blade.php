@extends('layouts.app')

@section('title', 'Grading')

@section('breadcrumb')
    <span class="bc-current">Grading</span>
@endsection

@push('styles')
<style>
    .template-card { background: #fff; border: 1px solid #f0f0f0; border-radius: 14px;
        padding: 18px; margin-bottom: 14px; }
    .template-card.is-default { border-color: #27AE60; box-shadow: 0 0 0 1px rgba(39,174,96,.2); }
    .default-tag { background: rgba(39,174,96,.1); color: #27AE60; font-size: 10px;
        font-weight: 700; padding: 2px 10px; border-radius: 10px; text-transform: uppercase; }
    table.grade-table th { background: #1E3A5F; color: #fff; padding: 8px; font-size: 12px; text-align: center; }
    table.grade-table td { padding: 6px; text-align: center; vertical-align: middle; }
    table.grade-table input { width: 100%; border: 2px solid #e9ecef; border-radius: 6px; padding: 5px; text-align: center; font-size: 13px; }
    .grade-pill { display: inline-flex; width: 32px; height: 32px; align-items: center; justify-content: center;
        border-radius: 8px; font-weight: 700; font-size: 12px; color: #fff; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-star-half-stroke"></i></span>
        Grading Scale
    </h1>
    <button class="btn btn-sm" id="btnNewTemplate" style="background:#1E3A5F;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-plus me-1"></i> New Template
    </button>
</div>

@foreach($templates as $template)
<div class="template-card {{ $template->is_default ? 'is-default' : '' }}" data-template="{{ $template->id }}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0" style="color:#1E3A5F;font-weight:700;">{{ $template->name }}
                @if($template->is_default)<span class="default-tag ms-2">Default</span>@endif
            </h6>
            <span class="text-muted small">Minimum Pass: {{ $template->min_pass_percent }}%</span>
        </div>
        <div class="d-flex gap-2">
            @unless($template->is_default)
            <button class="btn btn-sm btn-set-default" data-id="{{ $template->id }}" style="background:#27AE60;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-check"></i> Set Default
            </button>
            @endunless
            <button class="btn btn-sm btn-edit-template" data-id="{{ $template->id }}" style="background:#F39C12;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-edit"></i>
            </button>
            @unless($template->is_default)
            <button class="btn btn-sm btn-delete-template" data-id="{{ $template->id }}" data-name="{{ $template->name }}" style="background:#E74C3C;color:#fff;border-radius:8px;">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
            @endunless
        </div>
    </div>

    <table class="grade-table w-100">
        <thead><tr><th>From %</th><th>To %</th><th>Grade</th><th>Remarks</th></tr></thead>
        <tbody>
            @foreach($template->rules->sortByDesc('from_percent') as $rule)
            <tr>
                <td>{{ $rule->from_percent }}</td>
                <td>{{ $rule->to_percent }}</td>
                <td><span class="grade-pill" style="background:{{ ['A+'=>'#27AE60','A'=>'#2ECC71','B'=>'#3498DB','C'=>'#F39C12','D'=>'#e67e22','E'=>'#95a5a6','F'=>'#E74C3C'][$rule->grade] ?? '#6C757D' }};">{{ $rule->grade }}</span></td>
                <td>{{ $rule->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

{{-- BUILD/EDIT MODAL --}}
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#1E3A5F;border-radius:14px 14px 0 0;">
                <h6 class="modal-title text-white" id="modalTitle">New Grading Template</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editTemplateId">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-600">Template Name</label>
                        <input type="text" id="templateName" class="form-control" placeholder="e.g. Pre-Board Grading">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-600">Min Pass %</label>
                        <input type="number" id="minPassPercent" class="form-control" value="33">
                    </div>
                </div>

                <table class="grade-table w-100" id="ruleBuilderTable">
                    <thead><tr><th>From %</th><th>To %</th><th>Grade</th><th>Remarks</th><th></th></tr></thead>
                    <tbody id="ruleBuilderBody"></tbody>
                </table>
                <button type="button" class="btn btn-sm mt-2" id="btnAddRow" style="background:#3498DB;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-plus me-1"></i> Add Row
                </button>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="isDefaultCheck">
                    <label class="form-check-label small">Set as default template</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" data-bs-dismiss="modal" style="background:#6C757D;color:#fff;border-radius:8px;">Cancel</button>
                <button class="btn btn-sm" id="btnSaveTemplate" style="background:#27AE60;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-save me-1"></i> <span id="btnSaveTemplateText">Save Template</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const templateModal = new bootstrap.Modal(document.getElementById('templateModal'));

function addRuleRow(from = '', to = '', grade = '', remarks = '') {
    const row = `<tr>
        <td><input type="number" class="rb-from" value="${from}" min="0" max="100"></td>
        <td><input type="number" class="rb-to" value="${to}" min="0" max="100"></td>
        <td><input type="text" class="rb-grade" value="${grade}" maxlength="4"></td>
        <td><input type="text" class="rb-remarks" value="${remarks}"></td>
        <td><button type="button" class="btn btn-sm btn-remove-row" style="background:#E74C3C;color:#fff;"><i class="fa-solid fa-times"></i></button></td>
    </tr>`;
    $('#ruleBuilderBody').append(row);
}

$('#btnAddRow').on('click', () => addRuleRow());
$(document).on('click', '.btn-remove-row', function () { $(this).closest('tr').remove(); });

$('#btnNewTemplate').on('click', function () {
    $('#modalTitle').text('New Grading Template');
    $('#editTemplateId').val('');
    $('#templateName').val(''); $('#minPassPercent').val(33); $('#isDefaultCheck').prop('checked', false);
    $('#ruleBuilderBody').empty();
    [[90,100,'A+','Outstanding'],[80,89.99,'A','Excellent'],[70,79.99,'B','Very Good'],
     [60,69.99,'C','Good'],[50,59.99,'D','Satisfactory'],[33,49.99,'E','Pass'],[0,32.99,'F','Fail']]
        .forEach(r => addRuleRow(...r));
    templateModal.show();
});

$(document).on('click', '.btn-edit-template', function () {
    const card = $(this).closest('.template-card');
    $('#modalTitle').text('Edit Grading Template');
    $('#editTemplateId').val($(this).data('id'));
    $('#templateName').val(card.find('h6').text().trim().replace('Default',''));
    $('#ruleBuilderBody').empty();
    card.find('tbody tr').each(function () {
        const tds = $(this).find('td');
        addRuleRow($(tds[0]).text(), $(tds[1]).text(), $(tds[2]).text().trim(), $(tds[3]).text());
    });
    templateModal.show();
});

$('#btnSaveTemplate').on('click', function () {
    const rules = [];
    $('#ruleBuilderBody tr').each(function () {
        rules.push({
            from_percent: $(this).find('.rb-from').val(),
            to_percent: $(this).find('.rb-to').val(),
            grade: $(this).find('.rb-grade').val(),
            remarks: $(this).find('.rb-remarks').val(),
        });
    });

    const id = $('#editTemplateId').val();
    const url = id ? `/exams/grading/${id}` : '{{ route("exams.grading.store") }}';
    const method = id ? 'PUT' : 'POST';

    $('#btnSaveTemplate').prop('disabled', true);
    $('#btnSaveTemplateText').html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.ajax({
        url, method, data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#templateName').val(), min_pass_percent: $('#minPassPercent').val(),
            is_default: $('#isDefaultCheck').is(':checked'), rules,
        }
    }).done(res => { toastr.success(res.message); location.reload(); })
      .fail(xhr => {
          toastr.error(xhr.responseJSON?.message || 'Failed to save.');
          $('#btnSaveTemplate').prop('disabled', false);
          $('#btnSaveTemplateText').text('Save Template');
      });
});

$(document).on('click', '.btn-set-default', function () {
    $.post(`/exams/grading/${$(this).data('id')}/set-default`, { _token: $('meta[name="csrf-token"]').attr('content') })
        .done(res => { toastr.success(res.message); location.reload(); });
});

$(document).on('click', '.btn-delete-template', function () {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({ title: 'Are you sure?', text: `Delete "${name}"? This cannot be undone.`, icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#E74C3C', confirmButtonText: 'Yes, Delete' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `/exams/grading/${id}`, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
            .done(res => { toastr.success(res.message); location.reload(); });
    });
});
</script>
@endpush
