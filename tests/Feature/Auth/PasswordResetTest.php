<?php

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Features;

beforeEach(function (): void {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function (): void {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('inactive users do not receive password reset links', function (): void {
    Notification::fake();

    $user = User::factory()->withStatus(UserStatus::Disable)->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertNotSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification): true {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user): true {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('password cannot be reset with invalid token', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('inactive users cannot reset their password with a valid token', function (): void {
    $user = User::factory()->withStatus(UserStatus::Disable)->create();
    $token = Password::broker()->createToken($user);

    $response = $this
        ->from(route('password.reset', $token))
        ->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertRedirect(route('password.reset', $token))
        ->assertSessionHasErrors([
            'email' => UserStatus::Disable->message(),
        ]);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});
