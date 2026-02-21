<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Appointment;
use App\Models\BusinessAccount;
use App\Models\DisabilityPlacard;
use App\Models\Document;
use App\Models\EmissionsTest;
use App\Models\Faq;
use App\Models\FleetVehicle;
use App\Models\Household;
use App\Models\InsurancePolicy;
use App\Models\MemberBenefit;
use App\Models\OfficeLocation;
use App\Models\Payment;
use App\Models\Tribe;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\WorkflowRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LiveDataSeeder::class);

        $primaryTribe = Tribe::where('code', 'FTN')->firstOrFail();
        $primaryAdmin = User::where('email', 'admin@tribe.gov')->firstOrFail();
        $this->seedPhase3WorkflowRules($primaryTribe, $primaryAdmin);

        $john = $this->seedPrimaryDemoMember($primaryTribe);
        $staff = User::factory()
            ->count(config('tdmv.demo.staff_per_tribe'))
            ->staff()
            ->for($primaryTribe, 'tribe')
            ->create();
        $members = User::factory()
            ->count(config('tdmv.demo.members_per_tribe'))
            ->member()
            ->for($primaryTribe, 'tribe')
            ->create();

        $this->seedTribeOperationsData(
            tribe: $primaryTribe,
            members: $members->prepend($john),
            staff: $staff->prepend($primaryAdmin),
            skipVehicleCreationForUserIds: [$john->id],
        );

        $tribeCount = max(1, (int) config('tdmv.demo.tribes'));
        for ($i = 2; $i <= $tribeCount; $i++) {
            $tribe = Tribe::factory()->create([
                'name' => "Demo Tribal Nation {$i}",
                'slug' => "demo-tribal-nation-{$i}",
                'code' => "DT{$i}",
            ]);

            OfficeLocation::factory()->count(2)->for($tribe, 'tribe')->create();
            Faq::factory()->count(8)->for($tribe, 'tribe')->create();

            $admin = User::factory()
                ->admin()
                ->for($tribe, 'tribe')
                ->create([
                    'email' => "admin{$i}@tribe.gov",
                    'tribal_enrollment_id' => "ADMIN-{$i}",
                ]);
            $this->seedPhase3WorkflowRules($tribe, $admin);

            $tribeStaff = User::factory()
                ->count(config('tdmv.demo.staff_per_tribe'))
                ->staff()
                ->for($tribe, 'tribe')
                ->create();

            $tribeMembers = User::factory()
                ->count(config('tdmv.demo.members_per_tribe'))
                ->member()
                ->for($tribe, 'tribe')
                ->create();

            $this->seedTribeOperationsData(
                tribe: $tribe,
                members: $tribeMembers,
                staff: $tribeStaff->prepend($admin),
                skipVehicleCreationForUserIds: [],
            );
        }
    }

    private function seedPrimaryDemoMember(Tribe $tribe): User
    {
        $john = User::create([
            'tribe_id' => $tribe->id,
            'tribal_enrollment_id' => 'TID-123456',
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-05-15',
            'email' => 'john@example.com',
            'phone' => '(555) 234-5678',
            'password' => 'password',
            'role' => 'member',
            'address_line1' => '456 Member Lane',
            'city' => 'Reservation',
            'state' => 'ST',
            'zip_code' => '12346',
            'is_active' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $jane = User::create([
            'tribe_id' => $tribe->id,
            'tribal_enrollment_id' => 'TID-654321',
            'name' => 'Jane Doe',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '1992-07-11',
            'email' => 'jane@example.com',
            'phone' => '(555) 345-6789',
            'password' => 'password',
            'role' => 'member',
            'address_line1' => '456 Member Lane',
            'city' => 'Reservation',
            'state' => 'ST',
            'zip_code' => '12346',
            'is_active' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $ava = User::create([
            'tribe_id' => $tribe->id,
            'tribal_enrollment_id' => 'TID-654322',
            'name' => 'Ava Doe',
            'first_name' => 'Ava',
            'last_name' => 'Doe',
            'date_of_birth' => '2012-04-21',
            'email' => 'ava@example.com',
            'phone' => '(555) 456-7890',
            'password' => 'password',
            'role' => 'member',
            'address_line1' => '456 Member Lane',
            'city' => 'Reservation',
            'state' => 'ST',
            'zip_code' => '12346',
            'is_active' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $vehicleOne = Vehicle::create([
            'owner_id' => $john->id,
            'tribe_id' => $tribe->id,
            'vin' => '1HGCM82633A123451',
            'plate_number' => 'FTN1001',
            'year' => 2020,
            'make' => 'Toyota',
            'model' => 'Camry',
            'color' => 'Silver',
            'vehicle_type' => 'car',
            'registration_status' => 'active',
            'registration_date' => now()->subYear(),
            'expiration_date' => now()->addDays(45),
            'is_garaged_on_reservation' => true,
            'mileage' => 43000,
            'metadata' => ['fuel_type' => 'gas'],
        ]);

        $vehicleTwo = Vehicle::create([
            'owner_id' => $john->id,
            'tribe_id' => $tribe->id,
            'vin' => '1C4RJFBG6FC625472',
            'plate_number' => 'FTN2002',
            'year' => 2022,
            'make' => 'Jeep',
            'model' => 'Grand Cherokee',
            'color' => 'Black',
            'vehicle_type' => 'suv',
            'registration_status' => 'active',
            'registration_date' => now()->subMonths(6),
            'expiration_date' => now()->addDays(12),
            'is_garaged_on_reservation' => true,
            'mileage' => 21000,
            'metadata' => ['fuel_type' => 'gas'],
        ]);

        $application = Application::create([
            'user_id' => $john->id,
            'tribe_id' => $tribe->id,
            'vehicle_id' => $vehicleTwo->id,
            'service_type' => 'renewal',
            'status' => 'under_review',
            'priority' => 'normal',
            'submitted_at' => now()->subDays(2),
            'estimated_completion_date' => now()->addDays(1),
            'vehicle_data' => [
                'vin' => $vehicleTwo->vin,
                'make' => $vehicleTwo->make,
                'model' => $vehicleTwo->model,
                'year' => $vehicleTwo->year,
            ],
            'requirements_data' => [
                'insurance_verified' => true,
                'inspection_verified' => true,
            ],
        ]);

        $application->timeline()->createMany([
            [
                'event_type' => 'application_started',
                'description' => 'Application created',
                'performed_by' => $john->id,
            ],
            [
                'event_type' => 'application_submitted',
                'description' => 'Application submitted for review',
                'performed_by' => $john->id,
            ],
        ]);

        $this->createDocument($application, $john, 'insurance', 'accepted');
        $this->createDocument($application, $john, 'title', 'accepted');
        $this->createDocument($application, $john, 'tribal_id', 'accepted');

        Payment::create([
            'application_id' => $application->id,
            'user_id' => $john->id,
            'tribe_id' => $tribe->id,
            'transaction_id' => 'TXN-FTN-DEMO-001',
            'payment_method' => 'card',
            'amount' => 65.00,
            'fee_breakdown' => [
                'registration' => 45.00,
                'plate' => 15.00,
                'processing' => 5.00,
            ],
            'status' => 'completed',
            'payment_gateway' => 'stripe',
            'gateway_response' => ['seeded' => true],
            'paid_at' => now()->subDays(2),
        ]);

        $admin = User::where('tribe_id', $tribe->id)
            ->where('role', 'admin')
            ->first();

        $businessAccount = BusinessAccount::create([
            'tribe_id' => $tribe->id,
            'owner_user_id' => $john->id,
            'business_name' => 'Doe Family Logistics',
            'business_type' => 'fleet',
            'tax_id' => '11-2233445',
            'contact_email' => $john->email,
            'contact_phone' => $john->phone,
            'address' => $john->address_line1.', '.$john->city.', '.$john->state.' '.$john->zip_code,
            'tax_exempt' => true,
            'is_active' => true,
            'metadata' => ['seeded' => true],
        ]);

        $businessAccount->members()->syncWithoutDetaching([
            $john->id => ['role' => 'owner', 'is_primary' => true],
        ]);

        if ($admin) {
            $businessAccount->members()->syncWithoutDetaching([
                $admin->id => ['role' => 'manager', 'is_primary' => false],
            ]);
        }

        FleetVehicle::create([
            'business_account_id' => $businessAccount->id,
            'vehicle_id' => $vehicleTwo->id,
            'assigned_driver_id' => $john->id,
            'status' => 'active',
            'added_at' => now()->subMonths(4),
            'metadata' => ['seeded' => true],
        ]);

        InsurancePolicy::create([
            'vehicle_id' => $vehicleTwo->id,
            'user_id' => $john->id,
            'tribe_id' => $tribe->id,
            'provider_name' => 'Tribal Mutual',
            'policy_number' => 'POL-FTN-DEMO-2002',
            'effective_date' => now()->subMonths(6)->toDateString(),
            'expiration_date' => now()->addMonths(6)->toDateString(),
            'status' => 'active',
            'is_verified' => true,
            'verified_at' => now()->subMonths(5),
            'verified_by' => $admin?->id,
            'verification_source' => 'carrier_api',
            'metadata' => ['seeded' => true],
        ]);

        EmissionsTest::create([
            'vehicle_id' => $vehicleTwo->id,
            'user_id' => $john->id,
            'tribe_id' => $tribe->id,
            'test_date' => now()->subMonths(3)->toDateString(),
            'result' => 'pass',
            'facility_name' => 'First Tribal Emissions Center',
            'certificate_number' => 'EM-FTN-2002',
            'expires_at' => now()->addMonths(9)->toDateString(),
            'metadata' => ['seeded' => true],
        ]);

        VehicleInspection::create([
            'vehicle_id' => $vehicleTwo->id,
            'user_id' => $john->id,
            'tribe_id' => $tribe->id,
            'inspection_date' => now()->subMonths(3)->toDateString(),
            'result' => 'pass',
            'facility_name' => 'First Tribal Inspection Office',
            'certificate_number' => 'INSP-FTN-2002',
            'expires_at' => now()->addMonths(9)->toDateString(),
            'metadata' => ['seeded' => true],
        ]);

        MemberBenefit::create([
            'user_id' => $john->id,
            'tribe_id' => $tribe->id,
            'benefit_type' => 'veteran',
            'status' => 'active',
            'effective_date' => now()->subYear()->toDateString(),
            'expiration_date' => now()->addYear()->toDateString(),
            'verified_by' => $admin?->id,
            'verified_at' => now()->subYear(),
            'metadata' => ['seeded' => true],
        ]);

        DisabilityPlacard::create([
            'user_id' => $john->id,
            'tribe_id' => $tribe->id,
            'vehicle_id' => $vehicleTwo->id,
            'placard_type' => 'temporary',
            'status' => 'pending',
            'expiration_date' => now()->addMonths(6)->toDateString(),
            'metadata' => ['seeded' => true],
        ]);

        $household = Household::create([
            'tribe_id' => $tribe->id,
            'owner_user_id' => $john->id,
            'household_name' => 'Doe Household',
            'address_line1' => '456 Member Lane',
            'city' => 'Reservation',
            'state' => 'ST',
            'zip_code' => '12346',
            'is_active' => true,
            'metadata' => ['seeded' => true],
        ]);

        $household->members()->createMany([
            [
                'user_id' => $john->id,
                'relationship_type' => 'self',
                'is_primary' => true,
                'can_manage_minor_vehicles' => true,
                'is_minor' => false,
                'date_joined' => now()->subYears(4)->toDateString(),
            ],
            [
                'user_id' => $jane->id,
                'relationship_type' => 'spouse',
                'is_primary' => false,
                'can_manage_minor_vehicles' => true,
                'is_minor' => false,
                'date_joined' => now()->subYears(4)->toDateString(),
            ],
            [
                'user_id' => $ava->id,
                'relationship_type' => 'child',
                'is_primary' => false,
                'can_manage_minor_vehicles' => false,
                'is_minor' => true,
                'date_joined' => now()->subYears(1)->toDateString(),
            ],
        ]);

        $mainOffice = OfficeLocation::where('tribe_id', $tribe->id)->first();

        Appointment::create([
            'tribe_id' => $tribe->id,
            'user_id' => $john->id,
            'household_id' => $household->id,
            'office_location_id' => $mainOffice?->id,
            'appointment_type' => 'dmv_office_visit',
            'status' => 'confirmed',
            'scheduled_for' => now()->addDays(3)->setHour(10)->setMinute(30),
            'duration_minutes' => 30,
            'notes' => 'Bring updated insurance card and tribal ID.',
            'metadata' => ['seeded' => true],
        ]);

        Appointment::create([
            'tribe_id' => $tribe->id,
            'user_id' => $john->id,
            'household_id' => $household->id,
            'office_location_id' => $mainOffice?->id,
            'appointment_type' => 'plate_pickup',
            'status' => 'requested',
            'scheduled_for' => now()->addDays(9)->setHour(9)->setMinute(15),
            'duration_minutes' => 20,
            'notes' => 'Pickup renewed plate for FTN2002.',
            'metadata' => ['seeded' => true],
        ]);

        $john->notifications()->createMany([
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\ApplicationStatusUpdated',
                'data' => [
                    'title' => 'Application Under Review',
                    'message' => "Case {$application->case_number} is currently under review.",
                ],
                'read_at' => null,
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\VehicleExpiringSoon',
                'data' => [
                    'title' => 'Registration Expiring Soon',
                    'message' => "Your vehicle {$vehicleTwo->plate_number} expires in 12 days.",
                ],
                'read_at' => now()->subHours(1),
                'created_at' => now()->subDay(),
                'updated_at' => now()->subHours(1),
            ],
        ]);

        return $john;
    }

    /**
     * @param  array<int>  $skipVehicleCreationForUserIds
     */
    private function seedTribeOperationsData(Tribe $tribe, Collection $members, Collection $staff, array $skipVehicleCreationForUserIds): void
    {
        $vehicleMin = max(1, (int) config('tdmv.demo.vehicles_per_member_min'));
        $vehicleMax = max($vehicleMin, (int) config('tdmv.demo.vehicles_per_member_max'));
        $applicationsMin = max(1, (int) config('tdmv.demo.applications_per_vehicle_min'));
        $applicationsMax = max($applicationsMin, (int) config('tdmv.demo.applications_per_vehicle_max'));

        foreach ($members as $member) {
            $vehicles = $member->vehicles;
            if (! in_array($member->id, $skipVehicleCreationForUserIds, true)) {
                $vehicles = Vehicle::factory()
                    ->count(random_int($vehicleMin, $vehicleMax))
                    ->for($member, 'owner')
                    ->for($tribe, 'tribe')
                    ->create();
            }

            foreach ($vehicles as $vehicle) {
                for ($i = 0; $i < random_int($applicationsMin, $applicationsMax); $i++) {
                    $application = Application::factory()
                        ->for($member, 'user')
                        ->for($tribe, 'tribe')
                        ->for($vehicle, 'vehicle')
                        ->create([
                            'status' => 'draft',
                            'submitted_at' => null,
                            'vehicle_data' => [
                                'vin' => $vehicle->vin,
                                'make' => $vehicle->make,
                                'model' => $vehicle->model,
                                'year' => $vehicle->year,
                            ],
                        ]);

                    $application->timeline()->create([
                        'event_type' => 'application_started',
                        'description' => 'Application created',
                        'performed_by' => $member->id,
                    ]);

                    $targetStatus = fake()->randomElement([
                        'draft',
                        'submitted',
                        'under_review',
                        'info_requested',
                        'approved',
                        'completed',
                    ]);

                    if ($targetStatus === 'draft') {
                        continue;
                    }

                    $application->update([
                        'status' => 'submitted',
                        'submitted_at' => now()->subDays(random_int(1, 20)),
                        'estimated_completion_date' => now()->addDays(random_int(1, 14)),
                    ]);

                    $application->timeline()->create([
                        'event_type' => 'application_submitted',
                        'description' => 'Application submitted for review',
                        'performed_by' => $member->id,
                    ]);

                    $this->createDocument($application, $member, 'insurance', 'accepted');
                    $this->createDocument($application, $member, 'title', 'accepted');
                    $this->createDocument($application, $member, 'tribal_id', 'accepted');

                    if (in_array($targetStatus, ['under_review', 'info_requested', 'approved', 'completed'], true)) {
                        $reviewer = $staff->random();
                        $application->update([
                            'status' => $targetStatus,
                            'reviewed_at' => now()->subDays(random_int(0, 6)),
                            'reviewed_by' => $reviewer->id,
                            'reviewer_notes' => $targetStatus === 'info_requested'
                                ? 'Please upload a clearer proof of residency document.'
                                : 'Reviewed by staff.',
                        ]);

                        if ($targetStatus === 'info_requested') {
                            $application->timeline()->create([
                                'event_type' => 'info_requested',
                                'description' => 'More information requested',
                                'performed_by' => $reviewer->id,
                            ]);
                        }
                    }

                    if (in_array($targetStatus, ['approved', 'completed'], true)) {
                        $payment = Payment::factory()
                            ->for($application, 'application')
                            ->for($member, 'user')
                            ->for($tribe, 'tribe')
                            ->create(['status' => 'completed']);

                        $application->timeline()->create([
                            'event_type' => 'payment_received',
                            'description' => 'Payment received: $'.number_format((float) $payment->amount, 2),
                            'performed_by' => $member->id,
                        ]);
                    }
                }
            }

            $this->seedPhase2aMemberData(
                tribe: $tribe,
                member: $member,
                staff: $staff,
                vehicles: $vehicles,
            );

            $this->seedPhase2bMemberData(
                tribe: $tribe,
                member: $member,
                staff: $staff,
            );

            $member->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\GeneralAnnouncement',
                'data' => [
                    'title' => 'Welcome to the Tribal DMV Portal',
                    'message' => 'Your account is ready. You can now manage registrations online.',
                ],
                'read_at' => null,
                'created_at' => now()->subHours(random_int(1, 96)),
                'updated_at' => now()->subHours(random_int(1, 96)),
            ]);
        }
    }

    private function seedPhase2aMemberData(Tribe $tribe, User $member, Collection $staff, Collection $vehicles): void
    {
        if ($vehicles->isEmpty()) {
            return;
        }

        if (BusinessAccount::query()->where('owner_user_id', $member->id)->exists()) {
            return;
        }

        $businessAccount = BusinessAccount::factory()
            ->for($tribe, 'tribe')
            ->for($member, 'owner')
            ->create([
                'business_name' => "{$member->last_name} Transport Group",
            ]);

        $businessAccount->members()->syncWithoutDetaching([
            $member->id => ['role' => 'owner', 'is_primary' => true],
        ]);

        if ($staff->isNotEmpty()) {
            $manager = $staff->random();
            $businessAccount->members()->syncWithoutDetaching([
                $manager->id => ['role' => 'manager', 'is_primary' => false],
            ]);
        }

        $fleetVehicle = FleetVehicle::factory()
            ->for($businessAccount, 'businessAccount')
            ->for($vehicles->first(), 'vehicle')
            ->create([
                'assigned_driver_id' => $member->id,
                'status' => 'active',
            ]);

        $fleetVehicle->vehicle->insurancePolicies()->create([
            'user_id' => $member->id,
            'tribe_id' => $tribe->id,
            'provider_name' => 'Tribal Mutual',
            'policy_number' => strtoupper('POL-'.Str::random(10)),
            'effective_date' => now()->subMonths(random_int(1, 10))->toDateString(),
            'expiration_date' => now()->addMonths(random_int(1, 12))->toDateString(),
            'status' => 'active',
            'is_verified' => true,
            'verified_at' => now()->subDays(random_int(1, 90)),
            'verified_by' => $staff->isNotEmpty() ? $staff->random()->id : null,
            'verification_source' => 'carrier_api',
            'metadata' => ['seeded' => true],
        ]);

        $fleetVehicle->vehicle->emissionsTests()->create([
            'user_id' => $member->id,
            'tribe_id' => $tribe->id,
            'test_date' => now()->subMonths(random_int(1, 10))->toDateString(),
            'result' => fake()->randomElement(['pass', 'pass', 'waived', 'fail']),
            'facility_name' => "{$tribe->name} Emissions Office",
            'certificate_number' => strtoupper('EM-'.Str::random(8)),
            'expires_at' => now()->addMonths(random_int(2, 12))->toDateString(),
            'metadata' => ['seeded' => true],
        ]);

        $fleetVehicle->vehicle->inspections()->create([
            'user_id' => $member->id,
            'tribe_id' => $tribe->id,
            'inspection_date' => now()->subMonths(random_int(1, 10))->toDateString(),
            'result' => fake()->randomElement(['pass', 'pass', 'conditional', 'fail']),
            'facility_name' => "{$tribe->name} Safety Office",
            'certificate_number' => strtoupper('INSP-'.Str::random(8)),
            'expires_at' => now()->addMonths(random_int(2, 12))->toDateString(),
            'metadata' => ['seeded' => true],
        ]);

        if (! MemberBenefit::query()->where('user_id', $member->id)->exists()) {
            MemberBenefit::factory()
                ->for($member, 'user')
                ->for($tribe, 'tribe')
                ->create();
        }

        if (! DisabilityPlacard::query()->where('user_id', $member->id)->exists() && fake()->boolean(35)) {
            DisabilityPlacard::factory()
                ->for($member, 'user')
                ->for($tribe, 'tribe')
                ->for($fleetVehicle->vehicle, 'vehicle')
                ->create([
                    'status' => 'pending',
                    'placard_number' => null,
                    'approved_at' => null,
                    'approved_by' => null,
                ]);
        }
    }

    private function seedPhase2bMemberData(Tribe $tribe, User $member, Collection $staff): void
    {
        if (! Household::query()->where('owner_user_id', $member->id)->exists()) {
            $household = Household::factory()
                ->for($tribe, 'tribe')
                ->for($member, 'owner')
                ->create([
                    'household_name' => "{$member->last_name} Household",
                ]);

            $household->members()->create([
                'user_id' => $member->id,
                'relationship_type' => 'self',
                'is_primary' => true,
                'can_manage_minor_vehicles' => true,
                'is_minor' => false,
                'date_joined' => now()->subMonths(random_int(1, 60))->toDateString(),
                'metadata' => ['seeded' => true],
            ]);
        } else {
            $household = Household::query()
                ->where('owner_user_id', $member->id)
                ->first();
        }

        if (! $household) {
            return;
        }

        if (! Appointment::query()->where('user_id', $member->id)->exists()) {
            $office = OfficeLocation::query()
                ->where('tribe_id', $tribe->id)
                ->inRandomOrder()
                ->first();

            Appointment::factory()
                ->for($tribe, 'tribe')
                ->for($member, 'user')
                ->create([
                    'household_id' => $household->id,
                    'office_location_id' => $office?->id,
                    'status' => 'confirmed',
                    'scheduled_for' => now()->addDays(random_int(2, 30)),
                ]);

            if ($staff->isNotEmpty() && fake()->boolean(30)) {
                Appointment::factory()
                    ->for($tribe, 'tribe')
                    ->for($member, 'user')
                    ->create([
                        'household_id' => $household->id,
                        'office_location_id' => $office?->id,
                        'status' => 'cancelled',
                        'scheduled_for' => now()->subDays(random_int(1, 40)),
                        'cancelled_at' => now()->subDays(random_int(1, 20)),
                        'cancelled_by' => $staff->random()->id,
                        'cancel_reason' => 'Office closure due to weather.',
                    ]);
            }
        }
    }

    private function createDocument(Application $application, User $user, string $documentType, string $status): void
    {
        $name = "{$documentType}-".Str::uuid().'.pdf';
        $path = "documents/seed/{$application->id}/{$name}";
        $disk = Storage::disk(config('filesystems.default'));
        $disk->put($path, "Seeded document for {$documentType}");

        $fileSize = $disk->size($path);

        Document::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'document_type' => $documentType,
            'file_name' => $name,
            'file_path' => $path,
            'file_size' => $fileSize,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now()->subDays(random_int(0, 8)),
            'status' => $status,
            'reviewed_at' => $status === 'accepted' ? now()->subDays(random_int(0, 7)) : null,
            'metadata' => ['seeded' => true],
        ]);
    }

    private function seedPhase3WorkflowRules(Tribe $tribe, User $admin): void
    {
        WorkflowRule::updateOrCreate(
            [
                'tribe_id' => $tribe->id,
                'key' => 'auto_approve_simple_renewals',
            ],
            [
                'name' => 'Auto Approve Simple Renewals',
                'description' => 'Automatically approve submitted renewal applications that satisfy required documents and payment.',
                'is_active' => true,
                'config' => [
                    'required_documents' => ['insurance', 'title', 'tribal_id'],
                    'require_completed_payment' => true,
                    'max_vehicle_age_years' => 20,
                    'max_batch' => 100,
                ],
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );
    }
}
