<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Tribe;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'case_number' => null,
            'user_id' => User::factory(),
            'tribe_id' => Tribe::factory(),
            'vehicle_id' => Vehicle::factory(),
            'service_type' => fake()->randomElement([
                'renewal',
                'new_registration',
                'title_transfer',
                'plate_replacement',
                'duplicate_title',
            ]),
            'status' => fake()->randomElement([
                'draft',
                'submitted',
                'under_review',
                'info_requested',
                'approved',
                'completed',
            ]),
            'priority' => fake()->randomElement(['normal', 'normal', 'high', 'urgent']),
            'submitted_at' => now()->subDays(fake()->numberBetween(0, 20)),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'completed_at' => null,
            'estimated_completion_date' => now()->addDays(fake()->numberBetween(1, 12))->toDateString(),
            'vehicle_data' => [
                'vin' => strtoupper(fake()->bothify('#################')),
                'odometer' => fake()->numberBetween(1000, 200000),
            ],
            'requirements_data' => [
                'insurance_verified' => fake()->boolean(70),
                'inspection_verified' => fake()->boolean(50),
            ],
            'reviewer_notes' => null,
            'rejection_reason' => null,
        ];
    }
}
