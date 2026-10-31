<?php

use App\Mcp\Servers\StoreServer;
use App\Mcp\Tools\StartCheckout;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->address = Address::factory()->for($this->customer)->create();
    $this->product = Product::factory()->inStock(10)->create(['price_cents' => 1800]);
    $this->cart = Cart::factory()->for($this->customer)->create();
    $this->cart->items()->create(['product_id' => $this->product->id, 'quantity' => 2]);

    // What the agent gets back from start-checkout.
    $this->link = function (): string {
        $agent = User::findOrFail($this->customer->id);
        $agent->withAccessToken($agent->createToken('agent')->accessToken);

        $url = null;
        StoreServer::actingAs($agent)->tool(StartCheckout::class, ['address_id' => $this->address->id])
            ->assertStructuredContent(function ($json) use (&$url) {
                $url = $json->toArray()['confirmation_url'];
                $json->etc();
            });
        auth()->forgetGuards();

        return $url;
    };
});

it('shows the customer what the agent prepared', function () {
    $link = ($this->link)();

    $this->actingAs($this->customer)->get($link)
        ->assertInertia(fn (Assert $page) => $page
            ->component('checkout/confirm')
            ->where('total_cents', 3600)
            ->where('address.id', $this->address->id)
            ->has('confirmUrl'));
});

it('places the order only when the customer confirms, once', function () {
    $link = ($this->link)();

    $this->actingAs($this->customer)->post($link)
        ->assertRedirect(route('orders.show', Order::sole()));

    expect(Order::sole()->user->is($this->customer))->toBeTrue()
        ->and(Order::sole()->payment)->not->toBeNull()
        ->and($this->product->fresh()->stock)->toBe(8);

    // The cart is now empty, so the same link places nothing more.
    $this->actingAs($this->customer)->post($link)->assertRedirect(route('checkout.show'));
    expect(Order::count())->toBe(1);
});

it('refuses the link once the cart has changed', function () {
    $link = ($this->link)();
    $this->cart->items()->sole()->update(['quantity' => 5]);

    $this->actingAs($this->customer)->post($link)->assertRedirect(route('checkout.show'));

    expect(Order::count())->toBe(0);
});

it('refuses the link when a price has changed', function () {
    $link = ($this->link)();
    $this->product->update(['price_cents' => 2500]);

    $this->actingAs($this->customer)->post($link)->assertRedirect(route('checkout.show'));

    expect(Order::count())->toBe(0);
});

it('cannot be used by anyone else', function () {
    $link = ($this->link)();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->post($link)->assertForbidden();

    expect(Order::count())->toBe(0);
});

it('sends a signed-out customer to sign in first', function () {
    $this->get(($this->link)())->assertRedirect(route('login'));
});

it('cannot be tampered with or used after 15 minutes', function () {
    $link = ($this->link)();

    $this->actingAs($this->customer)->post(str_replace("address={$this->address->id}", 'address=999', $link))->assertForbidden();

    $this->travel(16)->minutes();
    $this->actingAs($this->customer)->post($link)->assertForbidden();

    expect(Order::count())->toBe(0);
});
