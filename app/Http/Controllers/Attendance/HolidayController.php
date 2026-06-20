<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderByDesc('date')->paginate(20);
        return view('attendance.holidays', compact('holidays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'name' => 'required|string|max:150',
            'type' => 'required|in:public,college',
            'campus_scope' => 'required|in:boys,girls,both',
        ]);

        $from = $request->date_from;
        $to = $request->date_to ?? $from;

        $period = \Carbon\CarbonPeriod::create($from, $to);
        $created = 0;

        foreach ($period as $date) {
            Holiday::firstOrCreate([
                'date' => $date->format('Y-m-d'),
                'campus_scope' => $request->campus_scope,
            ], [
                'name' => $request->name,
                'type' => $request->type,
                'created_by' => auth()->id(),
            ]);
            $created++;
        }

        AuditLog::record('CREATE', 'Attendance', "Holiday added: {$request->name} ({$created} day(s)) — ".ucfirst($request->campus_scope));

        return response()->json([
            'success' => true,
            'message' => "Holiday '{$request->name}' added for {$created} day(s). That period is excluded from working days.",
        ]);
    }

    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();

        AuditLog::record('DELETE', 'Attendance', "Holiday removed: {$name}");

        return response()->json(['success' => true, 'message' => "Holiday '{$name}' removed."]);
    }
}
