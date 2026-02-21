<?php

namespace App\Http\Requests\Api\Compliance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmissionsTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'test_date' => ['required', 'date'],
            'result' => ['required', 'string', Rule::in(['pending', 'pass', 'fail', 'waived'])],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:test_date'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
