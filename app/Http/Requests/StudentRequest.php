<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is already restricted by the route middleware (active admin/officer).
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // On update, the route has the bound Student; on create it is null.
        $student = $this->route('student');

        return [
            'student_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')->ignore($student?->id),
            ],
            'full_name' => ['required', 'string', 'max:150'],
            'course' => ['required', 'string', 'max:100'],
            'year_level' => ['required', 'string', 'max:20'],
            'section' => ['nullable', 'string', 'max:50'],

            // Optional photo. JPG/PNG only (no SVG, which can carry scripts). 3 MB max.
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:3072'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'photo.uploaded' => 'The photo failed to upload. It may be larger than the server allows.',
            'photo.max' => 'The photo must not be larger than 3 MB.',
            'photo.mimes' => 'The photo must be a JPG or PNG file.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'student_number' => 'Student ID',
            'full_name' => 'full name',
            'year_level' => 'year level',
        ];
    }
}