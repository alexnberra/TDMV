<?php

namespace Database\Factories;

use App\Models\EmissionsTest;
use App\Models\Tribe;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmissionsTest>
 */
class EmissionsTestFactory extends Factory
{
    protected $model = EmissionsTest::class;

    public function definition(): array
    {
        $testDate = now()->subDays(fake()->numberBetween(1, 365));
        $result = fake()->randomElement(['pass', 'fail', 'waived', 'pending']);

        return [
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),
            'tribe_id' => Tribe::factory(),
            'test_date' => $testDate->toDateString(),
            'result' => $result,
            'facility_name' => fake()->company().' Emissions',
            'certificate_number' => strtoupper(fake()->bothify('EM-######')),
            'expires_at' => $result === 'pass' ? (clone $testDate)->addYear()->toDateString() : null,
            'notes' => $result === 'fail' ? 'Retest required due to emissions threshold.' : null,
            'metadata' => [
                'machine_id' => strtoupper(fake()->bothify('EQ-###')),
            ],
        ];
    }
}
