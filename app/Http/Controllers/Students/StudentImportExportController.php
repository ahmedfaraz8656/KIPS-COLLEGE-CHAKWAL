<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportExportController extends Controller
{
    // ─── DOWNLOAD EMPTY TEMPLATE ───────────────────────────────────
    public function downloadTemplate()
    {
        $headers = [
            'name', 'father_name', 'whatsapp', 'campus', 'year', 'program', 'section',
            'cnic_bform', 'dob', 'alternate_phone', 'address', 'previous_school',
            'ninth_board', 'ninth_total_marks', 'ninth_obtained_marks',
            'tenth_board', 'tenth_total_marks', 'tenth_obtained_marks',
        ];

        $sample = [
            'Ahmad Ali Khan', 'Muhammad Ali', '03001234567', 'boys', 'first', 'ICS', 'PCB1',
            '12345-1234567-1', '2009-05-10', '03009876543', 'Chakwal City', 'Govt High School Chakwal',
            'BISE Rawalpindi', '1100', '920',
            'BISE Rawalpindi', '1100', '950',
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $sample);
            fclose($handle);
        }, 'student-admission-template.csv');
    }

    // ─── UPLOAD + PREVIEW ──────────────────────────────────────────
    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:5120']);

        $import = new StudentsImport;
        Excel::import($import, $request->file('file'));

        return response()->json([
            'success'  => true,
            'imported' => $import->imported,
            'skipped'  => $import->skipped,
            'message'  => count($import->imported).' student(s) imported successfully. '
                        . count($import->skipped).' skipped (errors).',
        ]);
    }

    // ─── EXPORT (with chosen columns) ─────────────────────────────
    public function export(Request $request)
    {
        $request->validate([
            'columns' => 'required|array|min:1',
            'ids'     => 'nullable|array',
        ]);

        $query = Student::with(['program', 'section'])->where('is_demo', false);

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        } else {
            if ($request->filled('campus') && $request->campus !== 'all') $query->where('campus', $request->campus);
            if ($request->filled('year') && $request->year !== 'all') $query->where('year', $request->year);
            if ($request->filled('section_id') && $request->section_id !== 'all') $query->where('section_id', $request->section_id);
        }

        $students = $query->get();

        $filename = 'kips-students-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new StudentsExport($request->columns, $students), $filename);
    }
}
