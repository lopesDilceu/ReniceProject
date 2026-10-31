<?php

use App\Actions\Checkout\PlaceOrder;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Exceptions\EmptyCart;
use App\Exceptions\InsufficientStock;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->address = Address::factory()->for($this->customer)->create();
    $this->cart = Cart::factory()->for($this->customer)->create();
    $this->placeOrder = app(PlaceOrder::class);
});

function addToCart(Cart $cart, Product $product, int $quantity): void
{
    $cart->items()->create(['product_id' => $product->id, 'quantity' => $quantity]);
}

it('turns the cart into a pending order', function () {
    $small = Product::factory()->inStock(10)->create(['name' => 'Copo 300ml gelo pequeno', 'price_cents' => 1125]);
    $medium = Product::factory()->inStock(10)->create(['name' => 'Copo 300ml gelo médio', 'price_cents' => 1800]);
    addToCart($this->cart, $small, 2);
    addToCart($this->cart, $medium, 1);

    $order = $this->placeOrder->handle($this->customer, $this->address);

    expect($order)
        ->status->toBe(OrderStatus::PendingPayment)
        ->total_cents->toBe(2 * 1125 + 1800)
        ->shipping_address->toBe($this->address->toSnapshot())
        ->and($order->user->is($this->customer))->toBeTrue()
        ->and($order->items)->toHaveCount(2)
        ->and($order->items->firstWhere('product_id', $small->id))
        ->product_name->toBe('Copo 300ml gelo pequeno')
        ->unit_price_cents->toBe(1125)
        ->quantity->toBe(2);
});

it('takes the sold quantities out of stock through the ledger', function () {
    $product = Product::factory()->inStock(10)->create();
    addToCart($this->cart, $product, 3);

    $order = $this->placeOrder->handle($this->customer, $this->address);

    $movement = $product->inventoryMovements()->sole();

    expect($product->fresh()->stock)->toBe(7)
        ->and($movement->type)->toBe(InventoryMovementType::Reservation)
        ->and($movement->quantity)->toBe(-3)
        ->and($movement->source->is($order))->toBeTrue();
});

it('empties the cart', function () {
    addToCart($this->cart, Product::factory()->inStock()->create(), 1);

    $this->placeOrder->handle($this->customer, $this->address);

    expect($this->cart->items()->count())->toBe(0);
});

it('charges the current price, not the price when the item was added', function () {
    $product = Product::factory()->inStock()->create(['price_cents' => 1000]);
    addToCart($this->cart, $product, 1);
    $product->update(['price_cents' => 1200]);

    expect($this->placeOrder->handle($this->customer, $this->address)->total_cents)->toBe(1200);
});

it('rejects an empty cart', function () {
    $this->placeOrder->handle($this->customer, $this->address);
})->throws(EmptyCart::class);

it('rejects a customer who never had a cart', function () {
    $newcomer = User::factory()->create();

    $this->placeOrder->handle($newcomer, Address::factory()->for($newcomer)->create());
})->throws(EmptyCart::class);

it('writes nothing when one item lacks stock', function () {
    $plenty = Product::factory()->inStock(10)->create();
    $scarce = Product::factory()->inStock(1)->create();
    addToCart($this->cart, $plenty, 2);
    addToCart($this->cart, $scarce, 2);

    expect(fn () => $this->placeOrder->handle($this->customer, $this->address))
        ->toThrow(InsufficientStock::class);

    expect(Order::count())->toBe(0)
        ->and($plenty->fresh()->stock)->toBe(10)
        ->and($scarce->fresh()->stock)->toBe(1)
        ->and($this->cart->items()->count())->toBe(2);
});

it('refuses products removed from the catalog', function () {
    $product = Product::factory()->inStock()->create();
    addToCart($this->cart, $product, 1);
    $product->delete();

    $this->placeOrder->handle($this->customer, $this->address);
})->throws(InsufficientStock::class);

it('refuses to ship to another customer\'s address', function () {
    addToCart($this->cart, Product::factory()->inStock()->create(), 1);

    $this->placeOrder->handle($this->customer, Address::factory()->create());
})->throws(AuthorizationException::class);

it('leaves other customers\' carts alone', function () {
    $product = Product::factory()->inStock()->create();
    addToCart($this->cart, $product, 1);
    $otherCart = Cart::factory()->create();
    addToCart($otherCart, $product, 4);

    $this->placeOrder->handle($this->customer, $this->address);

    expect($otherCart->items()->sole()->quantity)->toBe(4);
});

it('cannot place the same cart twice', function () {
    addToCart($this->cart, Product::factory()->inStock()->create(), 1);
    $this->placeOrder->handle($this->customer, $this->address);

    expect(fn () => $this->placeOrder->handle($this->customer, $this->address))
        ->toThrow(EmptyCart::class);

    expect(Order::count())->toBe(1);
})->issue(20);
