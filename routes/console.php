<?php

use App\Http\Resources\UserProfileResource;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tdmv:mode {mode : demo|live} {--fresh : Rebuild database after switching}', function () {
    $mode = strtolower((string) $this->argument('mode'));

    if (! in_array($mode, ['demo', 'live'], true)) {
        $this->error('Mode must be one of: demo, live');

        return self::FAILURE;
    }

    $envPath = base_path('.env');

    if (! File::exists($envPath)) {
        $this->error('Unable to locate .env file.');

        return self::FAILURE;
    }

    $contents = File::get($envPath);
    $entry = "TDMV_SEED_MODE={$mode}";

    if (preg_match('/^TDMV_SEED_MODE=.*/m', $contents) === 1) {
        $contents = (string) preg_replace('/^TDMV_SEED_MODE=.*/m', $entry, $contents);
    } else {
        $contents = Str::finish($contents, PHP_EOL).$entry.PHP_EOL;
    }

    File::put($envPath, $contents);

    Artisan::call('config:clear');
    config()->set('tdmv.seed_mode', $mode);

    $this->info("TDMV seed mode switched to [{$mode}].");

    if ($this->option('fresh')) {
        $this->call('migrate:fresh', ['--seed' => true]);
    } else {
        $this->line('Run `php artisan db:seed` (or use `--fresh`) to apply the selected mode data.');
    }

    return self::SUCCESS;
})->purpose('Switch between demo and live seed modes');

Artisan::command('tdmv:smoke {--fresh : Rebuild and seed before smoke tests}', function () {
    if ($this->option('fresh')) {
        $this->call('migrate:fresh', ['--seed' => true]);
    }

    $result = Process::timeout(300)->run(
        'php artisan test --testsuite=Feature --filter="VehicleApiTest|PlatformSmokeTest|ModelAndFeatureIntegrityTest|Phase2aApiTest|Phase2bApiTest|Phase3ApiTest" --compact'
    );

    $this->output->write($result->output());
    $this->output->write($result->errorOutput());

    if (! $result->successful()) {
        $this->error('Smoke tests failed.');

        return self::FAILURE;
    }

    $this->info('Smoke tests passed.');

    return self::SUCCESS;
})->purpose('Run platform smoke tests');

Artisan::command('tdmv:reconcile-migrations', function () {
    if (! Schema::hasTable('migrations')) {
        $this->line('Migrations table does not exist yet. Skipping reconciliation.');

        return self::SUCCESS;
    }

    $migrationTableMap = [
        '0001_01_01_000000_create_users_table' => ['users', 'password_reset_tokens', 'sessions'],
        '0001_01_01_000001_create_cache_table' => ['cache', 'cache_locks'],
        '0001_01_01_000002_create_jobs_table' => ['jobs', 'job_batches', 'failed_jobs'],
        '2026_02_21_000001_create_tribes_table' => ['tribes'],
        '2026_02_21_000003_create_vehicles_table' => ['vehicles'],
        '2026_02_21_000004_create_applications_table' => ['applications'],
        '2026_02_21_000005_create_documents_table' => ['documents'],
        '2026_02_21_000006_create_payments_table' => ['payments'],
        '2026_02_21_000007_create_notification_preferences_table' => ['notification_preferences'],
        '2026_02_21_000008_create_application_timeline_table' => ['application_timeline'],
        '2026_02_21_000009_create_office_locations_table' => ['office_locations'],
        '2026_02_21_000010_create_faqs_table' => ['faqs'],
        '2026_02_21_031139_create_personal_access_tokens_table' => ['personal_access_tokens'],
        '2026_02_21_120001_create_notifications_table' => ['notifications'],
        '2026_02_21_130001_create_business_accounts_table' => ['business_accounts'],
        '2026_02_21_130002_create_business_account_user_table' => ['business_account_user'],
        '2026_02_21_130003_create_fleet_vehicles_table' => ['fleet_vehicles'],
        '2026_02_21_130004_create_insurance_policies_table' => ['insurance_policies'],
        '2026_02_21_130005_create_emissions_tests_table' => ['emissions_tests'],
        '2026_02_21_130006_create_vehicle_inspections_table' => ['vehicle_inspections'],
        '2026_02_21_130007_create_member_benefits_table' => ['member_benefits'],
        '2026_02_21_130008_create_disability_placards_table' => ['disability_placards'],
        '2026_02_21_202114_create_households_table' => ['households'],
        '2026_02_21_202115_create_appointments_table' => ['appointments'],
        '2026_02_21_202115_create_household_members_table' => ['household_members'],
        '2026_02_21_203452_create_workflow_rules_table' => ['workflow_rules'],
        '2026_02_21_203453_create_assistant_interactions_table' => ['assistant_interactions'],
    ];

    $nextBatch = ((int) DB::table('migrations')->max('batch')) + 1;
    $reconciled = 0;

    foreach ($migrationTableMap as $migration => $tables) {
        $alreadyRecorded = DB::table('migrations')->where('migration', $migration)->exists();

        if ($alreadyRecorded) {
            continue;
        }

        $allTablesExist = collect($tables)->every(fn (string $table): bool => Schema::hasTable($table));

        if (! $allTablesExist) {
            continue;
        }

        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $nextBatch,
        ]);

        $reconciled++;
        $this->warn("Reconciled migration record for [{$migration}]");
    }

    if ($reconciled === 0) {
        $this->line('No migration reconciliation needed.');

        return self::SUCCESS;
    }

    $this->info("Reconciled {$reconciled} migration record(s).");

    return self::SUCCESS;
})->purpose('Mark create-table migrations as applied when matching tables already exist');

