<?php

namespace App\Events;

use App\Models\Order;
use App\Models\OrderStatusChange;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An order entered a new status (including being placed). Fired by
 * OrderStatusChange::record, the single place every transition goes through.
 */
class OrderStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly Order $order,
        public readonly OrderStatusChange $change,
    ) {}
}
