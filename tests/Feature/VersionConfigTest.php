<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('exposes the application version from config', function (): void {
    expect(config('version.app'))
        ->toBeString()
        ->toMatch('/^0\.\d+\.\d+(?:-rc(?:\.\d+)?)?$/');
});

it('shares the configured application version with inertia pages', function (): void {
    config(['version.app' => '0.99.0-rc.3']);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('version', '0.99.0-rc.3'),
        );
});
