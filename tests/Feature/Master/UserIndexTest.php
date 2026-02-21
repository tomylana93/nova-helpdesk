<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated user can open master users index', function () {
    $authenticatedUser = User::factory()->active()->create();
    User::factory()->active()->count(2)->create();
    User::factory()->disabled()->create();

    $this->actingAs($authenticatedUser)
        ->get(route('master.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('master/users/Index')
            ->has('users.data', 4)
            ->has('users.data.0.id')
            ->has('users.data.0.name')
            ->has('users.data.0.email')
            ->has('users.data.0.status')
            ->has('users.data.0.status_value')
            ->has('users.meta.current_page')
            ->has('users.meta.per_page')
            ->has('users.meta.last_page')
            ->has('users.meta.from')
            ->has('users.meta.to')
            ->has('users.meta.total')
        );
});
