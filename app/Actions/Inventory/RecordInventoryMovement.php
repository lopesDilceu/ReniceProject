<?php

namespace App\Actions\Inventory;

use App\Enums\InventoryMovementType;
use App\Exceptions\InsufficientStock;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * The only way stock changes. Locks the product row, writes the movement and
 * updates the cached balance in one transaction, so concurrent writers queue
 * up instead of overselling (replaces v1's atualizar_estoque_produto).
 */
class RecordInventoryMovement
{
    /**
     * @param  int  $quantity  Positive adds stock, negative removes it.
     *
     * @throws InsufficientStock when the movement would make stock negative.
     */
    public function handle(
        Product $product,
        InventoryMovementType $type,
        int $quantity,
        ?Model $source = null,
        ?User $actor = null,
        ?string $note = null,
    ): InventoryMovement {
        if ($quantity === 0) {
            throw new InvalidArgumentException('An inventory movement must change stock.');
        }

        return DB::transaction(function () use ($product, $type, $quantity, $source, $actor, $note) {
            $locked = Product::withTrashed()->lockForUpdate()->findOrFail($product->getKey());

            $stockAfter = $locked->stock + $quantity;

            if ($stockAfter < 0) {
                throw new InsufficientStock($locked, abs($quantity), $locked->stock);
            }

            $locked->forceFill(['stock' => $stockAfter])->save();
            $product->setRawAttributes($locked->getAttributes(), sync: true);

            $movement = new InventoryMovement;
            $movement->forceFill([
                'product_id' => $locked->getKey(),
                'type' => $type,
                'quantity' => $quantity,
                'stock_after' => $stockAfter,
                'user_id' => $actor?->getKey(),
                'note' => $note,
            ]);

            if ($source !== null) {
                $movement->source()->associate($source);
            }

            $movement->save();

            $this->alertOnLowStock($locked, stockBefore: $stockAfter - $quantity, stockAfter: $stockAfter);

            return $movement;
        });
    }

    /**
     * E-mails admins when stock crosses the threshold on the way down. Only
     * the crossing alerts: further sales below it do not, and a restock
     * above it arms the alert again.
     */
    private function alertOnLowStock(Product $product, int $stockBefore, int $stockAfter): void
    {
        $threshold = Config::integer('shop.low_stock_threshold');

        if ($stockBefore > $threshold && $stockAfter <= $threshold) {
            Notification::send(User::query()->where('is_admin', true)->get(), new LowStockAlert($product, $stockAfter));
        }
    }
}
