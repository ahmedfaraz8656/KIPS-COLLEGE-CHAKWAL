<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentTransferController extends Controller
{
    /**
     * Default First Year → Second Year section mapping, per Ahmed's spec.
     * Admin can override individual mappings on the Promotion screen.
     */
    protected array $promotionMap = [
        'PCB1' => 'SCB1', 'PCB2' => 'SCB2', 'PCB3' => 'SCB3',
        'PEB/PMB' => 'SEB/SMB',
        'PCG1' => 'SCG1', 'PCG2' => 'SCG2', 'PCG3' => 'SCG3',
        'PMG1' => 'SMG1', 'PMG2' => 'SMG2', 'PMG3' => 'SMG3',
        'PEG'  => 'SEG',  'FAIT' => 'FAIT2',
        'FAIT-B1' => 'FAIT-B2',
    ];

    // ─── SECTION TRANSFER PAGE ───────────────────────────────────
    public function index()
    {
        return view('students.transfer');
    }

    /**
     * AJAX: Given a campus + year (the SOURCE filters), return only the
     * sections matching that campus+year — used for both "From" and "To"
     * dropdowns so the user never sees an invalid combination.
     *
     * STRICT RULE: Girls sections never shown when campus=boys, and
     * vice versa. Second Year sections never shown when year=first.
     */
    public function sectionsByFilter(Request $request)
    {
        $request->validate([
            'campus' => 'required|in:boys,girls',
            'year'   => 'required|in:first,second',
        ]);

        $sections = Section::where('campus', $request->campus)
            ->where('year', $request->year)
            ->where('status', true)
            ->withCount(['students' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('code')
            ->get(['id', 'code']);

        return response()->json([
            'success' => true,
            'data' => $sections->map(fn ($s) => [
                'id'    => $s->id,
                'code'  => $s->code,
                'count' => $s->students_count,
            ]),
        ]);
    }

    /**
     * AJAX: Students in a given source section (for the transfer table).
     */
    public function studentsInSection(Request $request)
    {
        $request->validate(['section_id' => 'required|exists:sections,id']);

        $students = Student::where('section_id', $request->section_id)
            ->where('status', 'active')
            ->orderBy('roll_number')
            ->get(['id', 'roll_number', 'name', 'father_name']);

        return response()->json(['success' => true, 'data' => $students]);
    }

    /**
     * SINGLE / BULK MOVE — both use this one endpoint.
     * STRICT RULE enforced at model level (Student::canMoveTo):
     *   - same campus only (boys→boys, girls→girls)
     *   - same year only (first→first, second→second)
     */
    public function move(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'to_section_id' => 'required|exists:sections,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $targetSection = Section::findOrFail($request->to_section_id);
        $moved = [];
        $rejected = [];

        DB::transaction(function () use ($request, $targetSection, &$moved, &$rejected) {
            foreach ($request->student_ids as $studentId) {
                $student = Student::find($studentId);

                if (!$student->canMoveTo($targetSection)) {
                    $rejected[] = $student->name.' (campus/year mismatch)';
                    continue;
                }

                $fromCode = $student->section->code;
                $student->moveTo($targetSection, auth()->id(), $request->reason);

                AuditLog::record(
                    'MOVE', 'Students',
                    "{$student->name} ({$student->roll_number}) moved from {$fromCode} to {$targetSection->code}",
                    ['section' => $fromCode],
                    ['section' => $targetSection->code]
                );

                $moved[] = $student->name;
            }
        });

        if (empty($moved)) {
            return response()->json([
                'success' => false,
                'message' => 'No students were moved. '.implode(', ', $rejected),
            ], 422);
        }

        $message = count($moved).' student(s) moved to '.$targetSection->code.' successfully.';
        if (!empty($rejected)) {
            $message .= ' '.count($rejected).' skipped due to campus/year mismatch.';
        }

        return response()->json(['success' => true, 'message' => $message, 'moved' => $moved, 'rejected' => $rejected]);
    }

    // ─── PROMOTION PAGE ──────────────────────────────────────────
    public function promotionIndex()
    {
        $firstYearSections = Section::where('year', 'first')->where('status', true)->orderBy('code')->get();

        $mapping = [];
        foreach ($firstYearSections as $section) {
            $defaultTarget = $this->promotionMap[$section->code] ?? null;
            $targetSection = $defaultTarget
                ? Section::where('code', $defaultTarget)->first()
                : null;

            $mapping[] = [
                'from_section' => $section,
                'to_section'   => $targetSection,
                'student_count'=> $section->students()->where('status', 'active')->count(),
            ];
        }

        return view('students.promote', compact('mapping'));
    }

    /**
     * Promote First Year students to Second Year.
     * Accepts either "promote all in section" or specific student_ids,
     * with an admin-overridable section mapping.
     */
    public function promote(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array|min:1',
            'mappings.*.from_section_id' => 'required|exists:sections,id',
            'mappings.*.to_section_id'   => 'required|exists:sections,id',
            'student_ids' => 'nullable|array', // optional: specific students only
        ]);

        $totalPromoted = 0;

        DB::transaction(function () use ($request, &$totalPromoted) {
            foreach ($request->mappings as $mapping) {
                $fromSection = Section::find($mapping['from_section_id']);
                $toSection   = Section::find($mapping['to_section_id']);

                // Enforce: target must actually be a Second Year section
                // of the SAME campus as the source — never cross campus/year.
                if ($toSection->year !== 'second' || $toSection->campus !== $fromSection->campus) {
                    continue;
                }

                $query = Student::where('section_id', $fromSection->id)->where('status', 'active');

                if (!empty($request->student_ids)) {
                    $query->whereIn('id', $request->student_ids);
                }

                $students = $query->get();

                foreach ($students as $student) {
                    $student->sectionHistory()->create([
                        'from_section_id' => $fromSection->id,
                        'to_section_id'   => $toSection->id,
                        'action'          => 'promote',
                        'performed_by'    => auth()->id(),
                    ]);

                    $student->update([
                        'year'        => 'second',
                        'section_id'  => $toSection->id,
                        'program_id'  => $toSection->program_id,
                        'status'      => 'promoted',
                        'status_note' => "Promoted from {$fromSection->code} to {$toSection->code} on ".now()->format('d-M-Y'),
                    ]);

                    // Re-activate — "promoted" is a transitional flag, student stays active going forward
                    $student->update(['status' => 'active']);

                    $totalPromoted++;
                }

                AuditLog::record(
                    'PROMOTE', 'Students',
                    "{$students->count()} student(s) promoted from {$fromSection->code} to {$toSection->code}"
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => "{$totalPromoted} student(s) promoted to Second Year successfully.",
        ]);
    }
}
