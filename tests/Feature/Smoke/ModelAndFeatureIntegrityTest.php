<?php

use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\Appointment;
use App\Models\AssistantInteraction;
use App\Models\BusinessAccount;
use App\Models\DisabilityPlacard;
use App\Models\Document;
use App\Models\EmissionsTest;
use App\Models\Faq;
use App\Models\FleetVehicle;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\InsurancePolicy;
use App\Models\MemberBenefit;
use App\Models\NotificationPreferences;
use App\Models\OfficeLocation;
use App\Models\Payment;
use App\Models\Tribe;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\WorkflowRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('validates seeded model graph and core feature endpoints', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    expect(Tribe::query()->count())->toBeGreaterThan(0);
    expect(User::query()->count())->toBeGreaterThan(0);
    expect(Vehicle::query()->count())->toBeGreaterThan(0);
    expect(Application::query()->count())->toBeGreaterThan(0);
    expect(Document::query()->count())->toBeGreaterThan(0);
    expect(Payment::query()->count())->toBeGreaterThan(0);
    expect(NotificationPreferences::query()->count())->toBeGreaterThan(0);
    expect(ApplicationTimeline::query()->count())->toBeGreaterThan(0);
    expect(OfficeLocation::query()->count())->toBeGreaterThan(0);
    expect(Faq::query()->count())->toBeGreaterThan(0);
    expect(BusinessAccount::query()->count())->toBeGreaterThan(0);
    expect(FleetVehicle::query()->count())->toBeGreaterThan(0);
    expect(InsurancePolicy::query()->count())->toBeGreaterThan(0);
    expect(EmissionsTest::query()->count())->toBeGreaterThan(0);
    expect(VehicleInspection::query()->count())->toBeGreaterThan(0);
    expect(MemberBenefit::query()->count())->toBeGreaterThan(0);
    expect(DisabilityPlacard::query()->count())->toBeGreaterThan(0);
    expect(Household::query()->count())->toBeGreaterThan(0);
    expect(HouseholdMember::query()->count())->toBeGreaterThan(0);
    expect(Appointment::query()->count())->toBeGreaterThan(0);
    expect(WorkflowRule::query()->count())->toBeGreaterThan(0);

    $member = User::query()->where('email', 'john@example.com')->firstOrFail();
    $admin = User::query()->where('email', 'admin@tribe.gov')->firstOrFail();

    $vehicle = Vehicle::query()->apiDetail()->where('owner_id', $member->id)->firstOrFail();
    expect($vehicle->owner()->exists())->toBeTrue();
    expect($vehicle->tribe()->exists())->toBeTrue();

    $application = Application::query()->apiList()->where('user_id', $member->id)->firstOrFail();
    expect($application->user()->exists())->toBeTrue();
    expect($application->vehicle()->exists())->toBeTrue();

    expect(Vehicle::query()->expiringSoon(30)->count())->toBeInt();
    expect(Vehicle::query()->expired()->count())->toBeInt();
    expect(Application::query()->pending()->count())->toBeInt();
    expect(Application::query()->underReview()->count())->toBeInt();
    expect(WorkflowRule::query()->active()->count())->toBeGreaterThan(0);

    $this->getJson('/api/office-locations')->assertOk();
    $this->getJson('/api/faqs')->assertOk();

    Sanctum::actingAs($member);
    $this->getJson('/api/user')->assertOk();
    $this->getJson('/api/vehicles')->assertOk();
    $this->getJson('/api/applications')->assertOk();
    $this->getJson('/api/business-accounts')->assertOk();
    $this->getJson('/api/insurance-policies')->assertOk();
    $this->getJson('/api/emissions-tests')->assertOk();
    $this->getJson('/api/vehicle-inspections')->assertOk();
    $this->getJson('/api/member-benefits')->assertOk();
    $this->getJson('/api/disability-placards')->assertOk();
    $this->getJson('/api/households')->assertOk();
    $this->getJson('/api/appointments')->assertOk();
    $this->getJson('/api/notifications')->assertOk();
    $this->getJson('/api/notification-preferences')->assertOk();
    $this->getJson("/api/vehicles/{$vehicle->id}/renewal-history")->assertOk();
    $this->getJson("/api/applications/{$application->id}/timeline")->assertOk();

    $assistantResponse = $this->postJson('/api/assistant/query', [
        'query' => 'Do I have any renewals due soon?',
        'channel' => 'portal',
    ]);
    $assistantResponse->assertOk()->assertJsonStructure([
        'interaction_id',
        'intent',
        'message',
        'data',
        'suggestions',
        'response_time_ms',
    ]);

    expect(AssistantInteraction::query()->count())->toBeGreaterThan(0);

    Sanctum::actingAs($admin);
    $this->getJson('/api/admin/dashboard/stats')->assertOk();
    $this->getJson('/api/admin/applications')->assertOk();
    $this->getJson('/api/admin/phase3/insights')->assertOk();
    $this->postJson('/api/admin/phase3/automation/run', [
        'dry_run' => true,
    ])->assertOk();
});
