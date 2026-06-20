<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Setting;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Determines if a marking time counts as Late, based on the
     * CURRENT dynamic setting. Admin/Principal/MD can change this anytime;
     * the change is NOT retroactive to already-saved attendance records.
     */
    public function isLate(string $time): bool
    {
        $cutoff = Setting::get('attendance_late_time', '08:30');
        return $time > $cutoff;
    }

    /**
     * Full attendance statistics for one student over a date range.
     * Working days = total calendar days in range − Public/College holidays
     * for that student's campus. Used by Reports, Result Cards, and the
     * Parent/Student dashboards.
     */
    public function statsFor(int $studentId, string $campus, string $from, string $to): array
    {
        $records = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$from, $to])
            ->get();

        $present = $records->where('status', 'present')->count();
        $absent  = $records->where('status', 'absent')->count();
        $leave   = $records->where('status', 'leave')->count();
        $late    = $records->where('is_late', true)->count();

        $holidayDays = Holiday::where('date', '>=', $from)->where('date', '<=', $to)
            ->where(function ($q) use ($campus) {
                $q->where('campus_scope', 'both')->orWhere('campus_scope', $campus);
            })->count();

        $totalCalendarDays = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        $workingDays = max(0, $totalCalendarDays - $holidayDays);

        $percent = $workingDays > 0 ? round(($present / $workingDays) * 100, 1) : 0;

        $minThreshold = (float) Setting::get('attendance_min_percent', 75);
        $warnThreshold = (float) Setting::get('attendance_warning_percent', 80);

        $status = $percent >= $warnThreshold
            ? 'good'
            : ($percent >= $minThreshold ? 'warning' : 'low');

        return [
            'working_days' => $workingDays,
            'present'      => $present,
            'absent'       => $absent,
            'leave'        => $leave,
            'late'         => $late,
            'holidays'     => $holidayDays,
            'percent'      => $percent,
            'status'       => $status, // good / warning / low
        ];
    }

    /** Is the given date+campus a holiday (excluded from working days)? */
    public function isHoliday(string $date, string $campus): bool
    {
        return Holiday::isHoliday($date, $campus);
    }
}
