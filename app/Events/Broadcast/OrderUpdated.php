<?php

namespace App\Events\Broadcast;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An order entered a new status, pushed to the admin dashboard and to the
 * customer's order page. Queued (a WebSocket hiccup never breaks checkout)
 * and sent only after the transaction commits.
 */
class OrderUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly Order $order,
        public readonly bool $placed,
    ) {}

    /**
     * @return list<PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin'), new PrivateChannel("orders.{$this->order->id}")];
    }

    public function broadcastAs(): string
    {
        return 'order.updated';
    }

    /**
     * Just enough to update the screen; listeners reload what they show.
     *
     * @return array{id: int, status: string, status_label: string, total_cents: int, placed: bool}
     */
    public function broadcastWith(): array
    {
        /** @var OrderStatus $status */
        $status = $this->order->status;

        return [
            'id' => $this->order->id,
            'status' => $status->value,
            'status_label' => $status->label(),
            'total_cents' => $this->order->total_cents,
            'placed' => $this->placed,
        ];
    }
}
