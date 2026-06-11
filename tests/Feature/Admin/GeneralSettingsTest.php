<?php

use App\Models\User;
use App\Settings\GeneralSettings;
use Inertia\Testing\AssertableInertia as Assert;

test('general settings page is displayed', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $this->actingAs($user)
        ->get(route('admin.settings.general.edit'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('admin/settings/general/Edit')
            ->where('generalSettings.site_name', 'Nova Helpdesk')
            ->where('generalSettings.site_locale', 'en')
            ->has('localeOptions', 2)
            ->where('localeOptions.0.icon', 'us')
            ->where('localeOptions.0.value', 'en')
            ->where('localeOptions.1.icon', 'id')
            ->where('localeOptions.1.value', 'id'),
        );
});

test('general settings can be updated', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.settings.general.update'), [
            'site_name' => 'Acme Operations',
            'site_description' => 'Unified operations control center',
            'site_locale' => 'id',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('inertia.flash_data.toast.type', 'success')
        ->assertSessionHas('inertia.flash_data.toast.message', __('admin.settings.general.status.saved'))
        ->assertRedirect(route('admin.settings.general.edit'));

    $settings = app(GeneralSettings::class)->refresh();

    expect($settings->site_name)->toBe('Acme Operations');
    expect($settings->site_description)->toBe('Unified operations control center');
    expect($settings->site_locale)->toBe('id');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('locale', 'id'),
        );
});

test('site name is required when updating general settings', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($user)
        ->from(route('admin.settings.general.edit'))
        ->patch(route('admin.settings.general.update'), [
            'site_name' => '',
            'site_description' => 'Unified operations control center',
            'site_locale' => 'en',
        ]);

    $response
        ->assertSessionHasErrors('site_name')
        ->assertRedirect(route('admin.settings.general.edit'));
});

test('site locale must be valid when updating general settings', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($user)
        ->from(route('admin.settings.general.edit'))
        ->patch(route('admin.settings.general.update'), [
            'site_name' => 'Acme Operations',
            'site_description' => 'Unified operations control center',
            'site_locale' => 'fr',
        ]);

    $response
        ->assertSessionHasErrors('site_locale')
        ->assertRedirect(route('admin.settings.general.edit'));
});
