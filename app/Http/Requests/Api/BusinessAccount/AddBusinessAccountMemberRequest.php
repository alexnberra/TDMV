<?php

namespace App\Http\Requests\Api\BusinessAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddBusinessAccountMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'string', Rule::in(['owner', 'manager', 'billing', 'viewer', 'driver'])],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
