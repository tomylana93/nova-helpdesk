<?php

use App\Models\User;
use App\Settings\GeneralSettings;
use App\Settings\StyleSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('style settings page is displayed', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $this->actingAs($user)
        ->get(route('admin.settings.style.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('admin/settings/style/Edit')
            ->where('styleSettings.site_logo_style', 'icon')
            ->where('styleSettings.site_auth_layout', 'simple')
            ->where('styleSettings.site_layout', 'sidebar')
            ->where('styleSettings.site_theme', 'zinc')
            ->where('styleSettings.site_font', 'inter')
            ->has('logoStyleOptions', 2)
            ->has('authLayoutOptions', 3)
            ->has('layoutOptions', 2)
            ->has('themeOptions', 10)
            ->has('fontOptions', 5)
            ->has('brandingFiles.icon', 0)
            ->has('brandingFiles.logo', 0)
            ->has('brandingFiles.favicon', 0)
            ->where('fontOptions.0.heading', 'Inter')
            ->where('fontOptions.0.body', 'Inter'),
        );
});

test('style settings can be updated and applied to runtime', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.settings.style.update'), [
            'site_logo_style' => 'logo',
            'site_auth_layout' => 'split',
            'site_layout' => 'header',
            'site_theme' => 'emerald',
            'site_font' => 'space-grotesk-inter',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('inertia.flash_data.toast.type', 'success')
        ->assertSessionHas('inertia.flash_data.toast.message', __('admin.settings.style.status.saved'))
        ->assertRedirect(route('admin.settings.style.edit'));

    $settings = app(StyleSettings::class)->refresh();

    expect($settings->site_logo_style)->toBe('logo');
    expect($settings->site_auth_layout)->toBe('split');
    expect($settings->site_layout)->toBe('header');
    expect($settings->site_theme)->toBe('emerald');
    expect($settings->site_font)->toBe('space-grotesk-inter');

    app(GeneralSettings::class)->site_name = 'Nova Ops';
    app(GeneralSettings::class)->save();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-theme="emerald"', escape: false)
        ->assertSee('data-font="space-grotesk-inter"', escape: false)
        ->assertSee('id="site-font-stylesheet"', escape: false)
        ->assertSee('https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|inter:400,500', escape: false)
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('name', 'Nova Ops')
            ->where('style.site_logo_style', 'logo')
            ->where('style.site_auth_layout', 'split')
            ->where('style.site_layout', 'header')
            ->where('style.site_theme', 'emerald')
            ->where('style.site_font', 'space-grotesk-inter')
            ->where('branding.icon', '/assets/images/icon.png')
            ->where('branding.icon_alt', '/assets/images/icon_alt.png')
            ->where('branding.logo', '/assets/images/logo.png')
            ->where('branding.logo_alt', '/assets/images/logo_alt.png')
            ->where('branding.favicon_any', '/favicon.ico'),
        );

    auth()->logout();

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('data-theme="emerald"', escape: false)
        ->assertSee('data-font="space-grotesk-inter"', escape: false)
        ->assertSee('Nova Ops', escape: false);
});

test('style settings can promote uploaded branding assets', function (): void {
    Storage::fake('public');

    $user = grantAdminPermissions(User::factory()->create());

    $iconUploadId = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('icon.png', 128, 128),
        ])
        ->json('id');

    $faviconUploadId = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('favicon.png', 64, 64),
        ])
        ->json('id');

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.settings.style.update'), [
            'site_logo_style' => 'icon',
            'site_auth_layout' => 'simple',
            'site_layout' => 'sidebar',
            'site_theme' => 'zinc',
            'site_font' => 'inter',
            'site_icon_upload_id' => $iconUploadId,
            'site_favicon_upload_id' => $faviconUploadId,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.settings.style.edit'));

    $settings = app(StyleSettings::class)->refresh();

    expect($settings->site_icon_path)->not->toBe('');
    expect($settings->site_favicon_path)->not->toBe('');

    Storage::disk('public')->assertExists($settings->site_icon_path);
    Storage::disk('public')->assertExists($settings->site_favicon_path);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('branding.icon', Storage::disk('public')->url($settings->site_icon_path))
            ->where('branding.favicon', Storage::disk('public')->url($settings->site_favicon_path)),
        );
});

test('style settings require valid enum values', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($user)
        ->from(route('admin.settings.style.edit'))
        ->patch(route('admin.settings.style.update'), [
            'site_logo_style' => 'wordmark',
            'site_auth_layout' => 'stacked',
            'site_layout' => 'grid',
            'site_theme' => 'light',
            'site_font' => 'geist',
        ]);

    $response
        ->assertSessionHasErrors([
            'site_logo_style',
            'site_auth_layout',
            'site_layout',
            'site_theme',
            'site_font',
        ])
        ->assertRedirect(route('admin.settings.style.edit'));
});
