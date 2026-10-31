<?php

namespace App\Enums;

/**
 * Why stock changed. Reservations for pending payments arrive with #34.
 */
enum InventoryMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Compra de fornecedor',
            self::Sale => 'Venda',
            self::Adjustment => 'Ajuste manual',
        };
    }
}
