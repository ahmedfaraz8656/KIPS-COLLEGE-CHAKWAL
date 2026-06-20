<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage teachers');
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher')?->id;

        return [
            'name'            => ['required', 'string', 'max:150'],
            'father_name'     => ['nullable', 'string', 'max:150'],
            'cnic'            => ['nullable', 'string', 'max:20'],
            'whatsapp'        => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'email'           => ['required', 'email', Rule::unique('teachers', 'email')->ignore($teacherId)],
            'date_of_joining' => ['nullable', 'date'],
            'gender'          => ['required', 'in:male,female'],
            'qualification'   => ['nullable', 'string', 'max:150'],
            'photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'campus_access'   => ['required', 'in:boys,girls,both'],
            'status'          => ['nullable', 'boolean'],
            'roles'           => ['nullable', 'array'],
            'roles.*'         => ['exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => "Teacher's name is required.",
            'whatsapp.required' => 'WhatsApp number is required.',
            'email.required'    => 'Email is required for system login.',
            'email.unique'      => 'This email is already used by another teacher.',
            'gender.required'   => 'Please select Male or Female.',
        ];
    }
}
