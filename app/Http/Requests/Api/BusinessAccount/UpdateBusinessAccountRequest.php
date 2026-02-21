<?php

namespace App\Http\Requests\Api\BusinessAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['sometimes', 'string', 'max:255'],
            'business_type' => ['sometimes', 'string', Rule::in(['tribal_business', 'commercial', 'fleet', 'non_profit'])],
            'tax_id' => ['sometimes', 'nullable', 'string', 'max:100'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'address' => ['sometimes', 'nullable', 'string'],
            'tax_exempt' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
