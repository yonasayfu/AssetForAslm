<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows a user to obtain an api token with valid credentials', function () {
    User::factory()->withoutTwoFactor()->create([
        'email' => 'api@example.com',
        'password' => 'secret-password',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'api@example.com',
        'password' => 'secret-password',
        'device_name' => 'pest-tests',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'token',
        'token_type',
        'user' => ['id', 'name', 'email'],
    ]);
});

it('returns the authenticated profile via sanctum token', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/profile');

    $response->assertOk()->assertJsonFragment([
        'email' => $user->email,
    ]);
});
