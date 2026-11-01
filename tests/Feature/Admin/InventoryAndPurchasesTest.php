<?php

use App\Actions\Inventory\RecordSupplierPurchase;
use App\Enums\InventoryMovementType;
use App\Models\Product;
use App\Models\SupplierPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Admin Renice']);
    $this->product = Product::factory()->create(['price_cents' => 1800]);
});

describe('inventory', function () {
    it('adjusts stock with a reason and records who did it', function () {
        $this->actingAs($this->admin)->post(route('admin.inventory.store', $this->product), [
            'quantity' => 12, 'note' => 'Contagem física',
        ])->assertSessionHasNoErrors();

        $movement = $this->product->inventoryMovements()->sole();

        expect($this->product->fresh()->stock)->toBe(12)
            ->and($movement->type)->toBe(InventoryMovementType::Adjustment)
            ->and($movement->user_id)->toBe($this->admin->id)
            ->and($movement->note)->toBe('Contagem física');
    });

    it('rejects adjustments without a reason, of zero, or below zero stock', function (array $data) {
        $this->actingAs($this->admin)->post(route('admin.inventory.store', $this->product), $data)
            ->assertSessionHasErrors();

        expect($this->product->fresh()->stock)->toBe(0);
    })->with([
        'no reason' => [['quantity' => 5]],
        'zero' => [['quantity' => 0, 'note' => 'nada']],
        'more than in stock' => [['quantity' => -1, 'note' => 'avaria']],
    ]);

    it('lists the movement history, newest first', function () {
        app(RecordSupplierPurchase::class)->handle($this->product, 'Gelo Norte', 10, 400);
        $this->actingAs($this->admin)->post(route('admin.inventory.store', $this->product), ['quantity' => -2, 'note' => 'Avaria']);

        $this->actingAs($this->admin)->get(route('admin.inventory.show', $this->product))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/inventory/show')
                ->where('product.stock', 8)
                ->has('movements.data', 2)
                ->where('movements.data.0.quantity', -2)
                ->where('movements.data.0.user', 'Admin Renice')
                ->where('movements.data.1.type', 'purchase')
                ->where('movements.data.1.source', fn ($source) => str_starts_with($source, 'Compra #')));
    });
});

describe('supplier purchases', function () {
    it('registers a purchase: stock goes up, the sale price does not change', function () {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), [
            'product_id' => $this->product->id,
            'supplier' => 'Gelo Norte',
            'quantity' => 40,
            'unit_cost' => '4,50',
            'purchased_at' => '2026-10-01',
        ])->assertSessionHasNoErrors();

        expect(SupplierPurchase::sole())
            ->unit_cost_cents->toBe(450)
            ->purchased_at->toDateString()->toBe('2026-10-01')
            ->and($this->product->fresh())
            ->stock->toBe(40)
            ->price_cents->toBe(1800);
    })->issue(14);

    it('validates the purchase', function (array $overrides, string $field) {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), [
            'product_id' => $this->product->id, 'supplier' => 'Gelo Norte', 'quantity' => 10, 'unit_cost' => '4,50',
            ...$overrides,
        ])->assertSessionHasErrors($field);
    })->with([
        'unknown product' => [['product_id' => 999], 'product_id'],
        'zero quantity' => [['quantity' => 0], 'quantity'],
        'invalid cost' => [['unit_cost' => 'barato'], 'unit_cost_cents'],
        'future date' => [['purchased_at' => now()->addDay()->toDateString()], 'purchased_at'],
    ]);
});
