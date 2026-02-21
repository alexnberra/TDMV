<?php

namespace Database\Factories;

use App\Models\InsurancePolicy;
use App\Models\Tribe;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InsurancePolicy>
 */
class InsurancePolicyFactory extends Factory
{
    protected $model = InsurancePolicy::class;

    public function definition(): array
    {
        $effectiveDate = now()->subDays(fake()->numberBetween(1, 300));

        return [
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),
            'tribe_id' => Tribe::factory(),
            'provider_name' => fake()->randomElement(['Tribal Mutual', 'Blue Shield Auto', 'Reservation Assurance']),
            'policy_number' => strtoupper(fake()->bothify('POL-########')),
            'effective_date' => $effectiveDate->toDateString(),
            'expiration_date' => (clone $effectiveDate)->addYear()->toDateString(),
            'status' => fake()->randomElement(['pending', 'active', 'lapsed', 'expired']),
            'is_verified' => fake()->boolean(60),
            'verified_at' => fake()->boolean(60) ? now()->subDays(fake()->numberBetween(1, 90)) : null,
            'verified_by' => null,
            'verification_source' => fake()->randomElement(['manual_upload', 'carrier_api']),
            'metadata' => [
                'coverage' => fake()->randomElement(['liability', 'full', 'commercial']),
            ],
        ];
    }
}
