<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('never resets a store that is not the demo', function () {
    config(['shop.demo' => false]);
    Product::factory()->create(['name' => 'Produto real']);

    $this->artisan('demo:reset')->assertFailed();

    expect(Product::where('name', 'Produto real')->exists())->toBeTrue();
});

it('tells every page it is the demo', function (bool $demo) {
    config(['shop.demo' => $demo]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('demo', $demo));
})->with([true, false]);
