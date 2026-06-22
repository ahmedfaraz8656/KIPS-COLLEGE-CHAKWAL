@extends('layouts.app')

@section('title', $teacher->name.' - Timetable')

@section('breadcrumb')
    <a href="{{ route('teachers.show', $teacher) }}" class="bc-item">{{ $teacher->name }}</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">Timetable</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-table-cells"></i></span>
        {{ $teacher->name }} — Weekly Schedule
    </h1>
    <button class="btn btn-sm" style="background:#E74C3C;color:#fff;border-radius:8px;"><i class="fa-solid fa-file-pdf me-1"></i> Export PDF</button>
</div>

<div class="card-custom">
    <div class="card-body-c">
        <table class="simple-table w-100">
            <thead><tr><th>Day</th><th>Period</th><th>Time</th><th>Section</th><th>Subject</th></tr></thead>
            <tbody>
                @forelse($entries->sortBy(fn($e) => array_search($e->day, ['MON','TUE','WED','THU','FRI','SAT'])) as $e)
                <tr>
                    <td>{{ $e->day }}</td>
                    <td>{{ $e->periodSlot->period_number }}</td>
                    <td>{{ $e->periodSlot->start_time->format('h:i A') }}</td>
                    <td>{{ $e->section->code }}</td>
                    <td>{{ $e->subject->name }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-muted py-4">No periods assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
