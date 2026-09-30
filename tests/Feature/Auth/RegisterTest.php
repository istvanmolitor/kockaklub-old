<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration page can be rendered', function () {
    $this->get(route('user.register'))->assertSuccessful();
});

test('new users can register', function () {
    $response = $this->post(route('user.register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    $response->assertRedirect(config('user.redirects.login'));
});

test('registration requires a unique email', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->post(route('user.register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertInvalid('email');
    $this->assertGuest();
});
