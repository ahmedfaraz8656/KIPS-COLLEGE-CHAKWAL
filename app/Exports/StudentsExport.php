<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected array $columns;
    protected $students;

    /**
     * @param array $columns  Which columns the admin chose to export
     * @param mixed $students Collection of Student models (already filtered)
     */
    public function __construct(array $columns, $students)
    {
        $this->columns = $columns;
        $this->students = $students;
    }

    protected array $allColumns = [
        'roll_number'    => 'Roll No',
        'name'           => 'Name',
        'father_name'    => 'Father Name',
        'campus'         => 'Campus',
        'year'           => 'Year',
        'section'        => 'Section',
        'program'        => 'Program',
        'whatsapp'       => 'WhatsApp',
        'ninth_obtained_marks'  => '9th Marks',
        'tenth_obtained_marks'  => '10th Marks',
        'status'         => 'Status',
    ];

    public function collection()
    {
        return collect($this->students);
    }

    public function headings(): array
    {
        return array_map(fn ($key) => $this->allColumns[$key], $this->columns);
    }

    public function map($student): array
    {
        return array_map(function ($col) use ($student) {
            return match ($col) {
                'section' => $student->section?->code,
                'program' => $student->program?->code,
                'year'    => ucfirst($student->year).' Year',
                'campus'  => ucfirst($student->campus),
                'status'  => ucfirst($student->status),
                default   => $student->{$col},
            };
        }, $this->columns);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function title(): string
    {
        return 'KIPS College Students - '.now()->format('d-M-Y');
    }
}
