<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\DemoDataService;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class DemoDataController extends Controller
{
    public function __construct(protected DemoDataService $demoDataService) {}

    public function load()
    {
        $result = $this->demoDataService->load();

        AuditLog::record('IMPORT', 'Demo Data', "Sample data loaded: {$result['students']} students, {$result['teachers']} teachers, {$result['exams']} exams");

        return response()->json([
            'success' => true,
            'message' => "Sample data loaded successfully — {$result['students']} students, {$result['teachers']} teachers, {$result['exams']} exams with marks and 30 days of attendance.",
        ]);
    }

    public function delete()
    {
        $this->demoDataService->deleteAll();

        AuditLog::record('DELETE', 'Demo Data', 'All sample/demo data deleted');

        return response()->json(['success' => true, 'message' => 'All sample data has been removed. Your real data is untouched.']);
    }
}
