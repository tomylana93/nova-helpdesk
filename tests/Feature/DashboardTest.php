<?php

use App\Models\User;
use App\Settings\GeneralSettings;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function (): void {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('locale', app()->getLocale()),
        );
});

test('dashboard shares auth abilities for admin users', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auth.abilities.manage_settings', true)
            ->where('auth.abilities.view_users', true)
            ->where('auth.abilities.create_users', true)
            ->where('auth.abilities.update_users', true),
        );
});

test('dashboard shares auth abilities for super admins', function (): void {
    $user = grantSuperAdmin(User::factory()->create());

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auth.abilities.manage_settings', true)
            ->where('auth.abilities.view_users', true)
            ->where('auth.abilities.create_users', true)
            ->where('auth.abilities.update_users', true),
        );
});

test('dashboard uses locale from general settings', function (): void {
    $settings = app(GeneralSettings::class);
    $settings->site_locale = 'id';
    $settings->save();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('lang="id"', escape: false)
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('locale', 'id'),
        );
});
