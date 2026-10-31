<?php

use App\Actions\Inventory\RecordInventoryMovement;
use App\Enums\InventoryMovementType;
use App\Exceptions\InsufficientStock;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->record = app(RecordInventoryMovement::class);
});

it('adds stock and records the balance after the movement', function () {
    $product = Product::factory()->create();

    $movement = $this->record->handle($product, InventoryMovementType::Adjustment, 30);

    expect($product->stock)->toBe(30)
        ->and($product->fresh()->stock)->toBe(30)
        ->and($movement)
        ->type->toBe(InventoryMovementType::Adjustment)
        ->quantity->toBe(30)
        ->stock_after->toBe(30);
});

it('removes stock', function () {
    $product = Product::factory()->create();
    $this->record->handle($product, InventoryMovementType::Adjustment, 10);

    $this->record->handle($product, InventoryMovementType::Sale, -4);

    expect($product->fresh()->stock)->toBe(6);
});

it('refuses to make stock negative and changes nothing', function () {
    $product = Product::factory()->create();
    $this->record->handle($product, InventoryMovementType::Adjustment, 3);

    expect(fn () => $this->record->handle($product, InventoryMovementType::Sale, -4))
        ->toThrow(InsufficientStock::class);

    expect($product->fresh()->stock)->toBe(3)
        ->and($product->inventoryMovements()->count())->toBe(1);
});

it('keeps the cached stock equal to the sum of movements', function () {
    $product = Product::factory()->create();

    foreach ([20, -5, 8, -13, 2] as $quantity) {
        $this->record->handle($product, InventoryMovementType::Adjustment, $quantity);
    }

    expect($product->fresh()->stock)
        ->toBe((int) $product->inventoryMovements()->sum('quantity'))
        ->toBe(12)
        ->and($product->inventoryMovements()->latest('id')->value('stock_after'))->toBe(12);
});

it('records who made the change and why', function () {
    $product = Product::factory()->create();
    $admin = User::factory()->admin()->create();

    $movement = $this->record->handle(
        $product, InventoryMovementType::Adjustment, 5, actor: $admin, note: 'Contagem física'
    );

    expect($movement->user->is($admin))->toBeTrue()
        ->and($movement->note)->toBe('Contagem física');
});

it('rejects a movement that does not change stock', function () {
    $this->record->handle(Product::factory()->create(), InventoryMovementType::Adjustment, 0);
})->throws(InvalidArgumentException::class);

it('never updates or deletes a movement', function (Closure $change) {
    $movement = $this->record->handle(Product::factory()->create(), InventoryMovementType::Adjustment, 5);

    $change($movement);
})->with([
    'update' => fn (InventoryMovement $movement) => $movement->forceFill(['quantity' => 50])->save(),
    'delete' => fn (InventoryMovement $movement) => $movement->delete(),
])->throws(LogicException::class, 'append-only');
