<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('forgot password page can be rendered', function () {
    $this->get(route('user.password.request'))->assertSuccessful();
});

test('password reset link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('user.password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password page can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();
    $this->post(route('user.password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, function (ResetPassword $notification) use ($user) {
        $response = $this->get(route('user.password.reset', ['token' => $notification->token, 'email' => $user->email]));
        $response->assertSuccessful();

        return true;
    });
});

test('password can be reset with a valid token', function () {
    Notification::fake();

    $user = User::factory()->create();
    $this->post(route('user.password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, function (ResetPassword $notification) use ($user) {
        $response = $this->post(route('user.password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('user.login'));

        return true;
    });
});
