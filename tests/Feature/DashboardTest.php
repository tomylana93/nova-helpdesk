<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
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

test('dashboard returns correct inertia props based on user role', function (): void {
    // 1. Requester
    $requester = createRequesterUser();
    $this->actingAs($requester)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('role', 'requester')
            ->has('metrics')
            ->has('recentTickets')
            ->has('charts')
        );

    // 2. IT Agent
    $agent = createAgentUser();
    $this->actingAs($agent)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('role', 'it_agent')
            ->has('metrics')
            ->has('recentTickets')
            ->has('charts')
        );

    // 3. Super Admin
    $admin = grantSuperAdmin(User::factory()->create());
    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('role', 'super_admin')
            ->has('metrics')
            ->has('recentTickets')
            ->has('charts')
        );
});

test('dashboard recent tickets include sla payload', function (): void {
    $this->travelTo(Date::parse('2026-06-11 10:00:00'));

    $requester = createRequesterUser();

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'subject' => 'Dashboard SLA Ticket',
        'status' => TicketStatus::Open,
        'first_response_due_at' => Date::parse('2026-06-11 10:45:00'),
        'resolution_due_at' => Date::parse('2026-06-11 10:20:00'),
    ]);

    $this
        ->actingAs($requester)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->has('recentTickets', 1)
            ->where('recentTickets.0.subject', 'Dashboard SLA Ticket')
            ->where('recentTickets.0.sla.firstResponse.state', 'on_track')
            ->where('recentTickets.0.sla.firstResponse.remainingSeconds', 2700)
            ->where('recentTickets.0.sla.resolution.state', 'due_soon')
            ->where('recentTickets.0.sla.resolution.remainingSeconds', 1200)
        );
});
