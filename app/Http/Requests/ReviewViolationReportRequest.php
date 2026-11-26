<?php

namespace App\Http\Requests;

use App\Models\ViolationReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewViolationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins review reports (the route middleware enforces this too).
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'decision' => [
                'required',
                Rule::in([ViolationReport::STATUS_APPROVED, ViolationReport::STATUS_REJECTED]),
            ],

            // Remarks are optional for an approval, but required when rejecting
            // so the officer can see why.
            'remarks' => [
                Rule::requiredIf($this->input('decision') === ViolationReport::STATUS_REJECTED),
                'nullable',
                'string',
                'max:2000',
            ],
            'sanction_title' => [
                'nullable',
                'required_with:sanction_description,sanction_due_at',
                'string',
                'max:150',
            ],
            'sanction_description' => ['nullable', 'string', 'max:2000'],
            'sanction_due_at' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'decision.required' => 'Please choose Approve or Reject.',
            'decision.in' => 'Invalid decision.',
            'remarks.required' => 'Please give a reason (remarks) when rejecting a report.',
        ];
    }
}
