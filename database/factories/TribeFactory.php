<?php

namespace Database\Factories;

use App\Models\Tribe;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tribe>
 */
class TribeFactory extends Factory
{
    protected $model = Tribe::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Tribal Nation';
        $slugBase = Str::slug($name);
        $code = strtoupper(Str::of($slugBase)->replace('-', '')->substr(0, 3).fake()->numberBetween(1, 9));

        return [
            'name' => $name,
            'slug' => $slugBase.'-'.fake()->unique()->numberBetween(10, 99),
            'code' => $code,
            'logo_url' => null,
            'primary_color' => fake()->safeHexColor(),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->numerify('(555) ###-####'),
            'address' => fake()->address(),
            'is_active' => true,
            'settings' => [
                'fees' => [
                    'registration' => fake()->randomFloat(2, 30, 90),
                    'plate' => fake()->randomFloat(2, 10, 25),
                    'processing' => fake()->randomFloat(2, 3, 12),
                ],
            ],
        ];
    }
}
