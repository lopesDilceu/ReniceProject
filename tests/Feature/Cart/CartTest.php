<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create();
});

it('sends guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect(route('login'));
})->with([
    ['get', '/carrinho'],
    ['post', '/carrinho/itens'],
    ['patch', '/carrinho/itens/1'],
    ['delete', '/carrinho/itens/1'],
]);

describe('adding', function () {
    it('creates the cart and merges repeated additions', function () {
        $product = Product::factory()->inStock(10)->create();

        $this->actingAs($this->customer)->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertSessionHasNoErrors();
        $this->actingAs($this->customer)->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 3]);

        expect($this->customer->cart->items()->sole())
            ->product_id->toBe($product->id)
            ->quantity->toBe(5);
    });

    it('never adds more than is in stock', function () {
        $product = Product::factory()->inStock(3)->create();

        $this->actingAs($this->customer)->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 4])
            ->assertSessionHasErrors('quantity');

        expect($this->customer->cart?->items()->count() ?? 0)->toBe(0);
    });

    it('rejects invalid products and quantities', function (Closure $payload) {
        $this->actingAs($this->customer)->post(route('cart.items.store'), $payload())->assertSessionHasErrors();
    })->with([
        'zero' => fn () => ['product_id' => Product::factory()->inStock()->create()->id, 'quantity' => 0],
        'negative' => fn () => ['product_id' => Product::factory()->inStock()->create()->id, 'quantity' => -2],
        'over 99' => fn () => ['product_id' => Product::factory()->inStock(500)->create()->id, 'quantity' => 100],
        'removed product' => fn () => ['product_id' => tap(Product::factory()->inStock()->create())->delete()->id, 'quantity' => 1],
        'unknown product' => fn () => ['product_id' => 999, 'quantity' => 1],
    ]);
});

it('shows only the customer\'s own items and total', function () {
    $product = Product::factory()->inStock()->create(['price_cents' => 1000]);
    Cart::factory()->for($this->customer)->create()->items()->create(['product_id' => $product->id, 'quantity' => 2]);
    Cart::factory()->create()->items()->create(['product_id' => $product->id, 'quantity' => 7]);

    $this->actingAs($this->customer)->get(route('cart.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('cart/show')
            ->has('items', 1)
            ->where('items.0.quantity', 2)
            ->where('total_cents', 2000)
            ->where('cartCount', 2));
})->issue(23);

describe('SEC-03 cart items belong to their owner', function () {
    beforeEach(function () {
        $this->theirs = CartItem::factory()->create(['quantity' => 1]);
        $this->mine = Cart::factory()->for($this->customer)->create()
            ->items()->create(['product_id' => Product::factory()->inStock(5)->create()->id, 'quantity' => 1]);
    });

    it('forbids changing another customer\'s item', function () {
        $this->actingAs($this->customer)->patch(route('cart.items.update', $this->theirs), ['quantity' => 3])->assertForbidden();

        expect($this->theirs->fresh()->quantity)->toBe(1);
    })->issue(19);

    it('forbids deleting another customer\'s item', function () {
        $this->actingAs($this->customer)->delete(route('cart.items.destroy', $this->theirs))->assertForbidden();

        expect($this->theirs->fresh())->not->toBeNull();
    })->issue(19);

    it('rejects zero, negative and above-stock quantities', function (int $quantity) {
        $this->actingAs($this->customer)->patch(route('cart.items.update', $this->mine), ['quantity' => $quantity])
            ->assertSessionHasErrors('quantity');

        expect($this->mine->fresh()->quantity)->toBe(1);
    })->with([0, -1, 6])->issue(19);

    it('lets the owner change and remove their item', function () {
        $this->actingAs($this->customer)->patch(route('cart.items.update', $this->mine), ['quantity' => 4])->assertSessionHasNoErrors();
        expect($this->mine->fresh()->quantity)->toBe(4);

        $this->actingAs($this->customer)->delete(route('cart.items.destroy', $this->mine));
        expect($this->mine->fresh())->toBeNull();
    });
});
