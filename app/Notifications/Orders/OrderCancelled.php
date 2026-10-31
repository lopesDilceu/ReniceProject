<?php

namespace App\Notifications\Orders;

use App\Models\Order;

class OrderCancelled extends OrderNotification
{
    public function __construct(Order $order, public readonly ?string $reason)
    {
        parent::__construct($order);
    }

    public function subject(): string
    {
        return "Pedido #{$this->order->id} cancelado";
    }

    protected function intro(): array
    {
        $lines = ['Seu pedido foi cancelado e os itens voltaram ao estoque.'];

        if ($this->reason !== null) {
            $lines[] = "Motivo: {$this->reason}";
        }

        return $lines;
    }
}
