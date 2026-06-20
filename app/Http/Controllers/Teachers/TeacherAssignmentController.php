<?php

namespace App\Http\Controllers\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherSection;
use App\Models\Section;
use App\Models\SectionIncharge;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    // ─── SUBJECTS FOR A GIVEN SECTION (Program + Year aware) ────
    public function subjectsForSection(Request $request)
    {
        $request->validate(['section_id' => 'required|exists:sections,id']);

        $section = Section::with('program')->find($request->section_id);

        $subjects = $section->program->subjectsForYear($section->year)->get();

        return response()->json([
            'success' => true,
            'data' => $subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
        ]);
    }

    // ─── ADD A TEACHING ASSIGNMENT (Section + Subject) ──────────
    public function addAssignment(Request $request, Teacher $teacher)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $exists = TeacherSection::where('teacher_id', $teacher->id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'This assignment already exists.'], 422);
        }

        $assignment = TeacherSection::create([
            'teacher_id' => $teacher->id,
            'section_id' => $request->section_id,
            'subject_id' => $request->subject_id,
        ]);

        $section = Section::find($request->section_id);

        AuditLog::record('CREATE', 'Teachers', "{$teacher->name} assigned to {$section->code}");

        return response()->json([
            'success' => true,
            'message' => "{$teacher->name} assigned to {$section->code} successfully.",
            'data' => $assignment->load('section', 'subject'),
        ]);
    }

    // ─── REMOVE A TEACHING ASSIGNMENT ────────────────────────────
    public function removeAssignment(TeacherSection $assignment)
    {
        $assignment->delete();
        return response()->json(['success' => true, 'message' => 'Assignment removed successfully.']);
    }

    // ─── ASSIGN AS CLASS INCHARGE (with replace-confirmation) ───
    public function checkInchargeConflict(Request $request)
    {
        $request->validate(['section_id' => 'required|exists:sections,id']);

        $section = Section::find($request->section_id);
        $current = SectionIncharge::currentInchargeOf($section);

        return response()->json([
            'has_conflict' => (bool) $current,
            'current_teacher' => $current?->name,
            'section_code' => $section->code,
        ]);
    }

    public function assignIncharge(Request $request, Teacher $teacher)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'substitute_teacher_id' => 'nullable|exists:teachers,id',
            'confirm_replace' => 'nullable|boolean',
        ]);

        $section = Section::find($request->section_id);
        $existing = SectionIncharge::where('section_id', $section->id)->first();

        if ($existing && $existing->teacher_id !== $teacher->id && !$request->boolean('confirm_replace')) {
            return response()->json([
                'success' => false,
                'needs_confirmation' => true,
                'message' => "{$section->code} already has {$existing->teacher->name} as incharge. Replace?",
            ], 409);
        }

        SectionIncharge::updateOrCreate(
            ['section_id' => $section->id],
            ['teacher_id' => $teacher->id, 'substitute_teacher_id' => $request->substitute_teacher_id]
        );

        AuditLog::record('UPDATE', 'Teachers', "{$teacher->name} set as Class Incharge of {$section->code}");

        // Ensure teacher has the Class Incharge role for permission checks
        $teacher->user?->assignRole('Class Incharge');

        return response()->json([
            'success' => true,
            'message' => "{$teacher->name} is now Class Incharge of {$section->code}.",
        ]);
    }

    public function removeIncharge(SectionIncharge $incharge)
    {
        $section = $incharge->section;
        $teacherName = $incharge->teacher->name;
        $incharge->delete();

        AuditLog::record('DELETE', 'Teachers', "{$teacherName} removed as Class Incharge of {$section->code}");

        return response()->json(['success' => true, 'message' => 'Class Incharge assignment removed.']);
    }
}
