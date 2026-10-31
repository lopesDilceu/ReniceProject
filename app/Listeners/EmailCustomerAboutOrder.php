<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Notifications\Orders\OrderCancelled;
use App\Notifications\Orders\OrderPaid;
use App\Notifications\Orders\OrderPlaced;
use App\Notifications\Orders\OrderShipped;

/**
 * Picks the e-mail for each status the customer should hear about. The
 * notifications are queued after commit, so this runs inside the
 * transaction without sending anything if it rolls back.
 */
class EmailCustomerAboutOrder
{
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $customer = $order->loadMissing('user')->user;

        if ($customer->trashed()) {
            return;
        }

        $notification = match ($event->change->to_status) {
            OrderStatus::PendingPayment => new OrderPlaced($order),
            OrderStatus::Paid => new OrderPaid($order),
            OrderStatus::Shipped => new OrderShipped($order),
            OrderStatus::Cancelled => new OrderCancelled($order, $event->change->reason),
            OrderStatus::Delivered, OrderStatus::Refunded => null,
        };

        if ($notification !== null) {
            $customer->notify($notification);
        }
    }
}
