<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\OfficeLocation;
use App\Models\Tribe;
use App\Models\User;
use App\Models\WorkflowRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $tribe = Tribe::create([
            'name' => 'First Tribal Nation',
            'slug' => 'first-tribal-nation',
            'code' => 'FTN',
            'primary_color' => '#2563eb',
            'contact_email' => 'contact@tribe.gov',
            'contact_phone' => '(555) 123-4567',
            'address' => '123 Tribal Office Road, Headquarters, ST 12345',
            'is_active' => true,
            'settings' => [
                'fees' => [
                    'registration' => 45.00,
                    'plate' => 15.00,
                    'processing' => 5.00,
                ],
            ],
        ]);

        $admin = User::create([
            'tribe_id' => $tribe->id,
            'tribal_enrollment_id' => 'ADMIN-001',
            'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'date_of_birth' => '1980-01-01',
            'email' => 'admin@tribe.gov',
            'phone' => '(555) 111-1111',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'address_line1' => '123 Admin St',
            'city' => 'Headquarters',
            'state' => 'ST',
            'zip_code' => '12345',
            'is_active' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->seedPhase3WorkflowRules($tribe, $admin);

        OfficeLocation::create([
            'tribe_id' => $tribe->id,
            'name' => 'Main Office',
            'address' => '123 Tribal Office Road, Headquarters, ST 12345',
            'phone' => '(555) 123-4567',
            'email' => 'vehicleservices@tribe.gov',
            'hours' => [
                'monday' => '08:00-17:00',
                'tuesday' => '08:00-17:00',
                'wednesday' => '08:00-17:00',
                'thursday' => '08:00-17:00',
                'friday' => '08:00-17:00',
            ],
            'is_active' => true,
        ]);

        Faq::insert([
            [
                'tribe_id' => $tribe->id,
                'category' => 'Registration & Renewals',
                'question' => 'How do I renew my vehicle registration?',
                'answer' => 'Log in, open your vehicle profile, and follow the renewal workflow.',
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tribe_id' => $tribe->id,
                'category' => 'Payments',
                'question' => 'Which payment methods are accepted?',
                'answer' => 'Card and ACH are supported online. In-office payments can include cash and check.',
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
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
