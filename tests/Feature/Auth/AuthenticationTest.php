<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('renders the login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/login')
            ->where('canResetPassword', true));
});

it('signs users in and sends them back to the store', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('home', absolute: false));

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password with a Portuguese message', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['email' => 'Essas credenciais não foram encontradas em nossos registros.']);

    $this->assertGuest();
});

it('signs users out', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('home', absolute: false));

    $this->assertGuest();
});

it('keeps the v1 URLs working', function (string $old, string $new) {
    $this->get($old)->assertRedirect($new)->assertStatus(301);
})->with([
    ['/usuarios/login', '/login'],
    ['/usuarios/cadastro', '/register'],
]);

describe('SEC-06 authentication hardening', function () {
    it('throttles repeated failed logins', function () {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertTooManyRequests();

        $this->assertGuest();
    })->issue(22);

    it('regenerates the session on login', function () {
        $user = User::factory()->create();
        $this->get(route('login'));
        $before = session()->getId();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

        expect(session()->getId())->not->toBe($before);
    })->issue(22);

    it('invalidates the session and rotates the CSRF token on logout', function () {
        $this->actingAs(User::factory()->create())->get(route('home'));
        $session = session()->getId();
        $token = session()->token();

        $this->post(route('logout'));

        expect(session()->getId())->not->toBe($session)
            ->and(session()->token())->not->toBe($token);
    })->issue(22);

    afterEach(fn () => RateLimiter::clear('login'));
});
