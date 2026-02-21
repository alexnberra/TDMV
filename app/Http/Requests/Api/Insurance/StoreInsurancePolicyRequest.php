<?php

namespace App\Http\Requests\Api\Insurance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInsurancePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'provider_name' => ['required', 'string', 'max:255'],
            'policy_number' => ['required', 'string', 'max:100'],
            'effective_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date', 'after:effective_date'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'active', 'lapsed', 'expired', 'cancelled'])],
            'verification_source' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
