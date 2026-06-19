@extends('layouts.app')

@section('title', $student->name)

@section('breadcrumb')
    <a href="{{ route('students.index') }}" class="bc-item">Students</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">{{ $student->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title"><span class="page-icon"><i class="fa-solid fa-id-card"></i></span> {{ $student->name }}</h1>
    <a href="{{ route('students.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card-custom text-center">
            <div class="card-body-c">
                <img src="{{ $student->photo_url }}" class="rounded-circle mb-3" style="width:110px;height:110px;object-fit:cover;">
                <h5 style="color:#1E3A5F;font-weight:700;">{{ $student->name }}</h5>
                <p class="text-muted mb-1">{{ $student->father_name }}</p>
                <span class="status-badge status-{{ $student->status }}" style="padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;background:rgba(39,174,96,0.12);color:#27AE60;">
                    {{ ucfirst($student->status) }}
                </span>
                <hr>
                <div class="text-start" style="font-size:13px;">
                    <p><strong>Roll No:</strong> {{ $student->roll_number }}</p>
                    <p><strong>Campus:</strong> {{ ucfirst($student->campus) }}</p>
                    <p><strong>Year:</strong> {{ ucfirst($student->year) }} Year</p>
                    <p><strong>Section:</strong> {{ $student->section->code ?? '—' }}</p>
                    <p><strong>Program:</strong> {{ $student->program->code ?? '—' }}</p>
                    <p><strong>WhatsApp:</strong> {{ $student->whatsapp }}</p>
                </div>
                <a href="{{ route('students.edit', $student) }}" class="btn btn-sm w-100 mt-2" style="background:#F39C12;color:#fff;border-radius:8px;">
                    <i class="fa-solid fa-edit me-1"></i> Edit Details
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-custom mb-3">
            <div class="card-header-c"><h6 class="card-title-c"><i class="fa-solid fa-graduation-cap text-primary"></i> Previous Academic Record</h6></div>
            <div class="card-body-c">
                <div class="row">
                    <div class="col-md-6">
                        <h6 style="font-size:13px;font-weight:700;color:#1E3A5F;">9th Class</h6>
                        <p style="font-size:13px;">Board: {{ $student->ninth_board ?? '—' }}<br>
                        Marks: {{ $student->ninth_obtained_marks ?? '—' }} / {{ $student->ninth_total_marks ?? '—' }}
                        @if($student->ninth_percent) ({{ $student->ninth_percent }}%) @endif</p>
                    </div>
                    <div class="col-md-6">
                        <h6 style="font-size:13px;font-weight:700;color:#1E3A5F;">10th Class</h6>
                        <p style="font-size:13px;">Board: {{ $student->tenth_board ?? '—' }}<br>
                        Marks: {{ $student->tenth_obtained_marks ?? '—' }} / {{ $student->tenth_total_marks ?? '—' }}
                        @if($student->tenth_percent) ({{ $student->tenth_percent }}%) @endif</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-c"><h6 class="card-title-c"><i class="fa-solid fa-clock-rotate-left text-info"></i> Section History</h6></div>
            <div class="card-body-c">
                @forelse($student->sectionHistory as $history)
                    <div class="activity-item">
                        <div class="activity-avatar"><i class="fa-solid fa-exchange-alt"></i></div>
                        <div>
                            <div class="activity-text">
                                {{ ucfirst(str_replace('_', ' ', $history->action)) }}
                                @if($history->fromSection) from <strong>{{ $history->fromSection->code }}</strong> @endif
                                to <strong>{{ $history->toSection->code }}</strong>
                            </div>
                            <div class="activity-time">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3" style="font-size:13px;">No history yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
