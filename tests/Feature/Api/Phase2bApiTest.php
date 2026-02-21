<?php

use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a household and links a family member', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $john = User::where('email', 'john@example.com')->firstOrFail();
    $jane = User::where('email', 'jane@example.com')->firstOrFail();
    Sanctum::actingAs($john);

    $householdResponse = $this->postJson('/api/households', [
        'household_name' => 'Doe Extended Family',
        'city' => 'Reservation',
        'state' => 'ST',
    ]);

    $householdResponse->assertCreated()
        ->assertJsonPath('household.household_name', 'Doe Extended Family');

    $householdId = $householdResponse->json('household.id');

    $addMemberResponse = $this->postJson("/api/households/{$householdId}/members", [
        'user_id' => $jane->id,
        'relationship_type' => 'spouse',
        'is_minor' => false,
    ]);

    $addMemberResponse->assertOk();

    $showResponse = $this->getJson("/api/households/{$householdId}");
    $showResponse->assertOk();
    expect(collect($showResponse->json('household.members'))->pluck('user_id'))->toContain($jane->id);
});

it('creates and cancels an appointment', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $john = User::where('email', 'john@example.com')->firstOrFail();
    Sanctum::actingAs($john);

    $office = OfficeLocation::query()->firstOrFail();

    $createResponse = $this->postJson('/api/appointments', [
        'office_location_id' => $office->id,
        'appointment_type' => 'dmv_office_visit',
        'scheduled_for' => now()->addDays(5)->toIso8601String(),
        'duration_minutes' => 30,
        'notes' => 'Need title transfer review.',
    ]);

    $createResponse->assertCreated();
    $appointmentId = $createResponse->json('appointment.id');

    $cancelResponse = $this->postJson("/api/appointments/{$appointmentId}/cancel", [
        'cancel_reason' => 'Schedule conflict',
    ]);

    $cancelResponse->assertOk()
        ->assertJsonPath('appointment.status', 'cancelled');
});

it('allows staff to confirm a requested appointment while member cannot', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $john = User::where('email', 'john@example.com')->firstOrFail();
    Sanctum::actingAs($john);

    $office = OfficeLocation::query()->firstOrFail();
    $createResponse = $this->postJson('/api/appointments', [
        'office_location_id' => $office->id,
        'appointment_type' => 'document_review',
        'scheduled_for' => now()->addDays(6)->toIso8601String(),
    ]);
    $createResponse->assertCreated();

    $appointmentId = $createResponse->json('appointment.id');

    $memberConfirmResponse = $this->putJson("/api/appointments/{$appointmentId}", [
        'status' => 'confirmed',
    ]);
    $memberConfirmResponse->assertForbidden();

    $admin = User::where('email', 'admin@tribe.gov')->firstOrFail();
    Sanctum::actingAs($admin);

    $adminConfirmResponse = $this->putJson("/api/appointments/{$appointmentId}", [
        'status' => 'confirmed',
    ]);

    $adminConfirmResponse->assertOk()
        ->assertJsonPath('appointment.status', 'confirmed');
});
