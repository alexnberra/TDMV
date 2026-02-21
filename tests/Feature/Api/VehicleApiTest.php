<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('authenticates and returns an api token', function () {
    $this->seed();

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'user' => ['id', 'email'],
            'token',
        ]);
});

it('returns vehicle id 2 for the authenticated owner', function () {
    $this->seed();

    $user = User::where('email', 'john@example.com')->firstOrFail();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/vehicles/2');

    $response->assertOk()
        ->assertJsonPath('vehicle.id', 2)
        ->assertJsonPath('vehicle.owner_id', $user->id);
});
