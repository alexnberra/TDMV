<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds demo mode with sample member data', function () {
    config()->set('tdmv.seed_mode', 'demo');
    $this->seed();

    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'admin@tribe.gov')->exists())->toBeTrue();
});

it('seeds live mode without demo member data', function () {
    config()->set('tdmv.seed_mode', 'live');
    $this->seed();

    expect(User::where('email', 'admin@tribe.gov')->exists())->toBeTrue();
    expect(User::where('email', 'john@example.com')->exists())->toBeFalse();
});
