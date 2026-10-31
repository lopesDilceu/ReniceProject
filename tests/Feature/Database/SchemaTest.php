<?php

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Phone;
use App\Models\Product;
use App\Models\Review;
use App\Models\SupplierPurchase;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds every model from its factory', function (string $model) {
    expect($model::factory()->create())->toBeInstanceOf($model);
})->with([
    User::class, Phone::class, Address::class, Product::class, SupplierPurchase::class,
    Cart::class, CartItem::class, Order::class, OrderItem::class, Review::class,
]);

describe('users', function () {
    it('never mass-assigns the admin flag', function () {
        $user = User::create([
            'name' => 'Visitante',
            'email' => 'visitante@example.com',
            'password' => 'secret-password',
            'cpf' => '12345678909',
            'birth_date' => '2000-01-01',
            'is_admin' => true,
        ]);

        expect($user->fresh()->is_admin)->toBeFalse();
    })->issue(17);

    it('rejects a duplicated CPF', function () {
        User::factory()->create(['cpf' => '12345678909']);

        User::factory()->create(['cpf' => '12345678909']);
    })->throws(UniqueConstraintViolationException::class);

    it('rejects the same phone twice for one user', function () {
        $user = User::factory()->create();
        $user->phones()->create(['number' => '38999990000']);

        $user->phones()->create(['number' => '38999990000']);
    })->throws(UniqueConstraintViolationException::class);
});

describe('carts', function () {
    it('allows one cart per user', function () {
        $user = User::factory()->create();
        Cart::factory()->for($user)->create();

        Cart::factory()->for($user)->create();
    })->throws(UniqueConstraintViolationException::class);

    it('totals only its own items', function () {
        $product = Product::factory()->inStock()->create(['price_cents' => 1000]);
        $mine = Cart::factory()->create();
        $theirs = Cart::factory()->create();
        $mine->items()->create(['product_id' => $product->id, 'quantity' => 2]);
        $theirs->items()->create(['product_id' => $product->id, 'quantity' => 7]);

        expect($mine->totalCents())->toBe(2000)
            ->and($theirs->totalCents())->toBe(7000);
    })->issue(23);
});

describe('orders', function () {
    it('casts status to the OrderStatus enum', function () {
        $order = Order::factory()->status(OrderStatus::Paid)->create();

        expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    });

    it('keeps item name and price when the product changes', function () {
        $product = Product::factory()->create(['name' => 'Copo original', 'price_cents' => 1000]);
        $item = OrderItem::factory()->for($product)->create();

        $product->update(['name' => 'Copo renomeado', 'price_cents' => 9999]);

        expect($item->fresh())
            ->product_name->toBe('Copo original')
            ->unit_price_cents->toBe(1000);
    });

    it('keeps the shipping address when the customer edits theirs', function () {
        $address = Address::factory()->create(['street' => 'Rua Original']);
        $order = Order::factory()->for($address->user)->create([
            'shipping_address' => $address->toSnapshot(),
        ]);

        $address->update(['street' => 'Rua Nova']);

        expect($order->fresh()->shipping_address['street'])->toBe('Rua Original');
    });

    it('refuses to hard-delete a product that was sold', function () {
        $item = OrderItem::factory()->create();

        $item->product->forceDelete();
    })->throws(QueryException::class);

    it('refuses to hard-delete a product with supplier purchases', function () {
        $purchase = SupplierPurchase::factory()->create();

        $purchase->product->forceDelete();
    })->throws(QueryException::class);
});

describe('reviews', function () {
    it('allows one review per user per product', function () {
        $review = Review::factory()->create();

        Review::factory()->for($review->user)->for($review->product)->create();
    })->throws(UniqueConstraintViolationException::class);
});
