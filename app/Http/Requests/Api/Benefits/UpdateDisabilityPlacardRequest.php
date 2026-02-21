<?php

namespace App\Http\Requests\Api\Benefits;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisabilityPlacardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected', 'expired', 'revoked'])],
            'issued_at' => ['sometimes', 'nullable', 'date'],
            'expiration_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:issued_at'],
            'rejection_reason' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