Artisan::command('tdmv:ensure-login-users {--mode=live : live|demo}', function () {
    if (! Schema::hasTable('tribes') || ! Schema::hasTable('users')) {
        $this->error('Required tables are missing. Run migrations first.');

        return self::FAILURE;
    }

    $mode = strtolower((string) $this->option('mode'));
    if (! in_array($mode, ['live', 'demo'], true)) {
        $this->error('Mode must be live or demo.');

        return self::FAILURE;
    }

    $tribe = Tribe::query()->firstOrCreate(
        ['code' => 'FTN'],
        [
            'name' => 'First Tribal Nation',
            'slug' => 'first-tribal-nation',
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
        ]
    );

    $upsertUser = function (array $attributes) use ($tribe): User {
        $user = User::withTrashed()->where('email', $attributes['email'])->first();

        if (! $user) {
            $user = new User();
        }

        $user->forceFill([
            'tribe_id' => $tribe->id,
            'tribal_enrollment_id' => $attributes['tribal_enrollment_id'],
            'name' => $attributes['name'],
            'first_name' => $attributes['first_name'],
            'last_name' => $attributes['last_name'],
            'date_of_birth' => $attributes['date_of_birth'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'role' => $attributes['role'],
            'address_line1' => $attributes['address_line1'],
            'city' => $attributes['city'],
            'state' => $attributes['state'],
            'zip_code' => $attributes['zip_code'],
            'is_active' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => 'password',
        ]);

        $user->deleted_at = null;
        $user->save();

        return $user;
    };

    $admin = $upsertUser([
        'tribal_enrollment_id' => 'ADMIN-001',
        'name' => 'Admin User',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'date_of_birth' => '1980-01-01',
        'email' => 'admin@tribe.gov',
        'phone' => '(555) 111-1111',
        'role' => 'admin',
        'address_line1' => '123 Admin St',
        'city' => 'Headquarters',
        'state' => 'ST',
        'zip_code' => '12345',
    ]);

    $this->info("Ensured login: {$admin->email} / password");

    if ($mode === 'demo') {
        $demoUsers = [
            [
                'tribal_enrollment_id' => 'TID-123456',
                'name' => 'John Doe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'date_of_birth' => '1990-05-15',
                'email' => 'john@example.com',
                'phone' => '(555) 234-5678',
                'role' => 'member',
                'address_line1' => '456 Member Lane',
                'city' => 'Reservation',
                'state' => 'ST',
                'zip_code' => '12346',
            ],
            [
                'tribal_enrollment_id' => 'TID-654321',
                'name' => 'Jane Doe',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'date_of_birth' => '1992-07-11',
                'email' => 'jane@example.com',
                'phone' => '(555) 345-6789',
                'role' => 'member',
                'address_line1' => '456 Member Lane',
                'city' => 'Reservation',
                'state' => 'ST',
                'zip_code' => '12346',
            ],
            [
                'tribal_enrollment_id' => 'TID-654322',
                'name' => 'Ava Doe',
                'first_name' => 'Ava',
                'last_name' => 'Doe',
                'date_of_birth' => '2012-04-21',
                'email' => 'ava@example.com',
                'phone' => '(555) 456-7890',
                'role' => 'member',
                'address_line1' => '456 Member Lane',
                'city' => 'Reservation',
                'state' => 'ST',
                'zip_code' => '12346',
            ],
        ];

        foreach ($demoUsers as $demoUser) {
            $user = $upsertUser($demoUser);
            $this->info("Ensured login: {$user->email} / password");
        }
    }

    return self::SUCCESS;
})->purpose('Ensure default login users exist with known credentials');

Artisan::command('tdmv:debug-login {email=admin@tribe.gov} {--password=password}', function () {
    $email = (string) $this->argument('email');
    $password = (string) $this->option('password');
    $identifier = Str::lower(trim($email));

    $this->line('App environment: '.config('app.env'));
    $this->line('Default DB connection: '.config('database.default'));
    $this->line('Configured DB host: '.(string) config('database.connections.'.config('database.default').'.host'));
    $this->line('Configured DB database: '.(string) config('database.connections.'.config('database.default').'.database'));
    $this->line('Active DB database: '.(string) DB::connection()->getDatabaseName());
    $this->line('users table exists: '.(Schema::hasTable('users') ? 'yes' : 'no'));
    $this->line('tribes table exists: '.(Schema::hasTable('tribes') ? 'yes' : 'no'));
    $this->line('notification_preferences table exists: '.(Schema::hasTable('notification_preferences') ? 'yes' : 'no'));
    $this->line('personal_access_tokens table exists: '.(Schema::hasTable('personal_access_tokens') ? 'yes' : 'no'));

    if (! Schema::hasTable('users')) {
        $this->error('users table does not exist.');

        return self::FAILURE;
    }

    /** @var \App\Models\User|null $user */
    $user = User::withTrashed()
        ->whereRaw('LOWER(email) = ?', [$identifier])
        ->orWhereRaw('LOWER(tribal_enrollment_id) = ?', [$identifier])
        ->first(['id', 'email', 'password', 'role', 'is_active', 'deleted_at', 'tribe_id']);

    if (! $user) {
        $this->error("User not found for identifier: {$email}");

        return self::FAILURE;
    }

    $this->info("User found: {$user->email} (id={$user->id}, role={$user->role})");
    $this->line('is_active: '.($user->is_active ? 'true' : 'false'));
    $this->line('deleted_at: '.($user->deleted_at?->toDateTimeString() ?? 'null'));
    $this->line('tribe_id: '.($user->tribe_id ?? 'null'));
    $passwordPasses = Hash::check($password, (string) $user->password);
    $this->line('password hash check: '.($passwordPasses ? 'PASS' : 'FAIL'));

    $apiUser = User::query()
        ->apiAuth()
        ->whereRaw('LOWER(email) = ?', [$identifier])
        ->orWhereRaw('LOWER(tribal_enrollment_id) = ?', [$identifier])
        ->first();
    $this->line('apiAuth() query user found: '.($apiUser ? 'yes' : 'no'));

    try {
        $resourceUser = User::query()->whereKey($user->id)->firstOrFail();
        $relations = [];

        if (Schema::hasTable('tribes')) {
            $relations['tribe'] = fn ($query) => $query->apiPublic();
        }

        if (Schema::hasTable('notification_preferences')) {
            $relations['notificationPreferences'] = fn ($query) => $query->apiSelect();
        }

        if ($relations !== []) {
            $resourceUser->loadMissing($relations);
        }

        UserProfileResource::make($resourceUser)->resolve();
        $this->line('profile serialization: PASS');
    } catch (\Throwable $exception) {
        $this->error('profile serialization: FAIL');
        $this->line($exception->getMessage());

        return self::FAILURE;
    }

    if (Schema::hasTable('personal_access_tokens')) {
        try {
            $token = $user->createToken('debug-login-token');
            $token->accessToken->delete();
            $this->line('token creation: PASS');
        } catch (\Throwable $exception) {
            $this->error('token creation: FAIL');
            $this->line($exception->getMessage());

            return self::FAILURE;
        }
    } else {
        $this->error('token creation: FAIL (personal_access_tokens missing)');

        return self::FAILURE;
    }

    if (! $passwordPasses || ! $apiUser || ! $user->is_active || $user->deleted_at !== null) {
        $this->error('One or more login checks failed.');

        return self::FAILURE;
    }

    $this->info('All login checks passed.');

    return self::SUCCESS;
})->purpose('Debug login readiness for a specific email and password');

Artisan::command('tdmv:set-login-password {email} {password=password}', function () {
    $email = (string) $this->argument('email');
    $password = (string) $this->argument('password');

    /** @var \App\Models\User|null $user */
    $user = User::withTrashed()->where('email', $email)->first();

    if (! $user) {
        $this->error("User not found: {$email}");

        return self::FAILURE;
    }

    $user->forceFill([
        'password' => $password,
        'is_active' => true,
        'email_verified_at' => $user->email_verified_at ?? now(),
    ]);
    $user->deleted_at = null;
    $user->save();

    $this->info("Password reset for {$email}.");

    return self::SUCCESS;
})->purpose('Force reset a user password for login recovery');
