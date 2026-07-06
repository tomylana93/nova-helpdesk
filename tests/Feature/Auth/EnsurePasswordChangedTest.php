<?php

use App\Models\User;

it('redirects flagged users to the forced password page', function (): void {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('password.force.edit'));
});

it('allows flagged users to access the forced password routes and logout', function (): void {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->get(route('password.force.edit'))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('/');
});

it('allows users who already changed their password to access protected routes', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});
