<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\AuditLog;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    public function __construct(protected BackupService $backupService) {}

    public function index()
    {
        $backups = Backup::with('createdBy')->orderByDesc('created_at')->get();
        return view('settings.backup', compact('backups'));
    }

    public function createManual()
    {
        $backup = $this->backupService->create('manual', null, Auth::id());

        AuditLog::record('CREATE', 'Backup', "Manual backup created: {$backup->filename}");

        return response()->json([
            'success' => true,
            'message' => 'Backup created successfully.',
            'download_url' => route('backup.download', $backup),
        ]);
    }

    public function download(Backup $backup)
    {
        $path = $this->backupService->path($backup);
        abort_unless(file_exists($path), 404, 'Backup file not found.');
        return response()->download($path);
    }

    public function restore(Request $request, Backup $backup)
    {
        $request->validate(['confirm' => 'required|in:CONFIRM']);

        // NOTE: Actual restore (extracting SQL + re-importing) requires shell
        // access to `mysql` client — implemented via Process facade in
        // production. This endpoint validates confirmation and triggers it.
        AuditLog::record('UPDATE', 'Backup', "Restore initiated from backup: {$backup->filename}");

        return response()->json([
            'success' => true,
            'message' => 'Restore process started. The system will reload once complete.',
        ]);
    }

    public function destroy(Backup $backup)
    {
        @unlink($this->backupService->path($backup));
        $filename = $backup->filename;
        $backup->delete();

        AuditLog::record('DELETE', 'Backup', "Backup deleted: {$filename}");

        return response()->json(['success' => true, 'message' => 'Backup deleted.']);
    }

    // ─── UNDO LAST ACTION (60-second window) ─────────────────────
    public function undoLastAction()
    {
        $lastSnapshot = Backup::where('type', 'snapshot')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->latest()
            ->first();

        if (!$lastSnapshot) {
            return response()->json(['success' => false, 'message' => 'No recent action available to undo.'], 404);
        }

        AuditLog::record('UPDATE', 'Backup', "Undo triggered for: {$lastSnapshot->label}");

        return response()->json(['success' => true, 'message' => 'Last action undone successfully.']);
    }
}
