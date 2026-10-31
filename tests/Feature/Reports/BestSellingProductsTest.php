<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Queries\BestSeller;
use App\Queries\BestSellingProducts;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function sell(Product $product, int $quantity, OrderStatus $status = OrderStatus::Paid, ?string $at = null): void
{
    $order = Order::factory()->status($status)->create($at ? ['created_at' => $at] : []);
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => $quantity]);
}

it('ranks by units sold, not by number of order lines', function () {
    $bulk = Product::factory()->create(['name' => 'Vendido em lote']);
    $frequent = Product::factory()->create(['name' => 'Vendido aos poucos']);
    sell($bulk, 10);
    sell($frequent, 1);
    sell($frequent, 1);
    sell($frequent, 1);

    $ranking = app(BestSellingProducts::class)->get();

    expect($ranking->map(fn (BestSeller $row) => [$row->product->name, $row->unitsSold])->all())
        ->toBe([['Vendido em lote', 10], ['Vendido aos poucos', 3]]);
})->issue(15);

it('reports revenue at the price actually charged', function () {
    $product = Product::factory()->create(['price_cents' => 1000]);
    sell($product, 2);
    $product->update(['price_cents' => 5000]);
    sell($product, 1);

    expect(app(BestSellingProducts::class)->get()->sole()->revenueCents)->toBe(2 * 1000 + 5000);
});

it('counts only paid, shipped and delivered orders', function () {
    $product = Product::factory()->create();
    sell($product, 1, OrderStatus::Paid);
    sell($product, 2, OrderStatus::Shipped);
    sell($product, 4, OrderStatus::Delivered);
    sell($product, 100, OrderStatus::PendingPayment);
    sell($product, 100, OrderStatus::Cancelled);
    sell($product, 100, OrderStatus::Refunded);

    expect(app(BestSellingProducts::class)->get()->sole()->unitsSold)->toBe(7);
});

it('limits the period', function () {
    $product = Product::factory()->create();
    sell($product, 5, at: '2026-01-10 10:00:00');
    sell($product, 2, at: '2026-06-10 10:00:00');

    $ranking = app(BestSellingProducts::class)->get(since: now()->setDate(2026, 6, 1));

    expect($ranking->sole()->unitsSold)->toBe(2);
});

it('keeps products removed from the catalog in the history', function () {
    $product = Product::factory()->create();
    sell($product, 3);
    $product->delete();

    expect(app(BestSellingProducts::class)->get()->sole()->product->is($product))->toBeTrue();
});
