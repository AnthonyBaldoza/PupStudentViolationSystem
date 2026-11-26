<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateViolationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('report');

        return $this->user()?->can('update', $report) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'violation_category_id' => [
                'required',
                'integer',
                Rule::exists('violation_categories', 'id')->where('is_active', true),
            ],
            'incident_at' => ['required', 'date', 'before_or_equal:now'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'remove_evidence' => ['nullable', 'array'],
            'remove_evidence.*' => ['integer', 'exists:violation_report_evidence,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'incident_at.before_or_equal' => 'The incident date and time cannot be in the future.',
            'description.min' => 'Please describe the incident in at least 10 characters.',
            'evidence.max' => 'You may attach up to 5 evidence files.',
            'evidence.*.uploaded' => 'An evidence file failed to upload. It may be larger than the server allows.',
            'evidence.*.max' => 'Each evidence file must not be larger than 5 MB.',
            'evidence.*.mimes' => 'Evidence must be JPG, PNG or PDF files.',
        ];
    }
}
