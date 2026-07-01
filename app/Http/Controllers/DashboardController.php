<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // ─── MAIN DASHBOARD ──────────────────────────────────────────
    public function index()
    {
        $user = Auth::user();
        $role = $user->primaryRole();

        // Route to role-specific view
        $viewMap = [
            'Managing Director' => 'dashboard.admin',
            'Principal'         => 'dashboard.admin',
            'Admin'             => 'dashboard.admin',
            'Exam Controller'   => 'dashboard.exam_controller',
            'Teacher'           => 'dashboard.teacher',
            'Class Incharge'    => 'dashboard.class_incharge',
            'Student'           => 'dashboard.student',
            'Parent'            => 'dashboard.parent',
        ];

        $view = $viewMap[$role] ?? 'dashboard.admin';

        // Get initial stats (cached for 5 minutes)
        $stats = $this->buildStats($role);

        return view($view, compact('stats', 'user', 'role'));
    }

    // ─── BUILD STATS PER ROLE ────────────────────────────────────
    protected function buildStats(string $role): array
    {
        // For admin-level roles, show full stats
        if (in_array($role, ['Managing Director', 'Principal', 'Admin'])) {
            return Cache::remember("dashboard_stats_{$role}", 300, function () {
                return [
                    'total_students'  => $this->safeCount('students'),
                    'total_boys'      => $this->safeCount('students', ['campus' => 'boys']),
                    'total_girls'     => $this->safeCount('students', ['campus' => 'girls']),
                    'total_teachers'  => $this->safeCount('teachers'),
                    'first_year'      => $this->safeCount('students', ['year' => 'first']),
                    'second_year'     => $this->safeCount('students', ['year' => 'second']),
                    'active_sections' => $this->safeCount('sections'),
                    'today_attendance'=> $this->todayAttendancePercent(),
                    'total_exams'     => $this->safeCount('exams'),
                    'pending_fees'    => $this->pendingFeesTotal(),
                    'fees_this_month' => $this->feesCollectedThisMonth(),
                ];
            });
        }

        return [];
    }

    protected function pendingFeesTotal(): float
    {
        try {
            return (float) \App\Models\Fee::where('is_demo', false)
                ->selectRaw('SUM(amount_due - amount_paid - waiver_amount) as total')
                ->value('total') ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function feesCollectedThisMonth(): float
    {
        try {
            return (float) \App\Models\Fee::where('is_demo', false)
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount_paid');
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function safeCount(string $table, array $where = []): int
    {
        try {
            $query = \DB::table($table)->where('is_demo', false);
            foreach ($where as $col => $val) {
                $query->where($col, $val);
            }
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function todayAttendancePercent(): float
    {
        try {
            $today = Carbon::today()->toDateString();
            $total   = \DB::table('attendance')->whereDate('date', $today)->count();
            $present = \DB::table('attendance')->whereDate('date', $today)
                         ->whereIn('status', ['present', 'late'])->count();
            return $total > 0 ? round(($present / $total) * 100, 1) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    // ─── AJAX: STATS CARDS ───────────────────────────────────────
    public function getStats()
    {
        $this->authorizeAdmin();
        return response()->json([
            'success' => true,
            'data'    => $this->buildStats(Auth::user()->primaryRole()),
        ]);
    }

    // ─── AJAX: GENDER CHART ──────────────────────────────────────
    public function genderChart()
    {
        $this->authorizeAdmin();
        $boys  = $this->safeCount('students', ['campus' => 'boys']);
        $girls = $this->safeCount('students', ['campus' => 'girls']);
        return response()->json([
            'labels' => ['Boys', 'Girls'],
            'data'   => [$boys, $girls],
            'colors' => ['#1E3A5F', '#E74C3C'],
        ]);
    }

    // ─── AJAX: SECTIONS CHART ────────────────────────────────────
    public function sectionsChart()
    {
        $this->authorizeAdmin();
        try {
            $data = \DB::table('students as s')
                ->join('sections as sec', 's.section_id', '=', 'sec.id')
                ->select('sec.code', \DB::raw('COUNT(s.id) as count'))
                ->where('s.is_demo', false)
                ->groupBy('sec.code', 'sec.id')
                ->orderBy('count', 'desc')
                ->get();
            return response()->json([
                'labels' => $data->pluck('code'),
                'data'   => $data->pluck('count'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['labels' => [], 'data' => []]);
        }
    }

    // ─── AJAX: PROGRAMS CHART ────────────────────────────────────
    public function programsChart()
    {
        $this->authorizeAdmin();
        try {
            $data = \DB::table('students as s')
                ->join('programs as p', 's.program_id', '=', 'p.id')
                ->select('p.name', \DB::raw('COUNT(s.id) as count'))
                ->where('s.is_demo', false)
                ->groupBy('p.name', 'p.id')
                ->get();
            return response()->json([
                'labels' => $data->pluck('name'),
                'data'   => $data->pluck('count'),
                'colors' => ['#1E3A5F', '#E74C3C', '#F39C12', '#27AE60'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['labels' => [], 'data' => []]);
        }
    }

    // ─── AJAX: ATTENDANCE TREND ──────────────────────────────────
    public function attendanceChart()
    {
        $this->authorizeAdmin();
        try {
            $days = collect(range(29, 0))->map(function ($i) {
                $date    = Carbon::today()->subDays($i)->toDateString();
                $total   = \DB::table('attendance')->whereDate('date', $date)->count();
                $present = \DB::table('attendance')->whereDate('date', $date)
                             ->whereIn('status', ['present', 'late'])->count();
                return [
                    'date'    => Carbon::today()->subDays($i)->format('d M'),
                    'percent' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                ];
            });
            return response()->json([
                'labels' => $days->pluck('date'),
                'data'   => $days->pluck('percent'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['labels' => [], 'data' => []]);
        }
    }

    // ─── AJAX: RECENT ACTIVITY ───────────────────────────────────
    public function recentActivity()
    {
        $this->authorizeAdmin();
        try {
            $logs = \DB::table('audit_logs as a')
                ->join('users as u', 'a.user_id', '=', 'u.id')
                ->select('u.name', 'a.action', 'a.module', 'a.description', 'a.created_at')
                ->orderBy('a.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    $log->time_ago = Carbon::parse($log->created_at)->diffForHumans();
                    return $log;
                });
            return response()->json(['success' => true, 'data' => $logs]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    // ─── AJAX: PENDING ALERTS ────────────────────────────────────
    public function pendingAlerts()
    {
        $this->authorizeAdmin();
        $alerts = [];

        // 1. Students with attendance below 75% (computed from attendance table,
        //    students.attendance_percent does not exist as a stored column)
        try {
            $minPercent = (float) \App\Models\Setting::get('attendance_min_percent', 75);
            $lowAttendanceCount = \DB::table('students')
                ->where('is_demo', false)
                ->where('status', 'active')
                ->whereIn('id', function ($q) use ($minPercent) {
                    $q->select('student_id')
                        ->from('attendance')
                        ->whereIn('status', ['present', 'absent', 'leave'])
                        ->groupBy('student_id')
                        ->havingRaw(
                            '(SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) / COUNT(*)) * 100 < ?',
                            [$minPercent]
                        );
                })
                ->count();

            if ($lowAttendanceCount > 0) {
                $alerts[] = [
                    'type'    => 'danger',
                    'icon'    => 'fa-user-clock',
                    'message' => "{$lowAttendanceCount} student(s) have attendance below {$minPercent}%",
                    'link'    => route('attendance.reports'),
                ];
            }
        } catch (\Exception $e) {}

        // 2. Recent exams (last 14 days) with incomplete marks entry
        try {
            $recentExams = \App\Models\Exam::where('is_demo', false)
                ->where('exam_date', '>=', now()->subDays(14))
                ->get();

            foreach ($recentExams as $exam) {
                $incomplete = $exam->teachersWithIncompleteMarks();
                if ($incomplete->isNotEmpty()) {
                    $alerts[] = [
                        'type'    => 'warning',
                        'icon'    => 'fa-pen-to-square',
                        'message' => "{$exam->name}: {$incomplete->count()} section/subject(s) have pending marks entry",
                        'link'    => route('exams.show', $exam),
                    ];
                }
            }
        } catch (\Exception $e) {}

        // 3. Overdue unpaid fees
        try {
            $overdueCount = \App\Models\Fee::whereDate('payment_date', '<', now())
                ->whereRaw('(amount_due - amount_paid - waiver_amount) > 0')
                ->where('is_demo', false)
                ->count();

            if ($overdueCount > 0) {
                $alerts[] = [
                    'type'    => 'danger',
                    'icon'    => 'fa-money-bill-wave',
                    'message' => "{$overdueCount} fee record(s) are overdue and unpaid",
                    'link'    => route('fees.reports'),
                ];
            }
        } catch (\Exception $e) {}

        // 4. Sections with no Class Incharge assigned
        try {
            $noInchargeCount = \App\Models\Section::where('status', true)
                ->whereDoesntHave('incharge')
                ->count();

            if ($noInchargeCount > 0) {
                $alerts[] = [
                    'type'    => 'info',
                    'icon'    => 'fa-user-slash',
                    'message' => "{$noInchargeCount} section(s) have no Class Incharge assigned",
                    'link'    => route('teachers.index'),
                ];
            }
        } catch (\Exception $e) {}

        return response()->json(['success' => true, 'data' => $alerts]);
    }

    // ─── HELPER ──────────────────────────────────────────────────
    protected function authorizeAdmin(): void
    {
        if (!Auth::user()->hasAnyRole(['Managing Director','Principal','Admin','Exam Controller'])) {
            abort(403, 'Unauthorized');
        }
    }
}
