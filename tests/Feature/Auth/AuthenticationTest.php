<?php

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;

test('login screen can be rendered', function (): void {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionHas('inertia.flash_data.toast.type', 'warning')
        ->assertSessionHas(
            'inertia.flash_data.toast.message',
            __('auth.login.message.default_password_warning'),
        );
});

test('users with non-default passwords can authenticate without default password warning', function (): void {
    $user = User::factory()->create([
        'password' => 'new-password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'new-password',
    ]);

    $this->assertAuthenticated();
    $response
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionMissing('inertia.flash_data.toast');
});

test('users record their last login timestamp when authenticating', function (): void {
    $now = now()->startOfSecond();

    Date::setTestNow($now);

    $user = User::factory()->create([
        'password' => 'new-password',
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'new-password',
    ])->assertRedirect(route('dashboard', absolute: false));

    expect($user->refresh()->last_login_at?->toDateTimeString())->toBe($now->toDateTimeString());
});

test('users can not authenticate with invalid password', function (): void {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('disabled users can not authenticate using the login screen', function (): void {
    $user = User::factory()->withStatus(UserStatus::Disable)->create();

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => UserStatus::Disable->message(),
    ]);
    $this->assertGuest();
});

test('suspended users can not authenticate using the login screen', function (): void {
    $user = User::factory()->withStatus(UserStatus::Suspend)->create();

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => UserStatus::Suspend->message(),
    ]);
    $this->assertGuest();
});

test('inactive authenticated users are logged out from protected pages', function (): void {
    $user = User::factory()->withStatus(UserStatus::Disable)->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => UserStatus::Disable->message(),
    ]);
    $this->assertGuest();
});

test('users can logout', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});

test('users are rate limited', function (): void {
    $user = User::factory()->create();

    RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});
