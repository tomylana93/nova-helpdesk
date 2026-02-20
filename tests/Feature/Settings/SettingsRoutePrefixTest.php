<?php

use App\Models\User;

test('legacy settings endpoints are not available', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/profile')->assertNotFound();
    $this->actingAs($user)->patch('/settings/profile')->assertNotFound();
    $this->actingAs($user)->delete('/settings/profile')->assertNotFound();
    $this->actingAs($user)->get('/settings/password')->assertNotFound();
    $this->actingAs($user)->put('/settings/password')->assertNotFound();
    $this->actingAs($user)->get('/settings/appearance')->assertNotFound();
    $this->actingAs($user)->get('/settings/two-factor')->assertNotFound();
});
