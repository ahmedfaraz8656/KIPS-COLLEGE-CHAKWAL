<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\PeriodSlot;
use App\Models\TimetableEntry;
use App\Models\Teacher;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    protected array $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

    public function index()
    {
        $sections = Section::orderBy('code')->get();
        $periodSlots = PeriodSlot::orderBy('period_number')->get();
        $teachers = Teacher::where('status', true)->orderBy('name')->get();

        if ($periodSlots->isEmpty()) {
            $periodSlots = $this->createDefaultSlots();
        }

        return view('timetable.index', [
            'sections' => $sections, 'periodSlots' => $periodSlots,
            'teachers' => $teachers, 'days' => $this->days,
        ]);
    }

    protected function createDefaultSlots()
    {
        $slots = [
            ['08:00', '08:45'], ['08:45', '09:30'], ['09:30', '10:15'],
            ['10:30', '11:15'], ['11:15', '12:00'], ['12:00', '12:45'],
            ['13:15', '14:00'],
        ];
        foreach ($slots as $i => [$start, $end]) {
            PeriodSlot::create(['period_number' => $i + 1, 'start_time' => $start, 'end_time' => $end]);
        }
        return PeriodSlot::orderBy('period_number')->get();
    }

    // ─── LOAD GRID FOR A SECTION ─────────────────────────────────
    public function sectionGrid(Section $section)
    {
        $entries = TimetableEntry::where('section_id', $section->id)
            ->with('subject', 'teacher', 'periodSlot')->get();

        return response()->json(['success' => true, 'data' => $entries]);
    }

    // ─── SUBJECTS AVAILABLE FOR THIS SECTION ─────────────────────
    public function subjectsForSection(Section $section)
    {
        $subjects = $section->program->subjectsForYear($section->year)->get();
        return response()->json(['success' => true, 'data' => $subjects]);
    }

    // ─── CHECK CONFLICT BEFORE SAVE ──────────────────────────────
    public function checkConflict(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|in:MON,TUE,WED,THU,FRI,SAT',
            'period_slot_id' => 'required|exists:period_slots,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $conflict = TimetableEntry::findConflict(
            $request->teacher_id, $request->day, $request->period_slot_id, $request->section_id
        );

        return response()->json([
            'has_conflict' => (bool) $conflict,
            'message' => $conflict
                ? "{$conflict->teacher->name} already teaches {$conflict->section->code} on {$request->day} at this period."
                : null,
        ]);
    }

    // ─── ASSIGN PERIOD ────────────────────────────────────────────
    public function assign(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'day' => 'required|in:MON,TUE,WED,THU,FRI,SAT',
            'period_slot_id' => 'required|exists:period_slots,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'confirm_conflict' => 'nullable|boolean',
        ]);

        if (!$request->boolean('confirm_conflict')) {
            $conflict = TimetableEntry::findConflict($request->teacher_id, $request->day, $request->period_slot_id, $request->section_id);
            if ($conflict) {
                return response()->json([
                    'success' => false, 'needs_confirmation' => true,
                    'message' => "{$conflict->teacher->name} already teaches {$conflict->section->code} on {$request->day} at this period. Save anyway?",
                ], 409);
            }
        }

        $entry = TimetableEntry::updateOrCreate(
            ['section_id' => $request->section_id, 'day' => $request->day, 'period_slot_id' => $request->period_slot_id],
            ['subject_id' => $request->subject_id, 'teacher_id' => $request->teacher_id]
        );

        AuditLog::record('UPDATE', 'Timetable', "Period assigned: Section #{$request->section_id} {$request->day}");

        return response()->json(['success' => true, 'message' => 'Period assigned successfully.', 'data' => $entry->load('subject', 'teacher')]);
    }

    public function removeEntry(TimetableEntry $entry)
    {
        $entry->delete();
        return response()->json(['success' => true, 'message' => 'Period cleared.']);
    }

    // ─── TEACHER'S WEEKLY VIEW ────────────────────────────────────
    public function teacherView(Teacher $teacher)
    {
        $entries = TimetableEntry::where('teacher_id', $teacher->id)
            ->with('section', 'subject', 'periodSlot')->get();

        return view('timetable.teacher', compact('teacher', 'entries'));
    }
}
