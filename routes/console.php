<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
