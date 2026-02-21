<?php

namespace Database\Factories;

use App\Models\AssistantInteraction;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssistantInteraction>
 */
class AssistantInteractionFactory extends Factory
{
    protected $model = AssistantInteraction::class;

    public function definition(): array
    {
        return [
            'tribe_id' => Tribe::factory(),
            'user_id' => User::factory(),
            'application_id' => null,
            'channel' => 'portal',
            'intent' => fake()->randomElement([
                'general_help',
                'application_status',
                'renewal_help',
                'appointment_help',
                'household_help',
                'payment_help',
            ]),
            'query_text' => fake()->sentence(),
            'response_text' => fake()->paragraph(),
            'context' => ['source' => 'factory'],
            'response_time_ms' => fake()->numberBetween(25, 350),
            'was_helpful' => null,
            'metadata' => ['seeded' => true],
        ];
    }
}
