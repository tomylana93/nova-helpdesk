<?php

use App\Enums\UserStatus;
use App\Models\User;

beforeEach(function (): void {
    $this->artisan('permission:sync-roles')->assertSuccessful();
});

it('allows active users to access protected pages', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

it('logs out and redirects disabled users from protected pages', function (): void {
    $user = User::factory()->withStatus(UserStatus::Disable)->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => UserStatus::Disable->message(),
    ]);
    $this->assertGuest();
});

it('logs out and redirects suspended users from protected pages', function (): void {
    $user = User::factory()->withStatus(UserStatus::Suspend)->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => UserStatus::Suspend->message(),
    ]);
    $this->assertGuest();
});

it('allows guests to pass through the middleware', function (): void {
    $this->get(route('login'))->assertOk();
});
