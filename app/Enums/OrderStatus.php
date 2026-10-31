<?php

namespace App\Enums;

/**
 * Order lifecycle. Phase 4 (#32) adds the transition history and payment
 * webhooks on top of these rules.
 */
enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Aguardando pagamento',
            self::Paid => 'Pago',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregue',
            self::Cancelled => 'Cancelado',
            self::Refunded => 'Reembolsado',
        };
    }

    /**
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::PendingPayment => [self::Paid, self::Cancelled],
            self::Paid => [self::Shipped, self::Refunded],
            self::Shipped => [self::Delivered, self::Refunded],
            self::Delivered => [self::Refunded],
            self::Cancelled, self::Refunded => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->nextStatuses(), true);
    }

    /**
     * Statuses that undo the sale: the items go back to stock.
     */
    public function returnsStock(): bool
    {
        return $this === self::Cancelled || $this === self::Refunded;
    }
}
