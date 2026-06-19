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
                    'pending_fees'    => 0,
                ];
            });
        }

        return [];
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

        try {
            // Low attendance students (< 75%)
            $lowAtt = \DB::table('students')->where('attendance_percent', '<', 75)
                        ->where('is_demo', false)->count();
            if ($lowAtt > 0) {
                $alerts[] = [
                    'type'    => 'danger',
                    'icon'    => 'fa-user-clock',
                    'message' => "{$lowAtt} student(s) have attendance below 75%",
                    'link'    => route('dashboard'),
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
