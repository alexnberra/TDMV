<?php

namespace Database\Factories;

use App\Models\MemberBenefit;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberBenefit>
 */
class MemberBenefitFactory extends Factory
{
    protected $model = MemberBenefit::class;

    public function definition(): array
    {
        $effectiveDate = now()->subDays(fake()->numberBetween(1, 180));
        $status = fake()->randomElement(['pending', 'active', 'rejected', 'expired']);

        return [
            'user_id' => User::factory(),
            'tribe_id' => Tribe::factory(),
            'benefit_type' => fake()->randomElement(['elder', 'veteran', 'disabled', 'military_active']),
            'status' => $status,
            'effective_date' => $status === 'active' ? $effectiveDate->toDateString() : null,
            'expiration_date' => $status === 'active' ? (clone $effectiveDate)->addYear()->toDateString() : null,
            'verified_by' => null,
            'verified_at' => $status === 'active' ? now()->subDays(fake()->numberBetween(1, 90)) : null,
            'notes' => $status === 'rejected' ? 'Manual review failed verification checks.' : null,
            'metadata' => [
                'source' => fake()->randomElement(['self_service', 'staff_created']),
            ],
        ];
    }
}
