<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage students');
    }

    public function rules(): array
    {
        return [
            // Step 1 — Personal Info
            'name'              => ['required', 'string', 'max:150'],
            'father_name'       => ['required', 'string', 'max:150'],
            'dob'               => ['nullable', 'date', 'before:today'],
            'cnic_bform'        => ['nullable', 'string', 'max:20'],
            'whatsapp'          => ['required', 'string', 'max:20'],
            'alternate_phone'   => ['nullable', 'string', 'max:20'],
            'address'           => ['nullable', 'string', 'max:500'],
            'previous_school'   => ['required', 'string', 'max:150'],
            'photo'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            // Step 2 — 9th Class
            'ninth_board'            => ['nullable', 'string', 'max:100'],
            'ninth_roll_no'          => ['nullable', 'string', 'max:50'],
            'ninth_year'             => ['nullable', 'digits:4'],
            'ninth_total_marks'      => ['nullable', 'integer', 'min:0', 'max:2000'],
            'ninth_obtained_marks'   => ['nullable', 'integer', 'min:0', 'lte:ninth_total_marks'],
            'ninth_stream'           => ['nullable', Rule::in(['science', 'arts', 'computer'])],

            // Step 2 — 10th Class
            'tenth_board'            => ['nullable', 'string', 'max:100'],
            'tenth_roll_no'          => ['nullable', 'string', 'max:50'],
            'tenth_year'             => ['nullable', 'digits:4'],
            'tenth_total_marks'      => ['nullable', 'integer', 'min:0', 'max:2000'],
            'tenth_obtained_marks'   => ['nullable', 'integer', 'min:0', 'lte:tenth_total_marks'],
            'tenth_stream'           => ['nullable', Rule::in(['science', 'arts', 'computer'])],

            // Step 3 — Enrollment
            'campus'        => ['required', Rule::in(['boys', 'girls'])],
            'year'          => ['required', Rule::in(['first', 'second'])],
            'program_id'    => ['required', 'exists:programs,id'],
            'section_id'    => ['required', 'exists:sections,id'],
            'enrollment_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Student name is required.',
            'father_name.required'     => "Father's name is required.",
            'whatsapp.required'        => 'WhatsApp number is required.',
            'previous_school.required' => 'Previous school name is required.',
            'campus.required'          => 'Please select a campus.',
            'year.required'            => 'Please select an academic year.',
            'program_id.required'      => 'Please select a program.',
            'section_id.required'      => 'Please select a section.',
            'ninth_obtained_marks.lte'  => 'Obtained marks cannot exceed total marks (9th).',
            'tenth_obtained_marks.lte'  => 'Obtained marks cannot exceed total marks (10th).',
        ];
    }

    /**
     * STRICT RULE: FAIT program only available for Girls campus,
     * except the special FAIT-B1/FAIT-B2 sections explicitly seeded.
     * Cross-validate that the selected section actually matches the
     * selected campus + year + program — prevents any mismatch from
     * a tampered/stale dropdown.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $section = \App\Models\Section::find($this->section_id);

            if ($section) {
                if ($section->campus !== $this->campus) {
                    $validator->errors()->add('section_id', 'Selected section does not belong to the chosen campus.');
                }
                if ($section->year !== $this->year) {
                    $validator->errors()->add('section_id', 'Selected section does not belong to the chosen year.');
                }
                if ((int) $section->program_id !== (int) $this->program_id) {
                    $validator->errors()->add('section_id', 'Selected section does not belong to the chosen program.');
                }
            }
        });
    }
}
