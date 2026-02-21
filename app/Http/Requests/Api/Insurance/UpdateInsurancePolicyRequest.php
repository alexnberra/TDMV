<?php

namespace App\Http\Requests\Api\Insurance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInsurancePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'provider_name' => ['sometimes', 'string', 'max:255'],
            'policy_number' => ['sometimes', 'string', 'max:100'],
            'effective_date' => ['sometimes', 'date'],
            'expiration_date' => ['sometimes', 'date', 'after:effective_date'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'active', 'lapsed', 'expired', 'cancelled'])],
            'is_verified' => ['sometimes', 'boolean'],
            'verification_source' => ['sometimes', 'nullable', 'string', 'max:100'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
