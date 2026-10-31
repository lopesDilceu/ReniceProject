<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function signUp(array $overrides = []): array
{
    return [
        'name' => 'Cliente Novo',
        'email' => 'novo@example.com',
        'cpf' => '529.982.247-25',
        'birth_date' => '1995-05-20',
        'password' => 'password',
        'password_confirmation' => 'password',
        ...$overrides,
    ];
}

it('renders the sign-up page', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/register'));
});

it('creates the account, signs in and asks to confirm the e-mail', function () {
    Notification::fake();

    $this->post(route('register.store'), signUp())->assertRedirect(route('home', absolute: false));

    $user = User::sole();

    $this->assertAuthenticatedAs($user);
    expect($user)
        ->cpf->toBe('52998224725')
        ->birth_date->toDateString()->toBe('1995-05-20')
        ->is_admin->toBeFalse()
        ->hasVerifiedEmail()->toBeFalse();
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('validates the sign-up data', function (array $data, string $field) {
    User::factory()->create(['email' => 'taken@example.com', 'cpf' => '11144477735']);

    $this->post(route('register.store'), signUp($data))->assertSessionHasErrors($field);

    $this->assertGuest();
})->with([
    'invalid CPF' => [['cpf' => '529.982.247-24'], 'cpf'],
    'CPF already used' => [['cpf' => '111.444.777-35'], 'cpf'],
    'e-mail already used' => [['email' => 'taken@example.com'], 'email'],
    'under 16 years old' => [['birth_date' => now()->subYears(15)->toDateString()], 'birth_date'],
    'passwords do not match' => [['password_confirmation' => 'different'], 'password'],
]);

it('ignores an admin flag sent with the sign-up form', function () {
    $this->post(route('register.store'), signUp(['is_admin' => true]));

    expect(User::sole()->is_admin)->toBeFalse();
})->issue(17);
