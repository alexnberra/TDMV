<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $extension = fake()->randomElement(['pdf', 'jpg', 'png']);
        $name = fake()->uuid().'.'.$extension;

        return [
            'application_id' => Application::factory(),
            'user_id' => User::factory(),
            'document_type' => fake()->randomElement([
                'insurance',
                'title',
                'tribal_id',
                'drivers_license',
                'inspection',
                'proof_of_residency',
                'other',
            ]),
            'file_name' => $name,
            'file_path' => "documents/seed/{$name}",
            'file_size' => fake()->numberBetween(25_000, 1_500_000),
            'mime_type' => $extension === 'pdf' ? 'application/pdf' : "image/{$extension}",
            'uploaded_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'status' => fake()->randomElement(['uploaded', 'accepted', 'rejected']),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'rejection_reason' => null,
            'expiration_date' => fake()->boolean(35) ? now()->addMonths(fake()->numberBetween(1, 24))->toDateString() : null,
            'metadata' => [
                'source' => 'seed',
            ],
        ];
    }
}
