<?php

namespace Database\Factories;

use App\Models\DisabilityPlacard;
use App\Models\Tribe;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisabilityPlacard>
 */
class DisabilityPlacardFactory extends Factory
{
    protected $model = DisabilityPlacard::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'approved', 'rejected', 'expired']);
        $issuedDate = now()->subDays(fake()->numberBetween(1, 180));

        return [
            'user_id' => User::factory(),
            'tribe_id' => Tribe::factory(),
            'vehicle_id' => fake()->boolean(70) ? Vehicle::factory() : null,
            'placard_number' => $status === 'approved' ? strtoupper(fake()->bothify('DP-####-####')) : null,
            'placard_type' => fake()->randomElement(['temporary', 'permanent', 'veteran_disabled']),
            'status' => $status,
            'issued_at' => $status === 'approved' ? $issuedDate->toDateString() : null,
            'expiration_date' => $status === 'approved' ? (clone $issuedDate)->addYear()->toDateString() : null,
            'approved_by' => null,
            'approved_at' => $status === 'approved' ? now()->subDays(fake()->numberBetween(1, 90)) : null,
            'rejection_reason' => $status === 'rejected' ? 'Additional medical verification required.' : null,
            'metadata' => [
                'requested_channel' => fake()->randomElement(['online', 'office']),
            ],
        ];
    }
}
