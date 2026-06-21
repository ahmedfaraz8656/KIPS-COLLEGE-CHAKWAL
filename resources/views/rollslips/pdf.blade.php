<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 20px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #000; }

    .slip-pair { page-break-after: always; }
    .slip-pair:last-child { page-break-after: auto; }

    .slip { border: 2px solid #000; width: 100%; margin-bottom: 14px; }
    .slip-header { text-align: center; padding: 8px; border-bottom: 2px solid #000; }
    .slip-header .college { font-size: 15px; font-weight: bold; }
    .slip-header .addr { font-size: 9px; }
    .slip-title { text-align: center; font-weight: bold; font-size: 12px; padding: 5px; border-bottom: 2px solid #000; background: #f5f5f5; }
    .slip-meta { font-size: 10px; text-align: center; padding: 3px; border-bottom: 2px solid #000; }
    .slip-body { padding: 8px 14px; }
    .slip-body table { width: 100%; }
    .slip-body td { padding: 4px 0; font-size: 11px; }
    .slip-body .label { font-weight: bold; width: 28%; }
    .slip-footer { display: table; width: 100%; border-top: 2px solid #000; padding: 10px 14px; font-size: 10px; }
    .slip-footer .left { display: table-cell; width: 60%; }
    .slip-footer .right { display: table-cell; width: 40%; text-align: right; }
    .cut-line { border-top: 1px dashed #999; text-align: center; font-size: 8px; color: #999; margin: 4px 0; }
</style>
</head>
<body>

@foreach($students->chunk(2) as $pair)
<div class="slip-pair">
    @foreach($pair as $student)
    <div class="slip">
        <div class="slip-header">
            <div class="college">KIPS COLLEGE CHAKWAL</div>
            <div class="addr">Chakwal, Punjab, Pakistan</div>
        </div>
        <div class="slip-title">ADMIT CARD / ROLL NUMBER SLIP</div>
        <div class="slip-meta">Exam: {{ $exam->name }} &nbsp;|&nbsp; Date: {{ $exam->exam_date->format('d M Y') }}</div>
        <div class="slip-body">
            <table>
                <tr><td class="label">Roll No:</td><td>{{ $student->roll_number }}</td></tr>
                <tr><td class="label">Name:</td><td>{{ $student->name }}</td></tr>
                <tr><td class="label">Father:</td><td>{{ $student->father_name }}</td></tr>
                <tr><td class="label">Section:</td><td>{{ $student->section->code }} ({{ $student->program->code }})</td></tr>
                <tr><td class="label">Campus:</td><td>{{ ucfirst($student->campus) }} | Year: {{ $student->year === 'first' ? 'First' : 'Second' }}</td></tr>
            </table>
        </div>
        <div class="slip-footer">
            <div class="left">Signature: ______________</div>
            <div class="right">Stamp: &#9633;</div>
        </div>
    </div>
    @endforeach
    @if($pair->count() == 2)<div class="cut-line">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</div>@endif
</div>
@endforeach

</body>
</html>
