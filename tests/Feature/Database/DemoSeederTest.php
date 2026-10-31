<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Queries\BestSellingProducts;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('creates the documented demo accounts', function () {
    $admin = User::firstWhere('email', 'admin@renice.test');
    $customer = User::firstWhere('email', 'cliente@renice.test');

    expect($admin->is_admin)->toBeTrue()
        ->and($customer->is_admin)->toBeFalse()
        ->and(Hash::check(DemoSeeder::PASSWORD, $admin->password))->toBeTrue()
        ->and(Hash::check(DemoSeeder::PASSWORD, $customer->password))->toBeTrue()
        ->and($customer->addresses()->exists())->toBeTrue();
});

it('seeds the original catalog with one product out of stock', function () {
    expect(Product::count())->toBe(5)
        ->and(Product::where('stock', 0)->pluck('name')->all())->toBe(['Copo Fresh 500ml gelo médio']);
});

it('keeps every product\'s stock equal to its ledger', function () {
    Product::all()->each(function (Product $product) {
        expect($product->stock)->toBe((int) $product->inventoryMovements()->sum('quantity'));
    });
});

it('creates order history, reviews and a best-sellers ranking', function () {
    expect(Order::count())->toBeGreaterThan(5)
        ->and(Review::count())->toBeGreaterThan(0)
        ->and(app(BestSellingProducts::class)->get())->not->toBeEmpty();
});
