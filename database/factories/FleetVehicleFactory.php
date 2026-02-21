<?php

namespace Database\Factories;

use App\Models\BusinessAccount;
use App\Models\FleetVehicle;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FleetVehicle>
 */
class FleetVehicleFactory extends Factory
{
    protected $model = FleetVehicle::class;

    public function definition(): array
    {
        return [
            'business_account_id' => BusinessAccount::factory(),
            'vehicle_id' => Vehicle::factory(),
            'assigned_driver_id' => fake()->boolean(65) ? User::factory() : null,
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
            'added_at' => now()->subDays(fake()->numberBetween(1, 720)),
            'metadata' => [
                'department' => fake()->randomElement(['transport', 'maintenance', 'public-works', 'support']),
            ],
        ];
    }
}
