<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('action')) $query->where('action', $request->action);
        if ($request->filled('module')) $query->where('module', $request->module);
        if ($request->filled('from')) $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('created_at', '<=', $request->to);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('description', 'like', "%{$s}%"));
        }

        $logs = $query->paginate(50);
        $users = User::orderBy('name')->get(['id', 'name']);
        $modules = AuditLog::distinct()->pluck('module');
        $actions = AuditLog::distinct()->pluck('action');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $logs->map(fn ($l) => [
                    'date' => $l->created_at->format('d-M-Y h:i A'),
                    'user' => $l->user?->name ?? 'System',
                    'action' => $l->action,
                    'module' => $l->module,
                    'description' => $l->description,
                ]),
                'total' => $logs->total(),
            ]);
        }

        return view('settings.audit', compact('users', 'modules', 'actions'));
    }

    public function exportPdf(Request $request)
    {
        $logs = AuditLog::with('user')->orderByDesc('created_at')->limit(500)->get();
        $pdf = Pdf::loadView('settings.audit-pdf', compact('logs'))->setPaper('a4', 'landscape');
        return $pdf->download('Audit_Trail_'.now()->format('Ymd').'.pdf');
    }
}
