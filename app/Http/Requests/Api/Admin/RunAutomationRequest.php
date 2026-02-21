<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RunAutomationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'dry_run' => ['sometimes', 'boolean'],
            'rule_keys' => ['nullable', 'array'],
            'rule_keys.*' => ['string', 'max:120'],
        ];
    }
}
