<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 18px 24px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #000; }

    .sheet { page-break-after: always; }
    .sheet:last-child { page-break-after: auto; }

    .header-table { width: 100%; border-collapse: collapse; border: 2px solid #000; margin-bottom: 0; }
    .header-table td { border: none; padding: 6px 10px; vertical-align: middle; }
    .college-name { font-size: 18px; font-weight: bold; text-align: center; }
    .college-sub { font-size: 10px; text-align: center; color: #333; }
    .logo-cell { width: 60px; text-align: center; font-size: 9px; }

    .section-title-bar { border: 2px solid #000; border-top: none; text-align: center;
        font-weight: bold; font-size: 13px; padding: 5px; letter-spacing: 1px; }

    .info-table { width: 100%; border-collapse: collapse; border: 2px solid #000; border-top: none; }
    .info-table td { border: none; padding: 4px 10px; font-size: 11px; }
    .info-table .label { font-weight: bold; width: 18%; }

    .prev-record-bar { border: 2px solid #000; border-top: none; padding: 5px 10px;
        font-weight: bold; font-size: 11px; background: #fff; }
    .prev-table { width: 100%; border-collapse: collapse; border: 2px solid #000; border-top: none; }
    .prev-table td { border: none; padding: 4px 10px; font-size: 10.5px; }

    .exam-bar { border: 2px solid #000; border-top: none; padding: 5px 10px;
        font-weight: bold; font-size: 12px; }

    .marks-table { width: 100%; border-collapse: collapse; border: 2px solid #000; border-top: none; }
    .marks-table th { border: 1px solid #000; padding: 5px; font-size: 10.5px; text-align: center; background: #f0f0f0; }
    .marks-table td { border: 1px solid #000; padding: 5px; font-size: 10.5px; text-align: center; }
    .marks-table .subj-name { text-align: left; }
    .marks-table .total-row { font-weight: bold; background: #f5f5f5; }
    .absent-cell { font-weight: bold; }

    .remarks-row { border: 2px solid #000; border-top: none; padding: 5px 10px; font-size: 10.5px; }

    .cumulative-bar { border: 2px solid #000; border-top: none; padding: 6px 10px;
        font-weight: bold; font-size: 11.5px; background: #fafafa; }

    .att-title-bar { border: 2px solid #000; border-top: none; padding: 5px 10px;
        font-weight: bold; font-size: 12px; }
    .att-table { width: 100%; border-collapse: collapse; border: 2px solid #000; border-top: none; }
    .att-table th { border: 1px solid #000; padding: 5px; font-size: 10px; text-align: center; background: #f0f0f0; }
    .att-table td { border: 1px solid #000; padding: 5px; font-size: 10.5px; text-align: center; }

    .status-bar { border: 2px solid #000; border-top: none; padding: 5px 10px; font-size: 11px; font-weight: bold; }

    .sign-table { width: 100%; border-collapse: collapse; border: 2px solid #000; border-top: none; }
    .sign-table td { border: none; padding: 16px 10px 8px; font-size: 10.5px; width: 33%; }
    .sign-line { border-top: 1px solid #000; padding-top: 3px; display: block; }
</style>
</head>
<body>

@foreach($results as $r)
<div class="sheet">

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">[LOGO]</td>
            <td>
                <div class="college-name">KIPS COLLEGE CHAKWAL</div>
                <div class="college-sub">Chakwal, Punjab, Pakistan</div>
            </td>
            <td class="logo-cell"></td>
        </tr>
    </table>
    <div class="section-title-bar">STUDENT PROGRESS REPORT</div>

    {{-- STUDENT INFO --}}
    <table class="info-table">
        <tr>
            <td class="label">Name:</td><td>{{ $r['student']['name'] }}</td>
            <td class="label">Roll No:</td><td>{{ $r['student']['roll_number'] }}</td>
        </tr>
        <tr>
            <td class="label">Father:</td><td>{{ $r['student']['father_name'] }}</td>
            <td class="label">Class:</td><td>{{ $r['student']['year'] }}</td>
        </tr>
        <tr>
            <td class="label">Section:</td><td>{{ $r['student']['section'] }}</td>
            <td class="label">Program:</td><td>{{ $r['student']['program'] }} ({{ $r['student']['campus'] }})</td>
        </tr>
    </table>

    {{-- PREVIOUS ACADEMIC RECORD --}}
    @if($r['previous_record']['ninth'] || $r['previous_record']['tenth'])
    <div class="prev-record-bar">PREVIOUS ACADEMIC RECORD</div>
    <table class="prev-table">
        @if($r['previous_record']['ninth'])
        <tr>
            <td style="width:8%"><b>9th:</b></td>
            <td>Board: {{ $r['previous_record']['ninth']['board'] ?? '-' }}</td>
            <td>Obtained: {{ $r['previous_record']['ninth']['obtained'] }}/{{ $r['previous_record']['ninth']['total'] }}</td>
            <td>Percentage: {{ $r['previous_record']['ninth']['percent'] }}%</td>
        </tr>
        @endif
        @if($r['previous_record']['tenth'])
        <tr>
            <td><b>10th:</b></td>
            <td>Board: {{ $r['previous_record']['tenth']['board'] ?? '-' }}</td>
            <td>Obtained: {{ $r['previous_record']['tenth']['obtained'] }}/{{ $r['previous_record']['tenth']['total'] }}</td>
            <td>Percentage: {{ $r['previous_record']['tenth']['percent'] }}%</td>
        </tr>
        @endif
    </table>
    @endif

    {{-- PER-EXAM BLOCKS --}}
    @foreach($r['exams'] as $exam)
        <div class="exam-bar">{{ strtoupper($exam['exam_name']) }} — Date: {{ $exam['exam_date'] }}</div>
        <table class="marks-table">
            <thead>
                <tr><th style="width:34%">Subject</th><th>Total</th><th>Obtained</th><th>%</th><th>Grade</th></tr>
            </thead>
            <tbody>
                @foreach($exam['subjects'] as $subj)
                <tr>
                    <td class="subj-name">{{ $subj['subject'] }}</td>
                    <td>{{ $subj['total'] }}</td>
                    <td class="{{ $subj['is_absent'] || $subj['is_leave'] ? 'absent-cell' : '' }}">
                        {{ $subj['is_absent'] ? 'AB' : ($subj['is_leave'] ? 'L' : $subj['obtained']) }}
                    </td>
                    <td>{{ $subj['is_absent'] || $subj['is_leave'] ? '0%' : $subj['percent'].'%' }}</td>
                    <td>{{ $subj['grade'] }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td>{{ $exam['total'] }}</td>
                    <td>{{ $exam['obtained'] }}</td>
                    <td>{{ $exam['percent'] }}%</td>
                    <td>{{ $exam['grade'] }}</td>
                </tr>
            </tbody>
        </table>
        <div class="remarks-row">Remarks: {{ $exam['remarks'] }}</div>
    @endforeach

    {{-- CUMULATIVE --}}
    @if($r['cumulative'])
    <div class="cumulative-bar">
        CUMULATIVE ({{ count($r['exams']) }} EXAMS COMBINED):
        &nbsp; Total: {{ $r['cumulative']['total'] }} &nbsp;|&nbsp;
        Obtained: {{ $r['cumulative']['obtained'] }} &nbsp;|&nbsp;
        %: {{ $r['cumulative']['percent'] }}% &nbsp;|&nbsp;
        Grade: {{ $r['cumulative']['grade'] }}
    </div>
    @endif

    {{-- ATTENDANCE --}}
    <div class="att-title-bar">ATTENDANCE REPORT (Up to {{ $r['exams'][count($r['exams'])-1]['exam_date'] ?? '' }})</div>
    <table class="att-table">
        <thead>
            <tr><th>Working Days</th><th>Present</th><th>Absent</th><th>Leave</th><th>Late</th><th>%</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $r['attendance']['working_days'] }}</td>
                <td>{{ $r['attendance']['present'] }}</td>
                <td>{{ $r['attendance']['absent'] }}</td>
                <td>{{ $r['attendance']['leave'] }}</td>
                <td>{{ $r['attendance']['late'] }}</td>
                <td>{{ $r['attendance']['percent'] }}%</td>
            </tr>
        </tbody>
    </table>
    <div class="status-bar">
        Attendance Status:
        {{ $r['attendance']['status'] === 'Good Standing' ? '[OK]' : '[!]' }} {{ $r['attendance']['status'] }}
    </div>

    {{-- SIGNATURES --}}
    <table class="sign-table">
        <tr>
            <td><span class="sign-line">Class Incharge</span></td>
            <td><span class="sign-line">Date</span></td>
            <td><span class="sign-line">Principal Signature / Stamp</span></td>
        </tr>
    </table>
</div>
@endforeach

</body>
</html>
