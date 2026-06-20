<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService) {}

    // ─── MARK ATTENDANCE PAGE ────────────────────────────────────
    public function markPage(Request $request)
    {
        $user = auth()->user();

        // Class Incharges only see their own assigned section(s);
        // Admin/Principal/MD can mark any section.
        if ($user->hasRole('Class Incharge') && !$user->hasAnyRole(['Admin', 'Principal', 'Managing Director'])) {
            $sections = Section::whereHas('incharge', fn ($q) => $q->where('teacher_id', $user->teacher?->id))->get();
        } else {
            $sections = Section::where('status', true)->orderBy('code')->get();
        }

        return view('attendance.mark', compact('sections'));
    }

    // ─── AJAX: LOAD STUDENTS FOR A SECTION + DATE ───────────────
    public function loadStudents(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date|before_or_equal:today', // cannot mark future dates
        ]);

        $section = Section::find($request->section_id);
        $isHoliday = $this->attendanceService->isHoliday($request->date, $section->campus);

        $students = Student::where('section_id', $section->id)
            ->where('status', 'active')
            ->orderBy('roll_number')
            ->get(['id', 'roll_number', 'name', 'photo']);

        $existing = Attendance::where('section_id', $section->id)
            ->where('date', $request->date)
            ->get()
            ->keyBy('student_id');

        $data = $students->map(function ($s) use ($existing) {
            $att = $existing->get($s->id);
            return [
                'id' => $s->id,
                'roll_number' => $s->roll_number,
                'name' => $s->name,
                'photo_url' => $s->photo_url,
                'status' => $att?->status ?? null,
                'is_late' => $att?->is_late ?? false,
                'remarks' => $att?->remarks,
            ];
        });

        return response()->json([
            'success' => true,
            'is_holiday' => $isHoliday,
            'late_cutoff' => \App\Models\Setting::get('attendance_late_time', '08:30'),
            'data' => $data,
        ]);
    }

    // ─── SAVE ATTENDANCE (instant AJAX, bulk) ───────────────────
    public function save(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date|before_or_equal:today',
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|exists:students,id',
            'records.*.status' => 'required|in:present,absent,leave',
        ]);

        $section = Section::find($request->section_id);

        if ($this->attendanceService->isHoliday($request->date, $section->campus)) {
            return response()->json([
                'success' => false,
                'message' => 'This date is marked as a holiday. Attendance cannot be recorded.',
            ], 422);
        }

        $now = now()->format('H:i');
        $count = 0;

        DB::transaction(function () use ($request, $section, $now, &$count) {
            foreach ($request->records as $record) {
                $isLate = $record['status'] === 'present' && $this->attendanceService->isLate($now);

                Attendance::updateOrCreate(
                    ['student_id' => $record['student_id'], 'date' => $request->date],
                    [
                        'section_id' => $section->id,
                        'status' => $record['status'],
                        'is_late' => $isLate,
                        'marked_at_time' => $now,
                        'marked_by' => auth()->id(),
                        'remarks' => $record['remarks'] ?? null,
                    ]
                );
                $count++;
            }
        });

        AuditLog::record('CREATE', 'Attendance', "Attendance saved for {$count} students — {$section->code} | ".$request->date);

        return response()->json([
            'success' => true,
            'message' => "Attendance saved for {$count} students — {$section->code} | ".Carbon::parse($request->date)->format('d M Y'),
        ]);
    }

    // ─── BULK MARK ALL PRESENT/ABSENT (helper for the "Mark All" buttons) ─
    public function markAll(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:present,absent',
        ]);

        $studentIds = Student::where('section_id', $request->section_id)->where('status', 'active')->pluck('id');

        return response()->json([
            'success' => true,
            'student_ids' => $studentIds,
            'status' => $request->status,
        ]);
    }

    // ─── REPORTS PAGE ────────────────────────────────────────────
    public function reportsPage()
    {
        $sections = Section::where('status', true)->orderBy('code')->get();
        return view('attendance.reports', compact('sections'));
    }

    // ─── AJAX: SECTION SUMMARY REPORT ────────────────────────────
    public function sectionReport(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $section = Section::find($request->section_id);
        $students = Student::where('section_id', $section->id)->where('status', 'active')->orderBy('roll_number')->get();

        $data = $students->map(function ($student) use ($section, $request) {
            $stats = $this->attendanceService->statsFor($student->id, $section->campus, $request->from, $request->to);
            return array_merge(['student_id' => $student->id, 'roll_number' => $student->roll_number, 'name' => $student->name], $stats);
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ─── AJAX: SINGLE STUDENT REPORT (used by Student/Parent dashboards too) ─
    public function studentReport(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $student = Student::find($request->student_id);

        // Students/Parents can only view their OWN/their child's data
        $user = auth()->user();
        if ($user->hasRole('Student') && $user->id !== $student->user_id) {
            abort(403);
        }

        $stats = $this->attendanceService->statsFor($student->id, $student->campus, $request->from, $request->to);

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
