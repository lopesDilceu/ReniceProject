<?php

namespace App\Actions\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\Product;
use App\Models\SupplierPurchase;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Registers stock bought from a supplier. The unit cost is recorded on the
 * purchase and never copied into the product's sale price: v1's
 * after_compra_insert trigger did that and sold every restock at cost (#14).
 */
class RecordSupplierPurchase
{
    public function __construct(
        private readonly RecordInventoryMovement $recordMovement,
    ) {}

    public function handle(
        Product $product,
        string $supplier,
        int $quantity,
        int $unitCostCents,
        ?CarbonInterface $purchasedAt = null,
        ?User $actor = null,
    ): SupplierPurchase {
        return DB::transaction(function () use ($product, $supplier, $quantity, $unitCostCents, $purchasedAt, $actor) {
            $purchase = SupplierPurchase::create([
                'product_id' => $product->getKey(),
                'supplier' => $supplier,
                'quantity' => $quantity,
                'unit_cost_cents' => $unitCostCents,
                'purchased_at' => $purchasedAt ?? now(),
            ]);

            $this->recordMovement->handle(
                $product,
                InventoryMovementType::Purchase,
                $quantity,
                source: $purchase,
                actor: $actor,
            );

            return $purchase;
        });
    }
}
