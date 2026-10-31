<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('admin pages', [
    'dashboard' => fn () => route('admin.home'),
    'orders' => fn () => route('admin.orders.index'),
    'order' => fn () => route('admin.orders.show', Order::factory()->create()),
    'customers' => fn () => route('admin.customers.index'),
    'reviews' => fn () => route('admin.reviews.index'),
    'products' => fn () => route('admin.products.index'),
    'new product' => fn () => route('admin.products.create'),
    'edit product' => fn () => route('admin.products.edit', Product::factory()->create()),
    'inventory' => fn () => route('admin.inventory.show', Product::factory()->create()),
    'purchases' => fn () => route('admin.purchases.index'),
]);

it('sends guests to the login page', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with('admin pages');

it('forbids customers', function (string $url) {
    $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
})->with('admin pages');

it('lets admins in', function (string $url) {
    $this->actingAs(User::factory()->admin()->create())->get($url)->assertOk();
})->with('admin pages');

it('forbids customers from changing anything', function () {
    $product = Product::factory()->create(['price_cents' => 1000]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.products.update', $product), ['name' => 'Hack', 'price' => '0,01'])
        ->assertForbidden();

    expect($product->fresh()->price_cents)->toBe(1000);
});
