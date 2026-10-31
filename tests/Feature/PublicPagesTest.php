<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

/*
| Smoke tests for the public pages. Inertia pages assert the component and
| shared props. Auth pages are covered in tests/Feature/Auth.
*/

it('renders the Inertia pages', function (string $uri, string $component) {
    $this->get($uri)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->where('name', 'Renice')
            ->where('auth.user', null));
})->with([
    'home' => ['/', 'home'],
    'about' => ['/sobre', 'about'],
]);

it('shares only non-sensitive fields of the signed-in user', function () {
    $user = User::factory()->create(['name' => 'Cliente Demo']);

    $this->actingAs($user)
        ->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user', [
                'id' => $user->id,
                'name' => 'Cliente Demo',
                'email' => $user->email,
                'is_admin' => false,
                'avatar_url' => null,
            ]));
});

it('exposes a health check', function () {
    $this->get('/up')->assertOk();
});

it('sends guests away from the admin area', function () {
    $this->get('/adm')->assertRedirect('/');
});
