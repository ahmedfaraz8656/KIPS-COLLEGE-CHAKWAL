<?php

namespace App\Http\Controllers\Results;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Section;
use App\Models\Student;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade\Pdf;

class ResultController extends Controller
{
    public function __construct(protected ResultService $resultService) {}

    // ─── SELECTION PAGE ──────────────────────────────────────────
    public function index()
    {
        $exams = Exam::where('is_demo', false)->orderByDesc('exam_date')->get();
        return view('results.index', compact('exams'));
    }

    // ─── AJAX: Resolve which students match the filter ───────────
    public function resolveStudents(Request $request)
    {
        $request->validate([
            'campus' => 'nullable|in:boys,girls,both',
            'year'   => 'nullable|in:first,second,both',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $query = Student::where('status', 'active');

        if ($request->section_id) {
            $query->where('section_id', $request->section_id);
        } else {
            if ($request->filled('campus') && $request->campus !== 'both') $query->where('campus', $request->campus);
            if ($request->filled('year') && $request->year !== 'both') $query->where('year', $request->year);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('roll_number', 'like', "%{$s}%"));
        }

        $students = $query->orderBy('roll_number')->limit(500)->get(['id', 'roll_number', 'name']);

        return response()->json(['success' => true, 'data' => $students, 'count' => $students->count()]);
    }

    // ─── PREVIEW (mini cards on screen before PDF) ───────────────
    public function preview(Request $request)
    {
        $request->validate([
            'exam_ids' => 'required|array|min:1',
            'student_ids' => 'required|array|min:1',
        ]);

        $students = Student::whereIn('id', $request->student_ids)->with('section', 'program')->get();

        $results = $students->map(fn ($student) => $this->resultService->build($student, $request->exam_ids));

        return response()->json(['success' => true, 'data' => $results]);
    }

    // ─── GENERATE PDF (single or bulk — one page per student) ───
    public function generatePdf(Request $request)
    {
        $request->validate([
            'exam_ids' => 'required|array|min:1',
            'student_ids' => 'required|array|min:1',
        ]);

        $students = Student::whereIn('id', $request->student_ids)
            ->with('section', 'program')
            ->orderBy('roll_number')
            ->get();

        $results = $students->map(fn ($student) => $this->resultService->build($student, $request->exam_ids));

        $pdf = Pdf::loadView('results.pdf', compact('results'))->setPaper('a4', 'portrait');

        $filename = $results->count() === 1
            ? 'Result_'.$results->first()['student']['roll_number'].'.pdf'
            : 'Results_Bulk_'.now()->format('Ymd_His').'.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    // ─── GENERATE SHAREABLE LINK (expires in 24h) ────────────────
    public function shareLink(Request $request)
    {
        $request->validate([
            'exam_ids' => 'required|array|min:1',
            'student_ids' => 'required|array|min:1',
        ]);

        $url = URL::temporarySignedRoute(
            'results.shared-pdf',
            now()->addHours(24),
            ['exam_ids' => implode(',', $request->exam_ids), 'student_ids' => implode(',', $request->student_ids)]
        );

        return response()->json(['success' => true, 'url' => $url, 'expires_in' => '24 hours']);
    }

    // ─── PUBLIC SIGNED PDF ENDPOINT (for the share link above) ──
    public function sharedPdf(Request $request)
    {
        $examIds = array_map('intval', explode(',', $request->exam_ids));
        $studentIds = array_map('intval', explode(',', $request->student_ids));

        $students = Student::whereIn('id', $studentIds)->with('section', 'program')->get();
        $results = $students->map(fn ($student) => $this->resultService->build($student, $examIds));

        $pdf = Pdf::loadView('results.pdf', compact('results'))->setPaper('a4', 'portrait');
        return $pdf->stream('Result.pdf');
    }
}
