<?php

namespace App\Http\Controllers\Exams;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\ExamSubjectMark;
use App\Models\Attendance;
use App\Models\TeacherSection;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarksEntryController extends Controller
{
    // ─── ENTRY PAGE ──────────────────────────────────────────────
    public function index()
    {
        $exams = Exam::where('is_demo', false)->orderByDesc('exam_date')->get();
        return view('exams.marks-entry', compact('exams'));
    }

    /**
     * AJAX: Sections available to THIS teacher for a given exam+campus+year.
     * Admin/Principal/MD/Exam Controller see ALL sections in scope.
     * A regular Teacher sees ONLY sections where they have a teaching
     * assignment (per Ahmed: "teacher apna hi subject ka data enter kare").
     */
    public function sectionsForEntry(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'campus' => 'required|in:boys,girls',
            'year' => 'required|in:first,second',
        ]);

        $exam = Exam::find($request->exam_id);
        $user = auth()->user();

        $sectionIds = $exam->sections()
            ->where('campus', $request->campus)
            ->where('year', $request->year)
            ->pluck('sections.id');

        if (!$user->hasAnyRole(['Managing Director', 'Principal', 'Admin', 'Exam Controller'])) {
            $teacherId = $user->teacher?->id;
            $sectionIds = TeacherSection::where('teacher_id', $teacherId)
                ->whereIn('section_id', $sectionIds)
                ->pluck('section_id')
                ->unique();
        }

        $sections = Section::whereIn('id', $sectionIds)->orderBy('code')->get(['id', 'code']);

        return response()->json(['success' => true, 'data' => $sections]);
    }

    /**
     * AJAX: Subject(s) THIS teacher teaches in the selected section
     * (for this exam). A teacher only ever sees their OWN subject column
     * in the marks table — never other subjects or totals across subjects.
     */
    public function subjectsForEntry(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $exam = Exam::find($request->exam_id);
        $section = Section::find($request->section_id);
        $user = auth()->user();

        $examSubjectIds = ExamSubjectMark::where('exam_id', $exam->id)
            ->where('program_id', $section->program_id)
            ->where('year', $section->year)
            ->pluck('subject_id');

        if (!$user->hasAnyRole(['Managing Director', 'Principal', 'Admin', 'Exam Controller'])) {
            $teacherId = $user->teacher?->id;
            $examSubjectIds = TeacherSection::where('teacher_id', $teacherId)
                ->where('section_id', $section->id)
                ->whereIn('subject_id', $examSubjectIds)
                ->pluck('subject_id');
        }

        $subjects = \App\Models\Subject::whereIn('id', $examSubjectIds)->get(['id', 'name']);

        return response()->json(['success' => true, 'data' => $subjects]);
    }

    /**
     * AJAX: Load the marks-entry table for ONE subject in ONE section.
     * Absent/Leave students (per Attendance on exam_date) are flagged —
     * frontend disables their input and forces 0, exactly per Ahmed's rule.
     */
    public function loadMarksTable(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $exam = Exam::find($request->exam_id);
        $section = Section::find($request->section_id);

        $examSubjectMark = ExamSubjectMark::where('exam_id', $exam->id)
            ->where('program_id', $section->program_id)
            ->where('year', $section->year)
            ->where('subject_id', $request->subject_id)
            ->first();

        if (!$examSubjectMark) {
            return response()->json(['success' => false, 'message' => 'Marks are not configured for this subject in this exam.'], 422);
        }

        $students = Student::where('section_id', $section->id)->where('status', 'active')->orderBy('roll_number')->get();

        $existingMarks = StudentMark::where('exam_id', $exam->id)
            ->where('subject_id', $request->subject_id)
            ->get()->keyBy('student_id');

        // Who was absent/leave on the exam date — these students get forced 0
        $attendanceOnExamDate = Attendance::where('section_id', $section->id)
            ->where('date', $exam->exam_date->format('Y-m-d'))
            ->whereIn('status', ['absent', 'leave'])
            ->pluck('status', 'student_id');

        $data = $students->map(function ($student) use ($existingMarks, $attendanceOnExamDate) {
            $mark = $existingMarks->get($student->id);
            $attStatus = $attendanceOnExamDate->get($student->id);

            return [
                'student_id' => $student->id,
                'roll_number' => $student->roll_number,
                'name' => $student->name,
                'father_name' => $student->father_name,
                'obtained_marks' => $mark?->obtained_marks ?? null,
                'is_absent' => $attStatus === 'absent',
                'is_leave' => $attStatus === 'leave',
            ];
        });

        return response()->json([
            'success' => true,
            'total_marks' => $examSubjectMark->total_marks,
            'is_locked' => $exam->is_locked || ($exam->isPastDue() && !auth()->user()->hasAnyRole(['Principal', 'Managing Director'])),
            'data' => $data,
        ]);
    }

    /**
     * SAVE MARKS — instant AJAX. Absent/Leave students are FORCED to 0
     * regardless of what's submitted (enforced again here server-side,
     * not just frontend, as a hard backend rule — StudentMark::booted()
     * also enforces this at the model level as a second safeguard).
     */
    public function saveMarks(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|array|min:1',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.obtained_marks' => 'nullable|integer|min:0',
        ]);

        $exam = Exam::find($request->exam_id);
        $section = Section::find($request->section_id);

        // Due-date lock check (Principal/MD can bypass via extended date already reflected in is_locked)
        if ($exam->is_locked || ($exam->isPastDue() && !auth()->user()->hasAnyRole(['Principal', 'Managing Director']))) {
            return response()->json([
                'success' => false,
                'message' => 'The due date for this exam has passed. Marks entry is locked. Ask Principal to extend the due date.',
            ], 423);
        }

        $examSubjectMark = ExamSubjectMark::where('exam_id', $exam->id)
            ->where('program_id', $section->program_id)
            ->where('year', $section->year)
            ->where('subject_id', $request->subject_id)
            ->first();

        $totalMarks = $examSubjectMark->total_marks;

        $attendanceOnExamDate = Attendance::where('section_id', $section->id)
            ->where('date', $exam->exam_date->format('Y-m-d'))
            ->whereIn('status', ['absent', 'leave'])
            ->pluck('status', 'student_id');

        $count = 0;

        DB::transaction(function () use ($request, $exam, $section, $totalMarks, $attendanceOnExamDate, &$count) {
            foreach ($request->marks as $row) {
                $attStatus = $attendanceOnExamDate->get($row['student_id']);
                $isAbsent = $attStatus === 'absent';
                $isLeave = $attStatus === 'leave';

                // Validate marks don't exceed total (real-time validation also on frontend)
                $obtained = $row['obtained_marks'] ?? 0;
                if (!$isAbsent && !$isLeave && $obtained > $totalMarks) {
                    $obtained = $totalMarks; // clamp, never allow > max
                }

                StudentMark::updateOrCreate(
                    ['student_id' => $row['student_id'], 'exam_id' => $exam->id, 'subject_id' => $request->subject_id],
                    [
                        'section_id' => $section->id,
                        'total_marks' => $totalMarks,
                        'obtained_marks' => $obtained, // model's saving() hook forces 0 if is_absent/is_leave
                        'is_absent' => $isAbsent,
                        'is_leave' => $isLeave,
                        'entered_by' => auth()->id(),
                        'entered_at' => now(),
                    ]
                );
                $count++;
            }
        });

        AuditLog::record('CREATE', 'Exams', "Marks saved for {$count} students — {$exam->name} | {$section->code}");

        return response()->json([
            'success' => true,
            'message' => "Marks saved for {$count} students.",
        ]);
    }

    // ─── BULK: SET SAME MARK FOR SELECTED STUDENTS ──────────────
    public function setSameMark(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'value' => 'required|integer|min:0',
        ]);

        return response()->json(['success' => true, 'applied_to' => count($request->student_ids), 'value' => $request->value]);
        // Frontend applies this value to all selected rows client-side before final Save.
    }
}
