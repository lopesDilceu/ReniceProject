<?php

/*
| The WebAuthn ceremony itself (signatures from a real authenticator) is
| laravel/passkeys' job and covered by its own suite. These tests cover what
| this app configures: who may start each ceremony, the relying party, the
| management screen and what happens to passkeys with the account.
*/

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Passkeys\Passkey;

uses(RefreshDatabase::class);

function passkeyFor(User $user, string $name = 'Chrome no Windows'): Passkey
{
    $passkey = new Passkey([
        'name' => $name,
        'credential_id' => base64_encode(random_bytes(16)),
        // Google Password Manager's AAGUID, labelled by the package.
        'credential' => ['aaguid' => 'ea9b8d66-4d01-1d21-3ce4-b6b48cb575d4'],
    ]);
    $passkey->user()->associate($user)->save();

    return $passkey;
}

it('offers a sign-in challenge for this site to guests', function () {
    $this->getJson(route('passkey.login-options'))
        ->assertOk()
        ->assertJsonPath('options.rpId', parse_url(config('app.url'), PHP_URL_HOST))
        ->assertJsonStructure(['options' => ['challenge']]);

    $this->actingAs(User::factory()->create())->getJson(route('passkey.login-options'))->assertRedirect();
});

it('refuses a sign-in without a valid credential', function () {
    $this->getJson(route('passkey.login-options'));

    $this->postJson(route('passkey.login'), ['credential' => ['id' => 'forged']])->assertUnprocessable();

    $this->assertGuest();
});

it('asks for the password again before registering a passkey', function () {
    $user = User::factory()->create(['name' => 'Joana Lima', 'email' => 'joana@example.com']);

    $this->actingAs($user)->getJson(route('passkey.registration-options'))->assertStatus(423);

    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
        ->getJson(route('passkey.registration-options'))
        ->assertOk()
        ->assertJsonPath('options.user.name', 'joana@example.com')
        ->assertJsonPath('options.user.displayName', 'Joana Lima');
});

it('lists the customer\'s passkeys with the authenticator name', function () {
    $user = User::factory()->create();
    passkeyFor($user);
    passkeyFor(User::factory()->create(), 'Someone else');

    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('account.security'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('passkeys', 1)
            ->where('passkeys.0.name', 'Chrome no Windows')
            ->where('passkeys.0.authenticator', 'Google Password Manager'));
});

it('removes only the customer\'s own passkeys', function () {
    $user = User::factory()->create();
    $mine = passkeyFor($user);
    $theirs = passkeyFor(User::factory()->create());
    $confirmed = fn () => $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

    $confirmed()->delete(route('passkey.destroy', $theirs))->assertForbidden();
    $confirmed()->delete(route('passkey.destroy', $mine))->assertRedirect();

    expect(Passkey::pluck('id')->all())->toBe([$theirs->id]);
});

it('deletes the passkeys when the account is closed', function () {
    $user = User::factory()->create();
    passkeyFor($user);

    $user->delete();

    expect(Passkey::count())->toBe(0)
        ->and($user->fresh()->trashed())->toBeTrue();
});

it('tells password managers where to manage passkeys', function () {
    $this->getJson('/.well-known/passkey-endpoints')
        ->assertOk()
        ->assertExactJson(['enroll' => route('account.security'), 'manage' => route('account.security')]);
});
