<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $courses = config('school.courses', []);
        $yearLevels = config('school.year_levels', []);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],

            // JPG/PNG only (no SVG, which can carry scripts). 3 MB max.
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:3072'],
            'remove_photo' => ['nullable', 'boolean'],

            // Both roles
            'contact_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'position' => ['nullable', 'string', 'max:100'],
        ];

        // Each role can only send ITS OWN fields; anything else is ignored.
        if ($this->user()->isAdmin()) {
            $rules += [
                'department' => ['nullable', 'string', 'max:100'],
                'handled_courses' => ['nullable', 'array'],
                'handled_courses.*' => ['string', Rule::in($courses)],
                'handled_year_levels' => ['nullable', 'array'],
                'handled_year_levels.*' => ['string', Rule::in($yearLevels)],
            ];
        } else {
            $rules += [
                'student_number' => ['nullable', 'string', 'max:50'],
                'course' => ['nullable', 'string', Rule::in($courses)],
                'year_level' => ['nullable', 'string', Rule::in($yearLevels)],
                'section' => ['nullable', 'string', 'max:50'],
            ];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'photo.uploaded' => 'The photo failed to upload. It may be larger than the server allows.',
            'photo.max' => 'The photo must not be larger than 3 MB.',
            'photo.mimes' => 'The photo must be a JPG or PNG file.',
            'contact_number.regex' => 'The contact number may only contain digits, spaces, + - and parentheses.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'student_number' => 'Student ID',
            'year_level' => 'year level',
            'contact_number' => 'contact number',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => trim((string) $this->input('email')),
        ]);
    }
}