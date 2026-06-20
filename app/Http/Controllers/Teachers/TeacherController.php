<?php

namespace App\Http\Controllers\Teachers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    // ─── LIST PAGE ───────────────────────────────────────────────
    public function index()
    {
        $summary = [
            'total'        => Teacher::where('is_demo', false)->count(),
            'male'         => Teacher::where('is_demo', false)->where('gender', 'male')->count(),
            'female'       => Teacher::where('is_demo', false)->where('gender', 'female')->count(),
            'boys_campus'  => Teacher::where('is_demo', false)->whereIn('campus_access', ['boys', 'both'])->count(),
            'girls_campus' => Teacher::where('is_demo', false)->whereIn('campus_access', ['girls', 'both'])->count(),
            'incharges'    => Teacher::where('is_demo', false)->has('inchargeOf')->count(),
        ];

        return view('teachers.index', compact('summary'));
    }

    // ─── AJAX DATATABLE LIST ─────────────────────────────────────
    public function list(Request $request)
    {
        $query = Teacher::query()->where('is_demo', false)
            ->withCount(['sectionAssignments as subjects_count' => fn ($q) => $q->select(DB::raw('count(distinct subject_id)'))])
            ->with('inchargeOf.section');

        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('campus') && $request->campus !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('campus_access', $request->campus)->orWhere('campus_access', 'both');
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status === 'active');
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('whatsapp', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"));
        }

        $teachers = $query->orderBy('name')->paginate($request->get('per_page', 25));

        $data = $teachers->getCollection()->map(function ($t) {
            return [
                'id'             => $t->id,
                'photo_url'      => $t->photo_url,
                'name'           => $t->name,
                'gender'         => ucfirst($t->gender),
                'whatsapp'       => $t->whatsapp,
                'sections_count' => $t->sections_count,
                'subjects_count' => $t->subjects_count,
                'incharge_of'    => $t->inchargeOf->pluck('section.code')->filter()->implode(', ') ?: '—',
                'campus_access'  => ucfirst($t->campus_access),
                'status'         => $t->status,
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $teachers->total(),
            'current_page' => $teachers->currentPage(),
            'last_page' => $teachers->lastPage(),
        ]);
    }

    // ─── CREATE ──────────────────────────────────────────────────
    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::whereIn('name', [
            'Teacher', 'Class Incharge', 'Exam Controller',
        ])->get();

        return view('teachers.create', compact('roles'));
    }

    public function store(StoreTeacherRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('teachers', 'public');
        }

        $teacher = null;

        DB::transaction(function () use (&$teacher, $data, $request) {
            // Create login account automatically for the teacher
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make(Str::random(10)), // temp — force change on first login
                'whatsapp' => $data['whatsapp'],
                'gender'   => $data['gender'],
                'photo'    => $data['photo'] ?? null,
                'campus'   => $data['campus_access'],
                'status'   => $data['status'] ?? true,
                'force_password_change' => true,
            ]);
            $user->assignRole($request->input('roles', ['Teacher']));

            $teacher = Teacher::create($data + ['user_id' => $user->id]);

            AuditLog::record('CREATE', 'Teachers', "Teacher created: {$teacher->name}");
        });

        return response()->json([
            'success' => true,
            'message' => "Teacher {$teacher->name} added successfully. Login credentials created.",
            'redirect' => route('teachers.show', $teacher),
        ]);
    }

    // ─── SHOW (profile with tabs) ───────────────────────────────
    public function show(Teacher $teacher)
    {
        $teacher->load(['sectionAssignments.section', 'sectionAssignments.subject', 'inchargeOf.section', 'inchargeOf.substituteTeacher']);
        return view('teachers.show', compact('teacher'));
    }

    // ─── EDIT ────────────────────────────────────────────────────
    public function edit(Teacher $teacher)
    {
        $roles = \Spatie\Permission\Models\Role::whereIn('name', [
            'Teacher', 'Class Incharge', 'Exam Controller',
        ])->get();
        $currentRoles = $teacher->user?->roles->pluck('name')->toArray() ?? [];

        return view('teachers.edit', compact('teacher', 'roles', 'currentRoles'));
    }

    public function update(StoreTeacherRequest $request, Teacher $teacher)
    {
        $data = $request->validated();
        $before = $teacher->only(['name', 'whatsapp', 'campus_access', 'status']);

        if ($request->hasFile('photo')) {
            if ($teacher->photo) Storage::disk('public')->delete($teacher->photo);
            $data['photo'] = $request->file('photo')->store('teachers', 'public');
        }

        $teacher->update($data);

        if ($teacher->user) {
            $teacher->user->update([
                'name' => $data['name'], 'whatsapp' => $data['whatsapp'],
                'gender' => $data['gender'], 'campus' => $data['campus_access'],
                'status' => $data['status'] ?? $teacher->user->status,
            ]);
            if ($request->filled('roles')) {
                $teacher->user->syncRoles($request->roles);
            }
        }

        AuditLog::record('UPDATE', 'Teachers', "Teacher updated: {$teacher->name}", $before, $teacher->only(array_keys($before)));

        return response()->json(['success' => true, 'message' => 'Teacher updated successfully.']);
    }

    // ─── DELETE ──────────────────────────────────────────────────
    public function destroy(Teacher $teacher)
    {
        $name = $teacher->name;

        DB::transaction(function () use ($teacher) {
            $teacher->sectionAssignments()->delete();
            $teacher->inchargeOf()->delete();
            $teacher->user?->delete();
            $teacher->delete();
        });

        AuditLog::record('DELETE', 'Teachers', "Teacher deleted: {$name}");

        return response()->json(['success' => true, 'message' => "Teacher {$name} deleted successfully."]);
    }

    // ─── TOGGLE STATUS (Disable/Enable login access) ────────────
    public function toggleStatus(Teacher $teacher)
    {
        $teacher->status = !$teacher->status;
        $teacher->save();
        $teacher->user?->update(['status' => $teacher->status]);

        AuditLog::record(
            $teacher->status ? 'ENABLE' : 'DISABLE',
            'Teachers',
            "Teacher account ".($teacher->status ? 'enabled' : 'disabled').": {$teacher->name}"
        );

        return response()->json([
            'success' => true,
            'message' => $teacher->name.' has been '.($teacher->status ? 'enabled' : 'disabled').'.',
            'status' => $teacher->status,
        ]);
    }

    // ─── WORKLOAD VIEW ───────────────────────────────────────────
    public function workload(Teacher $teacher)
    {
        $assignments = $teacher->sectionAssignments()->with(['section', 'subject'])->get();

        $periodsPerDay = $assignments->count(); // simplified: 1 period per assignment per day (Timetable module refines this later)

        return response()->json([
            'success' => true,
            'data' => [
                'sections_count'  => $assignments->pluck('section_id')->unique()->count(),
                'subjects_count'  => $assignments->pluck('subject_id')->unique()->count(),
                'periods_per_day' => $periodsPerDay,
                'periods_per_week'=> $periodsPerDay * 6,
                'assignments' => $assignments->map(fn ($a) => [
                    'section' => $a->section->code,
                    'subject' => $a->subject->name,
                ]),
            ],
        ]);
    }
}
