<?php

use App\Models\User;

test('appearance settings page is not available', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/appearance')
        ->assertNotFound();
});
