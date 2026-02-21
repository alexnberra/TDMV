<?php

namespace App\Http\Requests\Api\Household;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddHouseholdMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'relationship_type' => ['required', 'string', Rule::in(['self', 'spouse', 'child', 'guardian', 'parent', 'sibling', 'other'])],
            'is_primary' => ['sometimes', 'boolean'],
            'can_manage_minor_vehicles' => ['sometimes', 'boolean'],
            'is_minor' => ['sometimes', 'boolean'],
            'date_joined' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
