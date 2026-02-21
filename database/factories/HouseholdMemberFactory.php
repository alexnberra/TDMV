<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HouseholdMember>
 */
class HouseholdMemberFactory extends Factory
{
    protected $model = HouseholdMember::class;

    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'user_id' => User::factory(),
            'relationship_type' => fake()->randomElement(['spouse', 'child', 'guardian', 'parent', 'sibling', 'other']),
            'is_primary' => false,
            'can_manage_minor_vehicles' => fake()->boolean(40),
            'is_minor' => fake()->boolean(30),
            'date_joined' => now()->subDays(fake()->numberBetween(1, 1500))->toDateString(),
            'metadata' => [
                'seeded' => true,
            ],
        ];
    }
}
