<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\Student;
use App\Models\Program;
use App\Models\Section;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // ─── MASTER LIST (with filters) ──────────────────────────────
    public function index(Request $request)
    {
        return view('students.index');
    }

    // ─── AJAX: DataTable server-side list ────────────────────────
    public function list(Request $request)
    {
        $query = Student::query()
            ->with(['program', 'section'])
            ->where('is_demo', false);

        if ($request->filled('campus') && $request->campus !== 'all') {
            $query->where('campus', $request->campus);
        }
        if ($request->filled('year') && $request->year !== 'all') {
            $query->where('year', $request->year);
        }
        if ($request->filled('program_id') && $request->program_id !== 'all') {
            $query->where('program_id', $request->program_id);
        }
        if ($request->filled('section_id') && $request->section_id !== 'all') {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('father_name', 'like', "%{$term}%")
                  ->orWhere('roll_number', 'like', "%{$term}%")
                  ->orWhere('whatsapp', 'like', "%{$term}%");
            });
        }

        $students = $query->orderByDesc('id')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data'    => $students,
        ]);
    }

    // ─── SHOW ADMISSION FORM ──────────────────────────────────────
    public function create()
    {
        $programs = Program::where('status', true)->get();
        return view('students.create', compact('programs'));
    }

    // ─── AJAX: Sections filtered by Campus + Year + Program ───────
    // STRICT RULE: FAIT only returned when campus = girls (except seeded boys FAIT sections)
    public function sectionsFor(Request $request)
    {
        $request->validate([
            'campus'     => 'required|in:boys,girls',
            'year'       => 'required|in:first,second',
            'program_id' => 'required|exists:programs,id',
        ]);

        $sections = Section::where('campus', $request->campus)
            ->where('year', $request->year)
            ->where('program_id', $request->program_id)
            ->where('status', true)
            ->withCount(['students' => fn ($q) => $q->where('status', 'active')])
            ->get(['id', 'code', 'capacity']);

        return response()->json([
            'success' => true,
            'data' => $sections->map(fn ($s) => [
                'id'    => $s->id,
                'code'  => $s->code,
                'count' => $s->students_count,
                'label' => $s->code.' ('.$s->students_count.' students)',
            ]),
        ]);
    }

    // ─── STORE NEW STUDENT (AJAX) ──────────────────────────────────
    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $program = Program::findOrFail($data['program_id']);

            // System-generated, duplicate-proof roll number
            $rollNumber = Student::generateRollNumber($data['campus'], $data['year'], $program);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('students', 'public');
            }

            $student = Student::create(array_merge($data, [
                'roll_number'     => $rollNumber,
                'photo'           => $photoPath,
                'created_by'      => auth()->id(),
            ]));

            $student->sectionHistory()->create([
                'from_section_id' => null,
                'to_section_id'   => $student->section_id,
                'action'          => 'initial_admission',
                'performed_by'    => auth()->id(),
            ]);

            AuditLog::record(
                'CREATE', 'Students',
                "Enrolled new student: {$student->name} (Roll: {$rollNumber}) into ".$student->section->code,
                [], $student->toArray()
            );

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => "Student enrolled successfully. Roll No: {$rollNumber}",
                'roll_number'  => $rollNumber,
                'student_id'   => $student->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Could not enroll student: '.$e->getMessage(),
            ], 422);
        }
    }

    // ─── VIEW SINGLE STUDENT PROFILE ──────────────────────────────
    public function show(Student $student)
    {
        $student->load(['program', 'section', 'sectionHistory.fromSection', 'sectionHistory.toSection']);
        return view('students.show', compact('student'));
    }

    // ─── EDIT FORM ─────────────────────────────────────────────────
    public function edit(Student $student)
    {
        $programs = Program::where('status', true)->get();
        return view('students.edit', compact('student', 'programs'));
    }

    // ─── UPDATE (AJAX) ─────────────────────────────────────────────
    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'father_name'     => 'required|string|max:150',
            'whatsapp'        => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:500',
            'cnic_bform'      => 'nullable|string|max:20',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $before = $student->toArray();

        if ($request->hasFile('photo')) {
            if ($student->photo) Storage::disk('public')->delete($student->photo);
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($data);

        AuditLog::record('UPDATE', 'Students', "Updated student details: {$student->name}", $before, $student->toArray());

        return response()->json(['success' => true, 'message' => 'Student details updated successfully.']);
    }

    // ─── DELETE (AJAX, soft delete, with audit) ───────────────────
    public function destroy(Student $student)
    {
        if (!auth()->user()->can('manage students')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete students.'], 403);
        }

        AuditLog::record('DELETE', 'Students', "Deleted student: {$student->name} (Roll: {$student->roll_number})", $student->toArray());

        $student->delete(); // soft delete — recoverable via Backup/Restore module

        return response()->json(['success' => true, 'message' => 'Student deleted successfully.']);
    }

    // ─── BULK DELETE (AJAX) ────────────────────────────────────────
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array|min:1'])['ids'];

        $students = Student::whereIn('id', $ids)->get();
        foreach ($students as $student) {
            AuditLog::record('DELETE', 'Students', "Bulk deleted student: {$student->name}", $student->toArray());
        }
        Student::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($ids).' student(s) deleted successfully.',
        ]);
    }
}
