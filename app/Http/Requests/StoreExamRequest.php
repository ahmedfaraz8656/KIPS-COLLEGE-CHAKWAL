<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create exam');
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:100'],
            'type'              => ['required', 'string', 'max:50'],
            'sequence'          => ['nullable', 'integer', 'min:1', 'max:10'],
            'exam_date'         => ['required', 'date'],
            'campus_scope'      => ['required', 'in:boys,girls,both'],
            'year_scope'        => ['required', 'in:first,second,both'],
            'description'       => ['nullable', 'string'],
            'grading_template_id' => ['nullable', 'exists:grading_templates,id'],
            'marks_due_date'    => ['nullable', 'date', 'after_or_equal:exam_date'],
            // subject_marks structure (one set per Program+Year tab configured):
            // [{program_id, year, subject_id, total_marks}, ...]
            'subject_marks'     => ['required', 'array', 'min:1'],
            'subject_marks.*.program_id'  => ['required', 'exists:programs,id'],
            'subject_marks.*.year'        => ['required', 'in:first,second'],
            'subject_marks.*.subject_id'  => ['required', 'exists:subjects,id'],
            'subject_marks.*.total_marks' => ['required', 'integer', 'min:1'],
        ];
    }
}
