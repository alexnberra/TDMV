<?php

namespace App\Http\Requests\Api\Benefits;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisabilityPlacardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'placard_type' => ['required', 'string', Rule::in(['temporary', 'permanent', 'veteran_disabled'])],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected', 'expired', 'revoked'])],
            'issued_at' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'rejection_reason' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
