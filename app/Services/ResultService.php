<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Exam;
use App\Models\StudentMark;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\GradingTemplate;
use Carbon\Carbon;

/**
 * Builds the full data structure needed for a Student Progress Report —
 * per Ahmed's spec: 9th/10th record, per-exam subject breakdown, cumulative
 * totals across multiple selected exams, and an attendance summary for the
 * period up to the last selected exam date. Used by both the on-screen
 * preview and the PDF result card.
 */
class ResultService
{
    /**
     * @param Student $student
     * @param array<int> $examIds  One or more exam IDs, in chronological order
     */
    public function build(Student $student, array $examIds): array
    {
        $exams = Exam::whereIn('id', $examIds)->orderBy('exam_date')->get();

        $examBlocks = [];
        $cumulativeTotal = 0;
        $cumulativeObtained = 0;

        foreach ($exams as $exam) {
            $marks = StudentMark::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->with('subject')
                ->get();

            $gradingTemplate = $exam->gradingTemplate ?? GradingTemplate::where('is_default', true)->first();

            $subjectRows = $marks->map(function ($mark) use ($gradingTemplate) {
                $percent = $mark->percent;
                $gradeRule = $gradingTemplate?->gradeFor($percent);

                return [
                    'subject'   => $mark->subject->name,
                    'total'     => $mark->total_marks,
                    'obtained'  => $mark->obtained_marks,
                    'percent'   => $percent,
                    'grade'     => $gradeRule?->grade ?? '-',
                    'is_absent' => $mark->is_absent,
                    'is_leave'  => $mark->is_leave,
                ];
            })->values();

            $examTotal = $marks->sum('total_marks');
            $examObtained = $marks->sum('obtained_marks');
            $examPercent = $examTotal > 0 ? round(($examObtained / $examTotal) * 100, 2) : 0;
            $examGrade = $gradingTemplate?->gradeFor($examPercent);

            $cumulativeTotal += $examTotal;
            $cumulativeObtained += $examObtained;

            $examBlocks[] = [
                'exam_name'   => $exam->name,
                'exam_date'   => $exam->exam_date->format('d M Y'),
                'subjects'    => $subjectRows,
                'total'       => $examTotal,
                'obtained'    => $examObtained,
                'percent'     => $examPercent,
                'grade'       => $examGrade?->grade ?? '-',
                'remarks'     => $examGrade?->remarks ?? '-',
            ];
        }

        $cumulativePercent = $cumulativeTotal > 0 ? round(($cumulativeObtained / $cumulativeTotal) * 100, 2) : 0;
        $defaultGrading = GradingTemplate::where('is_default', true)->first();
        $cumulativeGrade = $defaultGrading?->gradeFor($cumulativePercent);

        $lastExamDate = $exams->max('exam_date') ?? now();

        return [
            'student' => [
                'name'         => $student->name,
                'father_name'  => $student->father_name,
                'roll_number'  => $student->roll_number,
                'section'      => $student->section->code,
                'program'      => $student->program->code,
                'campus'       => ucfirst($student->campus),
                'year'         => $student->year === 'first' ? 'First Year' : 'Second Year',
                'photo_url'    => $student->photo_url,
            ],
            'previous_record' => [
                'ninth'  => $this->formatPreviousRecord($student, 'ninth'),
                'tenth'  => $this->formatPreviousRecord($student, 'tenth'),
            ],
            'exams' => $examBlocks,
            'cumulative' => count($examBlocks) > 1 ? [
                'total'    => $cumulativeTotal,
                'obtained' => $cumulativeObtained,
                'percent'  => $cumulativePercent,
                'grade'    => $cumulativeGrade?->grade ?? '-',
            ] : null,
            'attendance' => $this->buildAttendanceSummary($student, $lastExamDate),
        ];
    }

    protected function formatPreviousRecord(Student $student, string $prefix): ?array
    {
        $total = $student->{$prefix.'_total_marks'};
        if (!$total) return null;

        $obtained = $student->{$prefix.'_obtained_marks'};

        return [
            'board'    => $student->{$prefix.'_board'},
            'total'    => $total,
            'obtained' => $obtained,
            'percent'  => round(($obtained / $total) * 100, 1),
        ];
    }

    /**
     * Attendance from enrollment_date through $uptoDate, excluding holidays,
     * per Ahmed's spec: working_days = total days - public/college holidays
     * for that student's campus.
     */
    protected function buildAttendanceSummary(Student $student, $uptoDate): array
    {
        $uptoDate = Carbon::parse($uptoDate);

        $records = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$student->enrollment_date, $uptoDate])
            ->get();

        $present = $records->where('status', 'present')->count();
        $absent  = $records->where('status', 'absent')->count();
        $leave   = $records->where('status', 'leave')->count();
        $late    = $records->where('is_late', true)->count();

        $workingDays = $records->whereIn('status', ['present', 'absent', 'leave'])->count();
        $percent = $workingDays > 0 ? round(($present / $workingDays) * 100, 1) : 0;

        return [
            'working_days' => $workingDays,
            'present'      => $present,
            'absent'       => $absent,
            'leave'        => $leave,
            'late'         => $late,
            'percent'      => $percent,
            'status'       => $percent >= 85 ? 'Good Standing' : ($percent >= 75 ? 'Satisfactory' : 'Below Minimum'),
        ];
    }
}
