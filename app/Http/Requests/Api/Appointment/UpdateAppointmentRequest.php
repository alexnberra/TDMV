<?php

namespace App\Http\Requests\Api\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'office_location_id' => ['sometimes', 'nullable', 'integer', 'exists:office_locations,id'],
            'appointment_type' => [
                'sometimes',
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
            'status' => ['sometimes', 'string', Rule::in(['requested', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show', 'rescheduled'])],
            'scheduled_for' => ['sometimes', 'date', 'after:now'],
            'duration_minutes' => ['sometimes', 'integer', 'min:10', 'max:240'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
