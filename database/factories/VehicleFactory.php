<?php

namespace Database\Factories;

use App\Models\Tribe;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $registrationDate = now()->subMonths(fake()->numberBetween(2, 18));
        $expirationDate = (clone $registrationDate)->addYear();

        return [
            'owner_id' => User::factory(),
            'tribe_id' => Tribe::factory(),
            'vin' => strtoupper(fake()->unique()->bothify('#################')),
            'plate_number' => strtoupper(fake()->unique()->bothify('???####')),
            'year' => fake()->numberBetween(1998, (int) date('Y') + 1),
            'make' => fake()->randomElement(['Ford', 'Chevrolet', 'Toyota', 'Honda', 'Nissan', 'Jeep', 'Ram']),
            'model' => fake()->randomElement(['F-150', 'Silverado', 'Camry', 'Civic', 'Altima', 'Cherokee', '1500']),
            'color' => fake()->safeColorName(),
            'vehicle_type' => fake()->randomElement(['car', 'truck', 'suv', 'motorcycle', 'rv', 'trailer', 'commercial']),
            'registration_status' => fake()->randomElement(['active', 'pending', 'expired']),
            'registration_date' => $registrationDate->toDateString(),
            'expiration_date' => $expirationDate->toDateString(),
            'title_number' => strtoupper(fake()->bothify('TIT-######')),
            'lienholder_name' => fake()->boolean(35) ? fake()->company() : null,
            'lienholder_address' => fake()->boolean(35) ? fake()->address() : null,
            'is_garaged_on_reservation' => fake()->boolean(75),
            'mileage' => fake()->numberBetween(1500, 180000),
            'metadata' => [
                'fuel_type' => fake()->randomElement(['gas', 'diesel', 'hybrid', 'electric']),
                'weight_class' => fake()->randomElement(['light', 'medium', 'heavy']),
            ],
        ];
    }
}
