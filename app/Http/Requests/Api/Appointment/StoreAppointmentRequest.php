<?php

namespace App\Http\Requests\Api\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'household_id' => ['nullable', 'integer', 'exists:households,id'],
            'office_location_id' => ['nullable', 'integer', 'exists:office_locations,id'],
            'appointment_type' => [
                'required',
                'string',
                Rule::in([
                    'dmv_office_visit',
                    'road_test',
                    'vehicle_inspection',
                    'photo_signature_update',
                    'document_review',
                    'title_signing',
                    'plate_pickup',
                    'virtual_consultation',
                ]),
            ],
            'scheduled_for' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['sometimes', 'integer', 'min:10', 'max:240'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
