<?php

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('runs the end-to-end member and admin smoke flow', function () {
    Storage::fake('local');
    config()->set('filesystems.default', 'local');
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $member = User::where('email', 'john@example.com')->firstOrFail();
    Sanctum::actingAs($member);

    $createResponse = $this->postJson('/api/applications', [
        'service_type' => 'renewal',
        'vehicle_id' => 2,
        'vehicle_data' => [
            'vin' => '1C4RJFBG6FC625472',
            'make' => 'Jeep',
            'model' => 'Grand Cherokee',
            'year' => 2022,
        ],
        'requirements_data' => [],
    ]);

    $createResponse->assertCreated();
    $applicationId = $createResponse->json('application.id');

    expect($applicationId)->toBeInt();

    foreach (['insurance', 'title', 'tribal_id'] as $documentType) {
        $uploadResponse = $this->postJson("/api/applications/{$applicationId}/documents", [
            'document_type' => $documentType,
            'file' => UploadedFile::fake()->create("{$documentType}.pdf", 150, 'application/pdf'),
        ]);

        $uploadResponse->assertCreated();
    }

    $paymentResponse = $this->postJson("/api/applications/{$applicationId}/payments", [
        'payment_method' => 'card',
        'payment_token' => 'tok_smoke_test',
        'fee_breakdown' => [
            'registration' => 45,
            'plate' => 15,
            'processing' => 5,
        ],
    ]);
    $paymentResponse->assertCreated();

    $submitResponse = $this->postJson("/api/applications/{$applicationId}/submit", [
        'requirements_data' => [
            'payment_completed' => true,
            'accepted_terms' => true,
        ],
    ]);
    $submitResponse->assertOk()->assertJsonPath('application.status', 'submitted');

    $statusResponse = $this->getJson("/api/applications/{$applicationId}");
    $statusResponse->assertOk()->assertJsonPath('application.id', $applicationId);

    $timelineResponse = $this->getJson("/api/applications/{$applicationId}/timeline");
    $timelineResponse->assertOk();

    $admin = User::where('email', 'admin@tribe.gov')->firstOrFail();
    Sanctum::actingAs($admin);

    $adminStatsResponse = $this->getJson('/api/admin/dashboard/stats');
    $adminStatsResponse->assertOk()->assertJsonStructure([
        'applications_by_status',
        'pending_applications',
        'total_revenue',
        'expiring_soon',
    ]);

    $adminUpdateResponse = $this->putJson("/api/admin/applications/{$applicationId}/status", [
        'status' => 'under_review',
        'reviewer_notes' => 'Smoke test review',
    ]);
    $adminUpdateResponse->assertOk()->assertJsonPath('application.status', 'under_review');

    expect(Application::find($applicationId)?->status)->toBe('under_review');
});
