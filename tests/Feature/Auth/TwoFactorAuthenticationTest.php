<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

function otp(User $user): string
{
    return app(Google2FA::class)->getCurrentOtp(decrypt($user->fresh()->two_factor_secret));
}

function userWithTwoFactor(): User
{
    return User::factory()->create([
        'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-one', 'recovery-code-two'])),
        'two_factor_confirmed_at' => now(),
    ]);
}

describe('security page', function () {
    it('asks for the password again before showing it', function () {
        $this->actingAs(User::factory()->create())->get(route('account.security'))
            ->assertRedirect(route('password.confirm'));
    });

    it('shows the two-factor state and the passkeys', function () {
        $this->actingAs(User::factory()->create())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('account.security'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('account/security')
                ->where('twoFactorEnabled', false)
                ->where('twoFactorPending', false)
                ->has('passkeys', 0));
    });
});

describe('setting it up', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->confirmed = fn () => $this->actingAs($this->user)->withSession(['auth.password_confirmed_at' => time()]);
    });

    it('is enabled only after the first valid code', function () {
        ($this->confirmed)()->get(route('account.security'));
        ($this->confirmed)()->post(route('two-factor.enable'))->assertRedirect();

        expect($this->user->fresh()->two_factor_secret)->not->toBeNull()
            ->and($this->user->fresh()->hasEnabledTwoFactorAuthentication())->toBeFalse();

        ($this->confirmed)()->get(route('account.security'))
            ->assertInertia(fn (Assert $page) => $page->where('twoFactorPending', true));

        ($this->confirmed)()->post(route('two-factor.confirm'), ['code' => otp($this->user)])->assertSessionHasNoErrors();

        expect($this->user->fresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
        ($this->confirmed)()->getJson(route('two-factor.recovery-codes'))->assertOk()->assertJsonCount(8);
    });

    it('refuses a wrong confirmation code', function () {
        ($this->confirmed)()->get(route('account.security'));
        ($this->confirmed)()->post(route('two-factor.enable'));

        ($this->confirmed)()->post(route('two-factor.confirm'), ['code' => '000000'])
            ->assertSessionHasErrorsIn('confirmTwoFactorAuthentication', ['code' => 'O código de autenticação de dois fatores informado é inválido.']);

        expect($this->user->fresh()->two_factor_confirmed_at)->toBeNull();
    });

    it('drops a setup that was never confirmed', function () {
        ($this->confirmed)()->get(route('account.security'));
        ($this->confirmed)()->post(route('two-factor.enable'));
        ($this->confirmed)()->get(route('account.security'));

        // Fortify compares PHP's time(), which Carbon's test clock does not
        // move: age the moment the setup began to simulate a later visit.
        ($this->confirmed)()->withSession(['two_factor_confirming_at' => time() - 60])
            ->get(route('account.security'))
            ->assertInertia(fn (Assert $page) => $page->where('twoFactorPending', false));

        expect($this->user->fresh()->two_factor_secret)->toBeNull();
    });

    it('requires the password again to turn it on or off', function () {
        $this->actingAs($this->user)->post(route('two-factor.enable'))->assertRedirect(route('password.confirm'));
        $this->actingAs(userWithTwoFactor())->delete(route('two-factor.disable'))->assertRedirect(route('password.confirm'));
    });

    it('can be turned off', function () {
        $user = userWithTwoFactor();

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])->delete(route('two-factor.disable'));

        expect($user->fresh()->two_factor_secret)->toBeNull()
            ->and($user->fresh()->hasEnabledTwoFactorAuthentication())->toBeFalse();
    });
});

describe('signing in', function () {
    beforeEach(function () {
        $this->user = userWithTwoFactor();
        $this->signIn = fn () => $this->post(route('login.store'), ['email' => $this->user->email, 'password' => 'password']);
    });

    it('asks for the code after the password', function () {
        ($this->signIn)()->assertRedirect(route('two-factor.login'));

        $this->assertGuest();
        $this->get(route('two-factor.login'))->assertInertia(fn (Assert $page) => $page->component('auth/two-factor-challenge'));
    });

    it('signs in with the code from the app', function () {
        ($this->signIn)();

        $this->post(route('two-factor.login.store'), ['code' => otp($this->user)])->assertRedirect('/');

        $this->assertAuthenticatedAs($this->user);
    });

    it('signs in once with each recovery code', function () {
        ($this->signIn)();
        $this->post(route('two-factor.login.store'), ['recovery_code' => 'recovery-code-one'])->assertRedirect('/');
        $this->assertAuthenticatedAs($this->user);

        expect($this->user->fresh()->recoveryCodes())->not->toContain('recovery-code-one')
            ->toContain('recovery-code-two');
    });

    it('refuses a wrong code', function () {
        ($this->signIn)();

        $this->post(route('two-factor.login.store'), ['code' => '000000'])->assertSessionHasErrors('code');

        $this->assertGuest();
    });

    it('allows five codes per minute', function () {
        ($this->signIn)();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('two-factor.login.store'), ['code' => '000000']);
        }

        $this->post(route('two-factor.login.store'), ['code' => otp($this->user)])->assertTooManyRequests();
        $this->assertGuest();
    });
});
