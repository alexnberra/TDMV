<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
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
