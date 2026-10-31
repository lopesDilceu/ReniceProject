<?php

namespace Database\Factories;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Builds rows directly, without updating products.stock. For realistic stock
 * use RecordInventoryMovement or RecordSupplierPurchase instead.
 *
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 50);

        return [
            'product_id' => Product::factory(),
            'type' => InventoryMovementType::Adjustment,
            'quantity' => $quantity,
            'stock_after' => $quantity,
        ];
    }
}
