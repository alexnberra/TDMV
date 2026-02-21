<?php

namespace App\Http\Requests\Api\Benefits;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberBenefitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['pending', 'active', 'rejected', 'expired'])],
            'effective_date' => ['sometimes', 'nullable', 'date'],
            'expiration_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:effective_date'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
