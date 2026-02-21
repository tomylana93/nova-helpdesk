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
