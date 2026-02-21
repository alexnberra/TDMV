<?php

namespace Database\Factories;

use App\Models\Tribe;
use App\Models\User;
use App\Models\WorkflowRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowRule>
 */
class WorkflowRuleFactory extends Factory
{
    protected $model = WorkflowRule::class;

    public function definition(): array
    {
        return [
            'tribe_id' => Tribe::factory(),
            'key' => 'rule-'.fake()->unique()->lexify('??????'),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'is_active' => true,
            'config' => [
                'required_documents' => ['insurance', 'title', 'tribal_id'],
                'require_completed_payment' => true,
                'max_vehicle_age_years' => 20,
                'max_batch' => 100,
            ],
            'last_run_at' => null,
            'run_count' => 0,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
