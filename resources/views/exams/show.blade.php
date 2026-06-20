@extends('layouts.app')

@section('title', $exam->name)

@section('breadcrumb')
    <a href="{{ route('exams.index') }}" class="bc-item">Examinations</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">{{ $exam->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-file-alt"></i></span>
        {{ $exam->name }}
    </h1>
    <a href="{{ route('exams.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card-custom">
            <div class="card-body-c">
                <h6 style="color:#1E3A5F;">Exam Details</h6>
                <table class="table table-sm mt-2">
                    <tr><td class="text-muted">Type</td><td>{{ str_replace('_',' ',ucfirst($exam->type)) }}</td></tr>
                    <tr><td class="text-muted">Date</td><td>{{ $exam->exam_date->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Campus</td><td>{{ ucfirst($exam->campus_scope) }}</td></tr>
                    <tr><td class="text-muted">Year Scope</td><td>{{ ucfirst($exam->year_scope) }}</td></tr>
                    <tr><td class="text-muted">Sections</td><td>{{ $exam->sections->count() }}</td></tr>
                    <tr><td class="text-muted">Grading</td><td>{{ $exam->gradingTemplate?->name ?? 'Default' }}</td></tr>
                    <tr><td class="text-muted">Due Date</td>
                        <td>{{ $exam->effective_due_date?->format('d M Y, h:i A') ?? '—' }}</td></tr>
                </table>

                @can('create exam')
                @if($exam->marks_due_date)
                <button class="btn btn-sm w-100" id="btnExtend" style="background:#F39C12;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-clock me-1"></i> Extend Due Date
                </button>
                @endif
                @endcan
            </div>
        </div>

        @if($exam->isPastDue() && $incompleteTeachers->count())
        <div class="card-custom mt-3">
            <div class="card-body-c">
                <h6 class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Incomplete Marks Entry</h6>
                <ul class="list-unstyled small mt-2">
                    @foreach($incompleteTeachers as $t)
                        <li class="mb-1">
                            <i class="fa-solid fa-user text-muted"></i>
                            {{ $t->teacher->name }} — {{ $t->section->code }} ({{ $t->subject->name }})
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card-custom">
            <div class="card-body-c">
                <h6 style="color:#1E3A5F;">Configured Subjects & Marks</h6>
                <table class="simple-table w-100 mt-2">
                    <thead><tr><th>Program</th><th>Year</th><th>Subject</th><th>Total Marks</th></tr></thead>
                    <tbody>
                        @foreach($exam->subjectMarks as $sm)
                        <tr>
                            <td>{{ $sm->program->code }}</td>
                            <td>{{ ucfirst($sm->year) }}</td>
                            <td>{{ $sm->subject->name }}</td>
                            <td>{{ $sm->total_marks }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-custom mt-3">
            <div class="card-body-c">
                <h6 style="color:#1E3A5F;">Affected Sections</h6>
                @foreach($exam->sections as $section)
                    <span class="badge" style="background:rgba(30,58,95,.1);color:#1E3A5F;padding:6px 12px;margin:3px;">
                        {{ $section->code }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnExtend').on('click', function () {
    Swal.fire({
        title: 'Extend Due Date',
        html: '<input type="datetime-local" id="swalNewDate" class="swal2-input">',
        confirmButtonColor: '#F39C12', confirmButtonText: 'Extend', showCancelButton: true,
        preConfirm: () => document.getElementById('swalNewDate').value,
    }).then(r => {
        if (!r.isConfirmed || !r.value) return;
        $.post('{{ route("exams.extend-due-date", $exam) }}', {
            _token: $('meta[name="csrf-token"]').attr('content'), new_due_date: r.value,
        }).done(res => { toastr.success(res.message); location.reload(); })
          .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed.'));
    });
});
</script>
@endpush
