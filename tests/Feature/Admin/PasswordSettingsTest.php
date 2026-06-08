<?php

use App\Models\User;
use App\Settings\PasswordSettings;
use Inertia\Testing\AssertableInertia as Assert;

test('password settings page is displayed', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('admin.settings.password.edit'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('admin/settings/password/Edit')
            ->has('passwordRules'),
        );
});

test('password settings page requires password confirmation', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $this->actingAs($user)
        ->get(route('admin.settings.password.edit'))
        ->assertRedirect(route('password.confirm'));
});

test('password settings update requires password confirmation', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $this->actingAs($user)
        ->patch(route('admin.settings.password.update'), [
            'default_user_password' => 'new-default-password',
            'default_user_password_confirmation' => 'new-default-password',
        ])
        ->assertRedirect(route('password.confirm'));
});

test('password settings can be updated', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->patch(route('admin.settings.password.update'), [
            'default_user_password' => 'new-default-password',
            'default_user_password_confirmation' => 'new-default-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('inertia.flash_data.toast.type', 'success')
        ->assertSessionHas('inertia.flash_data.toast.message', __('admin.settings.password.status.saved'))
        ->assertRedirect(route('admin.settings.password.edit'));

    $passwordSettings = app(PasswordSettings::class)->refresh();

    expect($passwordSettings->default_user_password)->toBe('new-default-password');
});

test('default user password confirmation is required', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->from(route('admin.settings.password.edit'))
        ->patch(route('admin.settings.password.update'), [
            'default_user_password' => 'new-default-password',
            'default_user_password_confirmation' => 'different-confirmation',
        ]);

    $response
        ->assertSessionHasErrors('default_user_password')
        ->assertRedirect(route('admin.settings.password.edit'));
});
