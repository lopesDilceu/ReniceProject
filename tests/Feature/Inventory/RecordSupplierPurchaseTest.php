<?php

use App\Actions\Inventory\RecordSupplierPurchase;
use App\Enums\InventoryMovementType;
use App\Models\Product;
use App\Models\SupplierPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds the purchased quantity to stock through the ledger', function () {
    $product = Product::factory()->create();

    $purchase = app(RecordSupplierPurchase::class)->handle($product, 'Gelo Norte Ltda', 40, unitCostCents: 350);

    $movement = $product->inventoryMovements()->sole();

    expect($product->fresh()->stock)->toBe(40)
        ->and($movement->type)->toBe(InventoryMovementType::Purchase)
        ->and($movement->quantity)->toBe(40)
        ->and($movement->source->is($purchase))->toBeTrue();
});

it('records the cost without touching the sale price', function () {
    $product = Product::factory()->create(['price_cents' => 1125]);

    app(RecordSupplierPurchase::class)->handle($product, 'Gelo Norte Ltda', 10, unitCostCents: 400);

    expect($product->fresh()->price_cents)->toBe(1125)
        ->and(SupplierPurchase::sole()->unit_cost_cents)->toBe(400);
})->issue(14);
