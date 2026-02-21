<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a business account and assigns vehicle 2 to fleet', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $member = User::where('email', 'john@example.com')->firstOrFail();
    Sanctum::actingAs($member);

    $createBusinessResponse = $this->postJson('/api/business-accounts', [
        'business_name' => 'Doe Infrastructure Services',
        'business_type' => 'fleet',
    ]);

    $createBusinessResponse->assertCreated()
        ->assertJsonPath('business_account.business_name', 'Doe Infrastructure Services');

    $businessAccountId = $createBusinessResponse->json('business_account.id');

    $assignFleetVehicleResponse = $this->postJson("/api/business-accounts/{$businessAccountId}/fleet-vehicles", [
        'vehicle_id' => 2,
        'status' => 'active',
    ]);

    $assignFleetVehicleResponse->assertCreated()
        ->assertJsonPath('fleet_vehicle.vehicle_id', 2);

    $this->getJson("/api/business-accounts/{$businessAccountId}")
        ->assertOk()
        ->assertJsonPath('business_account.id', $businessAccountId);
});

it('creates insurance and compliance records for vehicle 2', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $member = User::where('email', 'john@example.com')->firstOrFail();
    Sanctum::actingAs($member);

    $this->postJson('/api/insurance-policies', [
        'vehicle_id' => 2,
        'provider_name' => 'Tribal Mutual',
        'policy_number' => 'POL-PHASE2A-001',
        'effective_date' => now()->toDateString(),
        'expiration_date' => now()->addYear()->toDateString(),
        'status' => 'pending',
    ])->assertCreated();

    $this->postJson('/api/emissions-tests', [
        'vehicle_id' => 2,
        'test_date' => now()->toDateString(),
        'result' => 'pass',
        'facility_name' => 'First Tribal Emissions Center',
    ])->assertCreated();

    $this->postJson('/api/vehicle-inspections', [
        'vehicle_id' => 2,
        'inspection_date' => now()->toDateString(),
        'result' => 'pass',
        'facility_name' => 'First Tribal Inspection Office',
    ])->assertCreated();

    $this->getJson('/api/insurance-policies')
        ->assertOk()
        ->assertJsonStructure(['data', 'current_page', 'total']);

    $this->getJson('/api/emissions-tests')
        ->assertOk()
        ->assertJsonStructure(['data', 'current_page', 'total']);

    $this->getJson('/api/vehicle-inspections')
        ->assertOk()
        ->assertJsonStructure(['data', 'current_page', 'total']);
});

it('allows staff to approve member benefits and placards', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    $member = User::where('email', 'john@example.com')->firstOrFail();
    Sanctum::actingAs($member);

    $benefitResponse = $this->postJson('/api/member-benefits', [
        'benefit_type' => 'disabled',
    ]);
    $benefitResponse->assertCreated();

    $placardResponse = $this->postJson('/api/disability-placards', [
        'vehicle_id' => 2,
        'placard_type' => 'temporary',
    ]);
    $placardResponse->assertCreated();

    $benefitId = $benefitResponse->json('member_benefit.id');
    $placardId = $placardResponse->json('disability_placard.id');

    $admin = User::where('email', 'admin@tribe.gov')->firstOrFail();
    Sanctum::actingAs($admin);

    $this->putJson("/api/member-benefits/{$benefitId}", [
        'status' => 'active',
    ])->assertOk()->assertJsonPath('member_benefit.status', 'active');

    $this->putJson("/api/disability-placards/{$placardId}", [
        'status' => 'approved',
    ])->assertOk()
        ->assertJsonPath('disability_placard.status', 'approved')
        ->assertJsonPath('disability_placard.approved_by', $admin->id);
});
