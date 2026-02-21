<?php

namespace Database\Factories;

use App\Models\OfficeLocation;
use App\Models\Tribe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficeLocation>
 */
class OfficeLocationFactory extends Factory
{
    protected $model = OfficeLocation::class;

    public function definition(): array
    {
        return [
            'tribe_id' => Tribe::factory(),
            'name' => fake()->randomElement(['Main Office', 'North Branch', 'South Branch']),
            'address' => fake()->address(),
            'phone' => fake()->numerify('(555) ###-####'),
            'email' => fake()->companyEmail(),
            'hours' => [
                'monday' => '08:00-17:00',
                'tuesday' => '08:00-17:00',
                'wednesday' => '08:00-17:00',
                'thursday' => '08:00-17:00',
                'friday' => '08:00-17:00',
            ],
            'is_active' => true,
            'latitude' => fake()->latitude(30, 49),
            'longitude' => fake()->longitude(-124, -67),
            'current_wait_time' => fake()->numberBetween(5, 75),
        ];
    }
}
