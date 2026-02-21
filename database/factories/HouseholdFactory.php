<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Household>
 */
class HouseholdFactory extends Factory
{
    protected $model = Household::class;

    public function definition(): array
    {
        return [
            'tribe_id' => Tribe::factory(),
            'owner_user_id' => User::factory(),
            'household_name' => fake()->lastName().' Household',
            'address_line1' => fake()->streetAddress(),
            'address_line2' => fake()->boolean(30) ? fake()->secondaryAddress() : null,
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->postcode(),
            'is_active' => true,
            'metadata' => [
                'seeded' => true,
            ],
        ];
    }
}
