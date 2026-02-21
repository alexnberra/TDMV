<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\Tribe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'tribe_id' => Tribe::factory(),
            'category' => fake()->randomElement([
                'Registration & Renewals',
                'Titles',
                'Payments',
                'Appointments',
            ]),
            'question' => fake()->sentence(10),
            'answer' => fake()->paragraph(3),
            'order' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }
}
