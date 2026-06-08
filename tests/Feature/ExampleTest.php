<?php

use App\Models\User;

test('guests are redirected to the login page from home', function (): void {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('authenticated users are redirected to the dashboard from home', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertRedirect(route('dashboard', absolute: false));
});
