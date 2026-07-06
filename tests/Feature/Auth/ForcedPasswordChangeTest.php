<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the forced password change page', function (): void {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->get(route('password.force.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('auth/ForcePasswordChange'));
});

it('updates the password, clears the flag, and redirects to dashboard', function (): void {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->put(route('password.force.update'), [
            'password' => 'NewStr0ng!Pass',
            'password_confirmation' => 'NewStr0ng!Pass',
        ])
        ->assertRedirect(route('dashboard'));

    $user->refresh();

    expect($user->must_change_password)->toBeFalse();
    expect(Hash::check('NewStr0ng!Pass', $user->password))->toBeTrue();
});

it('rejects a weak or unconfirmed password and keeps the flag', function (): void {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->from(route('password.force.edit'))
        ->put(route('password.force.update'), [
            'password' => 'weak',
            'password_confirmation' => 'nope',
        ])
        ->assertSessionHasErrors('password');

    expect($user->refresh()->must_change_password)->toBeTrue();
});
