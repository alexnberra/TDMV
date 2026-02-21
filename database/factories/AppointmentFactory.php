<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\OfficeLocation;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $scheduledFor = now()->addDays(fake()->numberBetween(1, 45));

        return [
            'tribe_id' => Tribe::factory(),
            'user_id' => User::factory(),
            'household_id' => null,
            'office_location_id' => OfficeLocation::factory(),
            'appointment_type' => fake()->randomElement([
                'dmv_office_visit',
                'road_test',
                'vehicle_inspection',
                'photo_signature_update',
                'document_review',
                'title_signing',
                'plate_pickup',
                'virtual_consultation',
            ]),
            'status' => fake()->randomElement(['requested', 'confirmed', 'rescheduled']),
            'scheduled_for' => $scheduledFor,
            'duration_minutes' => fake()->randomElement([20, 30, 45, 60]),
            'check_in_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'cancelled_by' => null,
            'cancel_reason' => null,
            'notes' => fake()->boolean(45) ? fake()->sentence() : null,
            'confirmation_code' => strtoupper(Str::random(8)),
            'metadata' => ['seeded' => true],
        ];
    }
}
