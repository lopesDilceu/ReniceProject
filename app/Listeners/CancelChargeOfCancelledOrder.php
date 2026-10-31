<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderStatusChanged;
use App\Jobs\CancelPixCharge;

class CancelChargeOfCancelledOrder
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->change->to_status !== OrderStatus::Cancelled) {
            return;
        }

        $payment = $event->order->payment()->first();

        if ($payment?->status === PaymentStatus::Pending) {
            CancelPixCharge::dispatch($payment);
        }
    }
}
