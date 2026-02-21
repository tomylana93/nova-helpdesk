<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated user can open master index', function () {
    $authenticatedUser = User::factory()->active()->create();

    $this->actingAs($authenticatedUser)
        ->get(route('master.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('master/Index')
        );
});

test('guest is redirected from master index', function () {
    $this->get(route('master.index'))
        ->assertRedirect(route('login'));
});
