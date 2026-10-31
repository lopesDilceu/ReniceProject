<?php

namespace App\Enums;

/**
 * Status of a Pix charge, as the payment provider reports it.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Aguardando Pix',
            self::Approved => 'Pago',
            self::Rejected => 'Recusado',
            self::Cancelled => 'Cancelado',
            self::Refunded => 'Estornado',
        };
    }
}
