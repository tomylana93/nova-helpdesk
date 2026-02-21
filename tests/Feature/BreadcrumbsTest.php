<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('dashboard shares dashboard breadcrumb', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('breadcrumbs', 1)
            ->where('breadcrumbs.0.title', __('breadcrumbs.dashboard'))
            ->where('breadcrumbs.0.href', route('dashboard'))
        );
});

test('master index shares dashboard and master breadcrumbs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('master.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('breadcrumbs', 2)
            ->where('breadcrumbs.0.title', __('breadcrumbs.dashboard'))
            ->where('breadcrumbs.0.href', route('dashboard'))
            ->where('breadcrumbs.1.title', __('breadcrumbs.master'))
            ->where('breadcrumbs.1.href', route('master.index'))
        );
});

test('master users index shares dashboard, master, and users breadcrumbs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('master.users.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('breadcrumbs', 3)
            ->where('breadcrumbs.0.title', __('breadcrumbs.dashboard'))
            ->where('breadcrumbs.0.href', route('dashboard'))
            ->where('breadcrumbs.1.title', __('breadcrumbs.master'))
            ->where('breadcrumbs.1.href', route('master.index'))
            ->where('breadcrumbs.2.title', __('breadcrumbs.users'))
            ->where('breadcrumbs.2.href', route('master.users.index'))
        );
});

test('master users create shares dashboard, master, users, and create breadcrumbs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('master.users.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('breadcrumbs', 4)
            ->where('breadcrumbs.0.title', __('breadcrumbs.dashboard'))
            ->where('breadcrumbs.0.href', route('dashboard'))
            ->where('breadcrumbs.1.title', __('breadcrumbs.master'))
            ->where('breadcrumbs.1.href', route('master.index'))
            ->where('breadcrumbs.2.title', __('breadcrumbs.users'))
            ->where('breadcrumbs.2.href', route('master.users.index'))
            ->where('breadcrumbs.3.title', __('breadcrumbs.user_create'))
            ->where('breadcrumbs.3.href', route('master.users.create'))
        );
});

test('master users edit shares dashboard, master, users, and edit breadcrumbs', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('master.users.edit', $targetUser))
        ->assertInertia(fn (Assert $page) => $page
            ->has('breadcrumbs', 4)
            ->where('breadcrumbs.0.title', __('breadcrumbs.dashboard'))
            ->where('breadcrumbs.0.href', route('dashboard'))
            ->where('breadcrumbs.1.title', __('breadcrumbs.master'))
            ->where('breadcrumbs.1.href', route('master.index'))
            ->where('breadcrumbs.2.title', __('breadcrumbs.users'))
            ->where('breadcrumbs.2.href', route('master.users.index'))
            ->where('breadcrumbs.3.title', __('breadcrumbs.user_edit'))
            ->where('breadcrumbs.3.href', route('master.users.edit', $targetUser))
        );
});
