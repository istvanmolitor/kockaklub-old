<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('login page can be rendered', function () {
    $this->get(route('user.login'))->assertSuccessful();
});

test('users can authenticate using the login page', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->post(route('user.login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(config('user.redirects.login'));
});

test('users cannot authenticate with an invalid password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->post(route('user.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('authenticated users can log out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('user.logout'));

    $this->assertGuest();
    $response->assertRedirect(config('user.redirects.logout'));
});
