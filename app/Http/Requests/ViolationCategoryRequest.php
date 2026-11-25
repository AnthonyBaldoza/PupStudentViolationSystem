<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ViolationCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Admin-only access is enforced by the route middleware.
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // On update the route has the bound category; on create it is null.
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                // Case-insensitive duplicate check ("Tardiness" == "tardiness").
                function (string $attribute, mixed $value, \Closure $fail) use ($category) {
                    $exists = DB::table('violation_categories')
                        ->whereRaw('lower(name) = ?', [mb_strtolower(trim((string) $value))])
                        ->when($category, fn ($q) => $q->where('id', '!=', $category->id))
                        ->exists();

                    if ($exists) {
                        $fail('A violation category with this name already exists.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }
}