<?php

namespace App\Http\Controllers\Results;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Student;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RollSlipController extends Controller
{
    public function index()
    {
        $exams = Exam::where('is_demo', false)->orderByDesc('exam_date')->get();
        return view('rollslips.index', compact('exams'));
    }

    public function generatePdf(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_ids' => 'required|array|min:1',
        ]);

        $exam = Exam::findOrFail($request->exam_id);

        $students = Student::whereIn('id', $request->student_ids)
            ->with('section', 'program')
            ->orderBy('roll_number')
            ->get();

        AuditLog::record('GENERATE', 'Roll Slips', "{$students->count()} roll slip(s) generated for {$exam->name}");

        $pdf = Pdf::loadView('rollslips.pdf', compact('students', 'exam'))->setPaper('a4', 'portrait');

        $filename = 'RollSlips_'.str_replace(' ', '_', $exam->name).'.pdf';

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
