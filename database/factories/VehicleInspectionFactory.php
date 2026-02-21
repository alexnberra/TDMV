<?php

namespace Database\Factories;

use App\Models\Tribe;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleInspection>
 */
class VehicleInspectionFactory extends Factory
{
    protected $model = VehicleInspection::class;

    public function definition(): array
    {
        $inspectionDate = now()->subDays(fake()->numberBetween(1, 365));
        $result = fake()->randomElement(['pass', 'fail', 'conditional', 'pending']);

        return [
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),
            'tribe_id' => Tribe::factory(),
            'inspection_date' => $inspectionDate->toDateString(),
            'result' => $result,
            'facility_name' => fake()->company().' Safety Inspection',
            'certificate_number' => strtoupper(fake()->bothify('INSP-######')),
            'expires_at' => $result === 'pass' ? (clone $inspectionDate)->addYear()->toDateString() : null,
            'notes' => $result === 'fail' ? 'Corrective maintenance required before approval.' : null,
            'metadata' => [
                'inspector_id' => strtoupper(fake()->bothify('I-####')),
            ],
        ];
    }
}
