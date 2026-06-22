<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Exam;
use App\Models\AcademicEvent;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        return view('calendar.index', compact('month', 'year'));
    }

    // ─── AJAX: All events for a given month, color-coded ────────
    public function eventsForMonth(Request $request)
    {
        $start = Carbon::create($request->year, $request->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $events = [];

        // Holidays
        foreach (Holiday::whereBetween('date', [$start, $end])->get() as $h) {
            $color = $h->type === 'public' ? '#E74C3C' : match ($h->campus_scope) {
                'boys' => '#F1C40F', 'girls' => '#9B59B6', default => '#F39C12',
            };
            $events[] = [
                'date' => $h->date->format('Y-m-d'), 'title' => $h->name,
                'color' => $color, 'type' => 'Holiday', 'campus' => $h->campus_scope,
            ];
        }

        // Exam days
        foreach (Exam::whereBetween('exam_date', [$start, $end])->get() as $e) {
            $events[] = [
                'date' => $e->exam_date->format('Y-m-d'), 'title' => $e->name,
                'color' => '#3498DB', 'type' => 'Exam', 'campus' => $e->campus_scope,
            ];
        }

        // College Events
        foreach (AcademicEvent::whereBetween('date', [$start, $end])->get() as $ev) {
            $events[] = [
                'id' => $ev->id, 'date' => $ev->date->format('Y-m-d'), 'title' => $ev->title,
                'color' => '#27AE60', 'type' => 'Event', 'campus' => $ev->campus_scope,
                'description' => $ev->description,
            ];
        }

        return response()->json(['success' => true, 'data' => $events]);
    }

    // ─── ADD EVENT / HOLIDAY (click on date) ─────────────────────
    public function storeEvent(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:150',
            'type' => 'required|in:holiday,event',
            'campus_scope' => 'required|in:boys,girls,both',
            'description' => 'nullable|string',
            'holiday_type' => 'required_if:type,holiday|in:public,college',
        ]);

        if ($request->type === 'holiday') {
            $record = Holiday::create([
                'date' => $request->date, 'name' => $request->title,
                'type' => $request->holiday_type, 'campus_scope' => $request->campus_scope,
                'created_by' => auth()->id(),
            ]);
            AuditLog::record('CREATE', 'Calendar', "Holiday added: {$record->name} on {$record->date}");
        } else {
            $record = AcademicEvent::create($request->only('date', 'title', 'description', 'campus_scope') + ['created_by' => auth()->id()]);
            AuditLog::record('CREATE', 'Calendar', "Event added: {$record->title} on {$record->date}");
        }

        return response()->json(['success' => true, 'message' => 'Added to calendar successfully. This also syncs with the Attendance module.']);
    }

    public function destroyHoliday(Holiday $holiday)
    {
        $holiday->delete();
        return response()->json(['success' => true, 'message' => 'Holiday removed.']);
    }

    public function destroyEvent(AcademicEvent $event)
    {
        $event->delete();
        return response()->json(['success' => true, 'message' => 'Event removed.']);
    }
}
