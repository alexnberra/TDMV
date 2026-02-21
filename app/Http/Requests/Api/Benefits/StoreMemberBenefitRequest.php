<?php

namespace App\Http\Requests\Api\Benefits;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberBenefitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'benefit_type' => ['required', 'string', Rule::in(['elder', 'veteran', 'disabled', 'military_active'])],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'active', 'rejected', 'expired'])],
            'effective_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
