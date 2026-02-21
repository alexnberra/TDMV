<?php

namespace App\Http\Requests\Api\Assistant;

use Illuminate\Foundation\Http\FormRequest;

class AssistantQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:3', 'max:500'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'channel' => ['sometimes', 'string', 'max:50'],
            'context' => ['nullable', 'array'],
        ];
    }
}
