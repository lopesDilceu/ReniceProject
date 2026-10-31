<?php

namespace App\Notifications\Orders;

use App\Http\Resources\AddressResource;

class OrderShipped extends OrderNotification
{
    public function subject(): string
    {
        return "Seu pedido #{$this->order->id} saiu para entrega";
    }

    protected function intro(): array
    {
        $address = AddressResource::format($this->order->shipping_address);

        return [
            'Seu pedido está a caminho.',
            "Entrega em: {$address['street']}, {$address['number']}, {$address['neighborhood']}, {$address['city']}/{$address['state']}.",
        ];
    }
}
