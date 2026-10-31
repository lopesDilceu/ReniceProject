<?php

namespace App\Listeners;

use App\Events\Broadcast\OrderUpdated;
use App\Events\OrderStatusChanged;

/**
 * Every timeline entry becomes a live update for the dashboard and the
 * order page, including the first one (a new order).
 */
class BroadcastOrderUpdate
{
    public function handle(OrderStatusChanged $event): void
    {
        OrderUpdated::dispatch($event->order, $event->change->from_status === null);
    }
}
