<?php

namespace Database\Factories;

use App\Models\BusinessAccount;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessAccount>
 */
class BusinessAccountFactory extends Factory
{
    protected $model = BusinessAccount::class;

    public function definition(): array
    {
        return [
            'tribe_id' => Tribe::factory(),
            'owner_user_id' => User::factory(),
            'business_name' => fake()->company().' Mobility Services',
            'business_type' => fake()->randomElement(['tribal_business', 'commercial', 'fleet', 'non_profit']),
            'tax_id' => strtoupper(fake()->bothify('##-#######')),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->numerify('(555) ###-####'),
            'address' => fake()->address(),
            'tax_exempt' => fake()->boolean(40),
            'is_active' => true,
            'metadata' => [
                'billing_code' => strtoupper(fake()->bothify('BUS-###')),
                'fleet_size_target' => fake()->numberBetween(2, 35),
            ],
        ];
    }
}
