<?php

namespace App\Actions\Payments;

use App\Actions\Orders\ChangeOrderStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidOrderTransition;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Records what the provider says about a charge and moves the order along.
 * Idempotent: applying the same status twice changes nothing, so replayed or
 * out-of-order notifications are harmless.
 *
 * - approved: the order becomes paid (by the system).
 * - refunded: a paid order becomes refunded and its stock returns.
 * - rejected / cancelled: recorded; the order expires on its own schedule.
 */
class ApplyPaymentStatus
{
    public function __construct(
        private readonly ChangeOrderStatus $changeStatus,
    ) {}

    public function handle(Payment $payment, PaymentStatus $status): Payment
    {
        return DB::transaction(function () use ($payment, $status) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());

            if ($payment->status === $status) {
                return $payment;
            }

            $payment->forceFill([
                'status' => $status,
                'paid_at' => $status === PaymentStatus::Approved ? now() : $payment->paid_at,
            ])->save();

            match ($status) {
                PaymentStatus::Approved => $this->moveOrder($payment, OrderStatus::Paid, 'Pix confirmado.'),
                PaymentStatus::Refunded => $this->moveOrder($payment, OrderStatus::Refunded, 'Pix estornado pelo provedor de pagamento.'),
                default => null,
            };

            return $payment;
        });
    }

    private function moveOrder(Payment $payment, OrderStatus $next, string $reason): void
    {
        try {
            $this->changeStatus->handle($payment->order()->firstOrFail(), $next, actor: null, reason: $reason);
        } catch (InvalidOrderTransition) {
            if ($next === OrderStatus::Paid) {
                // Paid after the order expired or was cancelled: the money must
                // go back. Refunds are issued from the provider's panel for now.
                Log::warning('Pix approved for an order that is no longer waiting for payment; refund it.', [
                    'order_id' => $payment->order_id,
                    'payment_id' => $payment->id,
                    'provider_payment_id' => $payment->provider_payment_id,
                ]);
            }
        }
    }
}
