<?php

namespace App\Actions\Orders;

use App\Actions\Inventory\RecordInventoryMovement;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransition;
use App\Models\Order;
use App\Models\OrderStatusChange;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The only way an order changes status. Checks the transition against
 * OrderStatus, records it in the order's timeline and, when cancelling or
 * refunding, puts the items back in stock through the ledger.
 */
class ChangeOrderStatus
{
    public function __construct(
        private readonly RecordInventoryMovement $recordMovement,
    ) {}

    /**
     * @param  User|null  $actor  null when the system makes the change (payment webhook, expiry).
     *
     * @throws InvalidOrderTransition
     */
    public function handle(Order $order, OrderStatus $next, ?User $actor, ?string $reason = null): Order
    {
        $reason = trim((string) $reason) ?: null;

        if ($next->requiresReason() && $reason === null) {
            throw InvalidOrderTransition::reasonRequired($next);
        }

        return DB::transaction(function () use ($order, $next, $actor, $reason) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            $previous = $order->status;

            if (! $previous->canTransitionTo($next)) {
                throw InvalidOrderTransition::notAllowed($previous, $next);
            }

            $order->status = $next;
            $order->save();

            OrderStatusChange::record($order, $previous, $actor, $reason);

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
