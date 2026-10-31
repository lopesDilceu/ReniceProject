<?php

namespace App\Notifications\Orders;

class OrderPlaced extends OrderNotification
{
    public function subject(): string
    {
        return "Recebemos seu pedido #{$this->order->id}";
    }

    protected function intro(): array
    {
        $lines = ['Recebemos seu pedido e os itens já estão separados para você.'];

        if ($this->order->payment_due_at !== null) {
            $lines[] = "Eles ficam reservados até {$this->localTime($this->order->payment_due_at)}. Sem pagamento até lá, o pedido é cancelado.";
        }

        return $lines;
    }
}
