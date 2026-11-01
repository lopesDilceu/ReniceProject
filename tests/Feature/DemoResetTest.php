<?php

/*
| demo:reset runs migrate:fresh, which cannot run inside the transaction
| RefreshDatabase wraps each test in: this file migrates for real instead.
*/

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;

uses(DatabaseMigrations::class);

it('rebuilds the demo: fresh data, demo seed, uploads removed', function () {
    config(['shop.demo' => true]);
    Storage::fake('public');
    Storage::disk('public')->put('avatars/visitor.jpg', 'x');
    Storage::disk('public')->put('products/visitor.jpg', 'x');
    User::factory()->create(['email' => 'visitante@example.com']);

    $this->artisan('demo:reset')->assertSuccessful();

    expect(User::where('email', 'visitante@example.com')->exists())->toBeFalse()
        ->and(User::where('email', 'cliente@renice.test')->exists())->toBeTrue()
        ->and(Product::count())->toBe(5);
    Storage::disk('public')->assertMissing(['avatars/visitor.jpg', 'products/visitor.jpg']);

    // The demo photos are uploads too, and survive the clean-up.
    Product::all()->each(fn (Product $product) => Storage::disk('public')->assertExists($product->image_path));
});
