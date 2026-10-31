<?php

namespace App\Actions\Orders;

use App\Actions\Inventory\RecordInventoryMovement;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Moves an order to its next status. Cancelling or refunding puts the items
 * back in stock through the ledger, with the order as source.
 */
class ChangeOrderStatus
{
    public function __construct(
        private readonly RecordInventoryMovement $recordMovement,
    ) {}

    public function handle(Order $order, OrderStatus $next, User $actor): Order
    {
        return DB::transaction(function () use ($order, $next, $actor) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if (! $order->status->canTransitionTo($next)) {
                throw new InvalidArgumentException(
                    "Um pedido \"{$order->status->label()}\" não pode passar para \"{$next->label()}\"."
                );
            }

            $order->status = $next;
            $order->save();

            if ($next->returnsStock()) {
                foreach ($order->items()->with('product')->get() as $item) {
                    $this->recordMovement->handle(
                        $item->product,
                        InventoryMovementType::Adjustment,
                        $item->quantity,
                        source: $order,
                        actor: $actor,
                        note: "Pedido #{$order->id} {$next->label()}",
                    );
                }
            }

            return $order;
        });
    }
}
