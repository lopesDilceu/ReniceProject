<?php

namespace App\Notifications\Orders;

class OrderPaid extends OrderNotification
{
    public function subject(): string
    {
        return "Pagamento confirmado: pedido #{$this->order->id}";
    }

    protected function intro(): array
    {
        return ['Seu pagamento foi confirmado. Agora é com a gente: avisaremos quando o pedido sair para entrega.'];
    }
}
