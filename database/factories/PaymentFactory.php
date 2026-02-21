<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Payment;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $registration = fake()->randomFloat(2, 35, 85);
        $plate = fake()->randomFloat(2, 10, 25);
        $processing = fake()->randomFloat(2, 3, 10);
        $amount = $registration + $plate + $processing;

        return [
            'application_id' => Application::factory(),
            'user_id' => User::factory(),
            'tribe_id' => Tribe::factory(),
            'transaction_id' => 'TXN-'.strtoupper(Str::random(12)),
            'payment_method' => fake()->randomElement(['card', 'ach']),
            'amount' => number_format($amount, 2, '.', ''),
            'fee_breakdown' => [
                'registration' => $registration,
                'plate' => $plate,
                'processing' => $processing,
            ],
            'status' => fake()->randomElement(['completed', 'completed', 'processing', 'failed']),
            'payment_gateway' => 'stripe',
            'gateway_response' => ['seeded' => true, 'success' => true],
            'paid_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'refunded_at' => null,
            'refund_amount' => null,
            'refund_reason' => null,
        ];
    }
}
