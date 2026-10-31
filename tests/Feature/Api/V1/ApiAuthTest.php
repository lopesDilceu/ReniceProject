<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create(['email' => 'joana@example.com']);
    $this->issue = fn (array $overrides = []) => $this->postJson('/api/v1/tokens', [
        'email' => 'joana@example.com',
        'password' => 'password',
        'device_name' => 'Integração ERP',
        ...$overrides,
    ]);

    // The auth guard caches the user between requests in one test; each
    // request must authenticate with its own token.
    $this->as = function (string $token) {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    };
});

describe('tokens', function () {
    it('are issued for 30 days with both scopes by default', function () {
        $this->freezeSecond();

        ($this->issue)()->assertCreated()
            ->assertJsonPath('abilities', ['read', 'write'])
            ->assertJsonPath('expires_at', now()->addDays(30)->toIso8601String());

        expect(PersonalAccessToken::sole()->name)->toBe('Integração ERP');
    });

    it('accept the e-mail in any case', function () {
        ($this->issue)(['email' => 'JOANA@example.com'])->assertCreated();
    });

    it('are refused with a wrong password, without saying which field was wrong', function () {
        ($this->issue)(['password' => 'wrong'])->assertUnprocessable()->assertJsonValidationErrors(['email']);
        ($this->issue)(['email' => 'nobody@example.com'])->assertUnprocessable()->assertJsonValidationErrors(['email']);

        expect(PersonalAccessToken::count())->toBe(0);
    });

    it('need the two-factor code when the account has it on', function () {
        $secret = app(Google2FA::class)->generateSecretKey();
        $this->customer->forceFill(['two_factor_secret' => encrypt($secret), 'two_factor_confirmed_at' => now()])->save();

        ($this->issue)()->assertUnprocessable()->assertJsonValidationErrors(['code']);
        ($this->issue)(['code' => '000000'])->assertUnprocessable()->assertJsonValidationErrors(['code']);
        ($this->issue)(['code' => app(Google2FA::class)->getCurrentOtp($secret)])->assertCreated();
    });

    it('allow five attempts per minute per e-mail', function () {
        foreach (range(1, 5) as $attempt) {
            ($this->issue)(['password' => 'wrong']);
        }

        ($this->issue)()->assertTooManyRequests();
    });

    it('can be revoked', function () {
        $token = ($this->issue)()->json('token');

        ($this->as)($token)->deleteJson('/api/v1/tokens/current')->assertNoContent();

        ($this->as)($token)->getJson('/api/v1/cart')->assertUnauthorized();
    });

    it('stop working when they expire', function () {
        $token = ($this->issue)()->json('token');

        $this->travel(31)->days();

        ($this->as)($token)->getJson('/api/v1/cart')->assertUnauthorized();
    });

    it('are revoked when the account is closed', function () {
        $token = ($this->issue)()->json('token');

        $this->customer->delete();

        ($this->as)($token)->getJson('/api/v1/cart')->assertUnauthorized();
        expect(PersonalAccessToken::count())->toBe(0);
    });
});

describe('scopes', function () {
    it('lets a read-only token read but not write', function () {
        $token = ($this->issue)(['abilities' => ['read']])->assertJsonPath('abilities', ['read'])->json('token');
        $product = Product::factory()->inStock(5)->create();

        ($this->as)($token)->getJson('/api/v1/cart')->assertOk();
        ($this->as)($token)->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertForbidden();

        expect(Cart::count())->toBe(0);
    });

    it('refuses unknown scopes', function () {
        ($this->issue)(['abilities' => ['admin']])->assertUnprocessable()->assertJsonValidationErrors(['abilities.0']);
    });
});

it('limits each token to 60 requests per minute without affecting the others', function () {
    $busy = ($this->issue)()->json('token');
    $other = ($this->issue)(['device_name' => 'App'])->json('token');

    foreach (range(1, 60) as $request) {
        ($this->as)($busy)->getJson('/api/v1/cart')->assertOk();
    }

    ($this->as)($busy)->getJson('/api/v1/cart')->assertTooManyRequests();
    ($this->as)($other)->getJson('/api/v1/cart')->assertOk();
});

describe('ownership', function () {
    beforeEach(function () {
        $this->token = $this->customer->createToken('tests')->plainTextToken;
        $this->stranger = User::factory()->create();
    });

    it('keeps other customers\' cart items and orders out of reach', function () {
        $product = Product::factory()->inStock(5)->create();
        $item = Cart::factory()->for($this->stranger)->create()->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        ($this->as)($this->token)->patchJson("/api/v1/cart/items/{$item->id}", ['quantity' => 5])->assertForbidden();
        ($this->as)($this->token)->deleteJson("/api/v1/cart/items/{$item->id}")->assertForbidden();
        ($this->as)($this->token)->getJson('/api/v1/orders/'.Order::factory()->for($this->stranger)->create()->id)->assertForbidden();

        expect($item->fresh()->quantity)->toBe(1);
    });

    it('only ships to the customer\'s own addresses', function () {
        Cart::factory()->for($this->customer)->create()->items()->create(['product_id' => Product::factory()->inStock(5)->create()->id, 'quantity' => 1]);

        ($this->as)($this->token)->postJson('/api/v1/orders', ['address_id' => Address::factory()->for($this->stranger)->create()->id])
            ->assertUnprocessable()->assertJsonValidationErrors(['address_id']);

        expect(Order::count())->toBe(0);
    });

    it('requires a confirmed e-mail to place orders', function () {
        $unverified = User::factory()->unverified()->create()->createToken('tests')->plainTextToken;

        ($this->as)($unverified)->postJson('/api/v1/orders', ['address_id' => 1])->assertForbidden();
    });

    it('reports an empty cart and missing stock as validation errors', function () {
        $address = Address::factory()->for($this->customer)->create();

        ($this->as)($this->token)->postJson('/api/v1/orders', ['address_id' => $address->id])
            ->assertUnprocessable()->assertJsonValidationErrors(['cart' => 'O carrinho está vazio.']);
    });
});
