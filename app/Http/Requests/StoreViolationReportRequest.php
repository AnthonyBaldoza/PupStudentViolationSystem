<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreViolationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOfficer() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],

            // Only active categories can be chosen.
            'violation_category_id' => [
                'required',
                'integer',
                Rule::exists('violation_categories', 'id')->where('is_active', true),
            ],

            'incident_at' => ['required', 'date', 'before_or_equal:now'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],

            // Optional single evidence file: 5 MB max (value is in kilobytes).
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Please search for and select a student.',
            'student_id.exists' => 'The selected student could not be found.',
            'violation_category_id.required' => 'Please select a violation category.',
            'violation_category_id.exists' => 'The selected violation category is not available.',
            'incident_at.before_or_equal' => 'The incident date and time cannot be in the future.',
            'description.min' => 'Please describe the incident in at least 10 characters.',
            'evidence.uploaded' => 'The evidence file failed to upload. It may be larger than the server allows.',
            'evidence.max' => 'The evidence file must not be larger than 5 MB.',
            'evidence.mimes' => 'Evidence must be a JPG, PNG or PDF file.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'incident_at' => 'incident date and time',
            'violation_category_id' => 'violation category',
        ];
    }
}