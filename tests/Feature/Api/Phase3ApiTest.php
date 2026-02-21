<?php

use App\Models\Application;
use App\Models\Document;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns assistant responses and stores interaction history', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $member = User::where('email', 'john@example.com')->firstOrFail();
    Sanctum::actingAs($member);

    $response = $this->postJson('/api/assistant/query', [
        'query' => 'What is the status of my latest application?',
        'channel' => 'portal',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'interaction_id',
            'intent',
            'message',
            'data',
            'suggestions',
            'response_time_ms',
        ])
        ->assertJsonPath('intent', 'application_status');

    $this->assertDatabaseHas('assistant_interactions', [
        'user_id' => $member->id,
        'tribe_id' => $member->tribe_id,
    ]);
});

it('returns phase 3 admin insights', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $admin = User::where('email', 'admin@tribe.gov')->firstOrFail();
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/phase3/insights');

    $response->assertOk()
        ->assertJsonStructure([
            'applications' => ['by_status', 'pending_review', 'at_risk_cases'],
            'vehicles' => ['expiring_within_30_days', 'expiring_within_7_days', 'expired_active'],
            'appointments' => ['next_14_days_total', 'next_14_days_by_day'],
            'assistant' => ['interactions_today', 'interactions_last_7_days'],
            'automation' => ['active_rules', 'rules', 'dry_run_preview'],
        ]);
});

it('dry-runs and applies phase 3 automation for simple renewal approvals', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $member = User::where('email', 'john@example.com')->firstOrFail();

    $application = Application::create([
        'user_id' => $member->id,
        'tribe_id' => $member->tribe_id,
        'vehicle_id' => 2,
        'service_type' => 'renewal',
        'status' => 'submitted',
        'submitted_at' => now()->subDay(),
        'vehicle_data' => [
            'vin' => '1C4RJFBG6FC625472',
            'make' => 'Jeep',
            'model' => 'Grand Cherokee',
            'year' => 2022,
        ],
        'requirements_data' => [
            'insurance_verified' => true,
            'inspection_verified' => true,
        ],
    ]);

    foreach (['insurance', 'title', 'tribal_id'] as $index => $documentType) {
        Document::create([
            'application_id' => $application->id,
            'user_id' => $member->id,
            'document_type' => $documentType,
            'file_name' => "{$documentType}-phase3-{$index}.pdf",
            'file_path' => "documents/tests/{$application->id}/{$documentType}.pdf",
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now()->subHours(3),
            'status' => 'accepted',
            'metadata' => ['source' => 'phase3-test'],
        ]);
    }

    Payment::create([
        'application_id' => $application->id,
        'user_id' => $member->id,
        'tribe_id' => $member->tribe_id,
        'transaction_id' => 'TXN-PHASE3-TEST-001',
        'payment_method' => 'card',
        'amount' => 65.00,
        'fee_breakdown' => [
            'registration' => 45.00,
            'plate' => 15.00,
            'processing' => 5.00,
        ],
        'status' => 'completed',
        'payment_gateway' => 'stripe',
        'gateway_response' => ['approved' => true],
        'paid_at' => now()->subHours(2),
    ]);

    $admin = User::where('email', 'admin@tribe.gov')->firstOrFail();
    Sanctum::actingAs($admin);

    $dryRunResponse = $this->postJson('/api/admin/phase3/automation/run', [
        'dry_run' => true,
    ]);

    $dryRunResponse->assertOk()
        ->assertJsonPath('dry_run', true);

    $dryRunRule = collect($dryRunResponse->json('results'))
        ->firstWhere('rule_key', 'auto_approve_simple_renewals');

    expect($dryRunRule)->not->toBeNull();
    expect($dryRunRule['matched_application_ids'])->toContain($application->id);

    $applyResponse = $this->postJson('/api/admin/phase3/automation/run', [
        'dry_run' => false,
    ]);

    $applyResponse->assertOk()
        ->assertJsonPath('dry_run', false);

    $application->refresh();
    expect($application->status)->toBe('approved');

    $this->assertDatabaseHas('application_timeline', [
        'application_id' => $application->id,
        'event_type' => 'workflow_auto_approved',
    ]);
});
