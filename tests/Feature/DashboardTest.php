<?php

use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Date;
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

test('dashboard returns role-shaped props with period defaults', function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));

    $this->actingAs(createRequesterUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('role', 'requester')
            ->where('period.mode', 'monthly')
            ->where('period.month', 6)
            ->where('period.year', 2026)
            ->has('live')
            ->has('periodMetrics')
            ->has('trend.points')
            ->has('breakdown.segments')
            ->where('compliance', null)
        );
});

test('dashboard accepts yearly period from query', function (): void {
    $this->actingAs(createAgentUser())
        ->get(route('dashboard', ['mode' => 'yearly', 'year' => 2025]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('period.mode', 'yearly')
            ->where('period.month', null)
            ->where('period.year', 2025)
            ->where('trend.granularity', 'month')
            ->has('trend.points', 12)
        );
});

test('dashboard clamps invalid period query to safe defaults', function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));

    $this->actingAs(createRequesterUser())
        ->get(route('dashboard', ['mode' => 'weekly', 'month' => 99, 'year' => 1800]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('period.mode', 'monthly')
            ->where('period.month', 6)
            ->where('period.year', 2026)
        );
});
