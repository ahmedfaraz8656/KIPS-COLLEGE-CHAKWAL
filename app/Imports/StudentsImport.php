<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Program;
use App\Models\Section;
use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

/**
 * Required columns: name, father_name, whatsapp, campus, year, program, section
 * Optional columns: cnic_bform, dob, alternate_phone, address, previous_school,
 *                    ninth_board, ninth_total_marks, ninth_obtained_marks,
 *                    tenth_board, tenth_total_marks, tenth_obtained_marks
 */
class StudentsImport implements ToCollection, WithHeadingRow
{
    public array $imported = [];
    public array $skipped = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // account for header row

            // ─── Required field check ──────────────────────────
            $required = ['name', 'father_name', 'whatsapp', 'campus', 'year', 'program', 'section'];
            $missing = [];
            foreach ($required as $field) {
                if (empty($row[$field])) $missing[] = $field;
            }
            if ($missing) {
                $this->skipped[] = ['row' => $rowNumber, 'reason' => 'Missing: '.implode(', ', $missing)];
                continue;
            }

            $campus = strtolower(trim($row['campus'])) === 'girls' ? 'girls' : 'boys';
            $year   = str_contains(strtolower($row['year']), 'sec') ? 'second' : 'first';

            $program = Program::where('code', strtoupper(trim($row['program'])))->first();
            if (!$program) {
                $this->skipped[] = ['row' => $rowNumber, 'reason' => "Unknown program: {$row['program']}"];
                continue;
            }

            $section = Section::where('code', trim($row['section']))
                ->where('campus', $campus)->where('year', $year)->first();
            if (!$section) {
                $this->skipped[] = ['row' => $rowNumber, 'reason' => "Unknown/mismatched section: {$row['section']}"];
                continue;
            }

            // ─── Duplicate check (CNIC or WhatsApp) ────────────
            $whatsapp = preg_replace('/\D/', '', $row['whatsapp']);
            $duplicate = Student::where('whatsapp', $whatsapp)
                ->orWhere(fn ($q) => !empty($row['cnic_bform']) ? $q->where('cnic_bform', $row['cnic_bform']) : $q)
                ->first();
            if ($duplicate) {
                $this->skipped[] = ['row' => $rowNumber, 'reason' => "Duplicate WhatsApp/CNIC — matches existing student {$duplicate->roll_number}"];
                continue;
            }

            $rollNumber = Student::generateRollNumber($campus, $year, $program);

            $student = Student::create([
                'roll_number'      => $rollNumber,
                'name'             => trim($row['name']),
                'father_name'      => trim($row['father_name']),
                'whatsapp'         => $whatsapp,
                'cnic_bform'       => $row['cnic_bform'] ?? null,
                'dob'              => $row['dob'] ?? null,
                'alternate_phone'  => $row['alternate_phone'] ?? null,
                'address'          => $row['address'] ?? null,
                'previous_school'  => $row['previous_school'] ?? '—',
                'campus'           => $campus,
                'year'             => $year,
                'program_id'       => $program->id,
                'section_id'       => $section->id,
                'enrollment_date'  => now(),
                'ninth_board'           => $row['ninth_board'] ?? null,
                'ninth_total_marks'     => $row['ninth_total_marks'] ?? null,
                'ninth_obtained_marks'  => $row['ninth_obtained_marks'] ?? null,
                'tenth_board'           => $row['tenth_board'] ?? null,
                'tenth_total_marks'     => $row['tenth_total_marks'] ?? null,
                'tenth_obtained_marks'  => $row['tenth_obtained_marks'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            $student->sectionHistory()->create([
                'to_section_id' => $section->id,
                'action'        => 'initial_admission',
                'performed_by'  => auth()->id(),
            ]);

            $this->imported[] = ['row' => $rowNumber, 'roll_number' => $rollNumber, 'name' => $student->name];
        }

        if (count($this->imported) > 0) {
            AuditLog::record('IMPORT', 'Students', count($this->imported).' student(s) imported via Excel.');
        }
    }
}
