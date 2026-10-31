<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransition;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

/**
 * Cancels orders whose payment window is over, releasing their reserved
 * stock. Runs every minute (orders:expire-unpaid); safe to run concurrently
 * with payments because ChangeOrderStatus re-checks the status under lock.
 */
class ExpireUnpaidOrders
{
    public const REASON = 'Pagamento não confirmado dentro do prazo.';

    public function __construct(
        private readonly ChangeOrderStatus $changeStatus,
    ) {}

    /**
     * @return int how many orders expired.
     */
    public function handle(): int
    {
        $expired = 0;

        Order::query()
            ->where('status', OrderStatus::PendingPayment)
            ->where('payment_due_at', '<=', now())
            ->chunkById(100, function (Collection $orders) use (&$expired) {
                foreach ($orders as $order) {
                    try {
                        $this->changeStatus->handle($order, OrderStatus::Cancelled, actor: null, reason: self::REASON);
                        $expired++;
                    } catch (InvalidOrderTransition) {
                        // Paid or cancelled after it was selected: nothing to release.
                    }
                }
            });

        return $expired;
    }
}
