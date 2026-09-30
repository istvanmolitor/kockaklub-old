<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('user.profile.edit'))->assertRedirect(route('user.login'));
});

test('profile page can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('user.profile.edit'))->assertSuccessful();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('user.profile.update'), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $response->assertRedirect();
    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
});

test('password can be updated from the profile page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('user.profile.password'), [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
});

test('account can be deleted from the profile page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('user.profile.destroy'), [
        'password' => 'password',
    ]);

    $response->assertRedirect(config('user.redirects.logout'));
    $this->assertGuest();
    $this->assertModelMissing($user);
});
