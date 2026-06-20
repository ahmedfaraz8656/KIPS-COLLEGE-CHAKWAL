<?php

namespace App\Http\Controllers\Exams;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamRequest;
use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\ExamSubjectMark;
use App\Models\Program;
use App\Models\Section;
use App\Models\GradingTemplate;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    // ─── LIST ────────────────────────────────────────────────────
    public function index()
    {
        $exams = Exam::where('is_demo', false)
            ->withCount('sections')
            ->orderByDesc('exam_date')
            ->paginate(15);

        return view('exams.index', compact('exams'));
    }

    // ─── CREATE PAGE ─────────────────────────────────────────────
    public function create()
    {
        $programs = Program::where('status', true)->get();
        $gradingTemplates = GradingTemplate::all();

        // Standard exam type sequence, per Ahmed's spec
        $examTypes = [
            'test' => 'Test (1-10 series)',
            'flp'  => 'FLP (First Learning Phase)',
            'rnt'  => 'RNT (Revision/Retest)',
            'send_up' => 'Send Up',
            'pre_board' => 'Pre Board',
            'custom' => 'Custom',
        ];

        return view('exams.create', compact('programs', 'gradingTemplates', 'examTypes'));
    }

    /**
     * AJAX — CORE OF MODULE 7: Dynamic Subject Loading.
     *
     * Given Program + Year + Exam Type (+ sequence number for rotation),
     * returns the default subject list + marks for that combination,
     * pre-filled from program_subject table.
     *
     * ROTATION RULE:
     *  - Test Series (type=test): only ONE of the rotating pair shows,
     *    alternating by sequence number (odd/even).
     *  - FLP / Send Up / Pre Board: BOTH subjects in the rotation_group
     *    show (cumulative exams use all 7 subjects).
     */
    public function getDefaultSubjects(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'year' => 'required|in:first,second',
            'type' => 'required|string',
            'sequence' => 'nullable|integer',
        ]);

        $program = Program::find($request->program_id);
        $allSubjects = $program->subjectsForYear($request->year)->get();

        $isTestSeries = $request->type === 'test';
        $sequence = (int) ($request->sequence ?? 1);

        $filtered = $allSubjects->filter(function ($subject) use ($isTestSeries, $sequence, $allSubjects) {
            $pivot = $subject->pivot;

            if (!$pivot->is_rotating) {
                return true; // always-included subject
            }

            if (!$isTestSeries) {
                return true; // FLP/Send Up/Pre Board: include BOTH rotating subjects
            }

            // Test series: alternate by sequence parity within the same rotation_group
            $groupMembers = $allSubjects->where('pivot.rotation_group', $pivot->rotation_group)->values();
            $indexInGroup = $groupMembers->search(fn ($s) => $s->id === $subject->id);

            // Odd test number (1,3,5...) -> first member of group;
            // Even test number (2,4,6...) -> second member.
            $targetIndex = $sequence % 2 === 1 ? 0 : 1;

            return $indexInGroup === $targetIndex;
        })->values();

        return response()->json([
            'success' => true,
            'data' => $filtered->map(fn ($s) => [
                'subject_id' => $s->id,
                'name' => $s->name,
                'default_marks' => $s->pivot->default_marks,
                'sort_order' => $s->pivot->sort_order,
                'is_rotating' => (bool) $s->pivot->is_rotating,
                'rotation_group' => $s->pivot->rotation_group,
            ])->sortBy('sort_order')->values(),
            'grand_total' => $filtered->sum('pivot.default_marks'),
        ]);
    }

    /**
     * AJAX — which sections will be affected for a given Program+Year,
     * filtered further by the exam's overall campus_scope.
     * Used to show the live preview: "This applies to PCB1, PCB2, PCB3 (142 students)"
     */
    public function affectedSections(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'year' => 'required|in:first,second',
            'campus_scope' => 'required|in:boys,girls,both',
        ]);

        $query = Section::where('program_id', $request->program_id)
            ->where('year', $request->year)
            ->where('status', true);

        if ($request->campus_scope !== 'both') {
            $query->where('campus', $request->campus_scope);
        }

        $sections = $query->withCount(['students' => fn ($q) => $q->where('status', 'active')])->get();

        return response()->json([
            'success' => true,
            'sections' => $sections->pluck('code'),
            'section_ids' => $sections->pluck('id'),
            'total_students' => $sections->sum('students_count'),
        ]);
    }

    // ─── STORE (Create Exam) ─────────────────────────────────────
    public function store(StoreExamRequest $request)
    {
        $exam = null;

        DB::transaction(function () use ($request, &$exam) {
            $exam = Exam::create([
                'name' => $request->name,
                'type' => $request->type,
                'sequence' => $request->sequence,
                'exam_date' => $request->exam_date,
                'campus_scope' => $request->campus_scope,
                'year_scope' => $request->year_scope,
                'description' => $request->description,
                'grading_template_id' => $request->grading_template_id ?? GradingTemplate::where('is_default', true)->first()?->id,
                'marks_due_date' => $request->marks_due_date,
                'created_by' => auth()->id(),
            ]);

            $affectedSectionIds = collect();

            // Group subject_marks by program_id + year so we resolve sections ONCE per group
            $grouped = collect($request->subject_marks)->groupBy(fn ($row) => $row['program_id'].'-'.$row['year']);

            foreach ($grouped as $rows) {
                $programId = $rows->first()['program_id'];
                $year = $rows->first()['year'];

                // Resolve target sections for this Program + Year, filtered by exam's campus_scope
                $sectionQuery = Section::where('program_id', $programId)->where('year', $year)->where('status', true);
                if ($exam->campus_scope !== 'both') {
                    $sectionQuery->where('campus', $exam->campus_scope);
                }
                $sectionIds = $sectionQuery->pluck('id');
                $affectedSectionIds = $affectedSectionIds->merge($sectionIds);

                // Save the configured marks ONCE per program+year (applies to ALL matching sections)
                foreach ($rows as $row) {
                    ExamSubjectMark::create([
                        'exam_id' => $exam->id,
                        'program_id' => $programId,
                        'year' => $year,
                        'subject_id' => $row['subject_id'],
                        'total_marks' => $row['total_marks'],
                    ]);
                }
            }

            // Link exam to every resolved section (deduplicated)
            foreach ($affectedSectionIds->unique() as $sectionId) {
                ExamSection::create(['exam_id' => $exam->id, 'section_id' => $sectionId]);
            }

            AuditLog::record('CREATE', 'Exams', "Exam created: {$exam->name} for {$affectedSectionIds->unique()->count()} section(s)");
        });

        return response()->json([
            'success' => true,
            'message' => "{$exam->name} created successfully for ".$exam->sections()->count()." section(s).",
            'redirect' => route('exams.show', $exam),
        ]);
    }

    // ─── SHOW ────────────────────────────────────────────────────
    public function show(Exam $exam)
    {
        $exam->load(['sections.program', 'subjectMarks.subject', 'subjectMarks.program', 'gradingTemplate']);
        $incompleteTeachers = $exam->isPastDue() ? $exam->teachersWithIncompleteMarks() : collect();

        return view('exams.show', compact('exam', 'incompleteTeachers'));
    }

    // ─── DELETE ──────────────────────────────────────────────────
    public function destroy(Exam $exam)
    {
        $name = $exam->name;

        DB::transaction(function () use ($exam) {
            $exam->subjectMarks()->delete();
            $exam->studentMarks()->delete();
            $exam->sections()->detach();
            $exam->delete();
        });

        AuditLog::record('DELETE', 'Exams', "Exam deleted: {$name}");

        return response()->json(['success' => true, 'message' => "{$name} deleted successfully."]);
    }

    // ─── EXTEND DUE DATE (Principal/MD only) ────────────────────
    public function extendDueDate(Request $request, Exam $exam)
    {
        $request->validate(['new_due_date' => 'required|date|after:now']);

        $exam->update(['marks_due_date_extended_to' => $request->new_due_date]);

        AuditLog::record('UPDATE', 'Exams', "Due date extended for {$exam->name} to {$request->new_due_date}");

        return response()->json([
            'success' => true,
            'message' => "Due date extended to ".\Carbon\Carbon::parse($request->new_due_date)->format('d M Y, h:i A'),
        ]);
    }

    // ─── INCOMPLETE TEACHERS (who hasn't entered marks by due date) ─
    public function incompleteTeachers(Exam $exam)
    {
        $teachers = $exam->teachersWithIncompleteMarks();

        return response()->json([
            'success' => true,
            'data' => $teachers->map(fn ($t) => [
                'teacher' => $t->teacher->name,
                'section' => $t->section->code,
                'subject' => $t->subject->name,
            ]),
        ]);
    }
}
