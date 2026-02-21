<?php

namespace App\Http\Requests\Api\Fleet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignFleetVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'assigned_driver_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'maintenance'])],
            'added_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
