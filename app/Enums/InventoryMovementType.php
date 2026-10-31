<?php

namespace App\Enums;

/**
 * Why stock changed. Checkout reserves the units while the order waits for
 * payment; cancelling the order, or letting the payment expire, releases
 * them. A paid order keeps its reservation: the units are sold.
 */
enum InventoryMovementType: string
{
    case Purchase = 'purchase';
    case Reservation = 'reservation';
    case Release = 'release';
    case Return = 'return';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Compra de fornecedor',
            self::Reservation => 'Reserva de pedido',
            self::Release => 'Reserva liberada',
            self::Return => 'Devolução',
            self::Adjustment => 'Ajuste manual',
        };
    }
}
