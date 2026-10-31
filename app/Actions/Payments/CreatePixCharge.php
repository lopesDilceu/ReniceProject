<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use App\Payments\PaymentGatewayException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Creates the order's Pix charge, once. Safe to call again (a retry after a
 * provider error, a double click): an existing charge is returned, and the
 * provider receives the same idempotency key, so it never creates a second
 * charge either.
 */
class CreatePixCharge
{
    /**
     * Mercado Pago refuses Pix expirations under 30 minutes from creation.
     */
    private const MINIMUM_LIFETIME_MINUTES = 31;

    public function __construct(
        private readonly PaymentGateway $gateway,
    ) {}

    /**
     * @throws PaymentGatewayException
     */
    public function handle(Order $order): Payment
    {
        if ($existing = $order->payment()->first()) {
            return $existing;
        }

        if ($order->status !== OrderStatus::PendingPayment) {
            throw new LogicException("Order #{$order->id} is not waiting for payment.");
        }

        $expiresAt = $order->payment_due_at ?? now()->addMinutes(self::MINIMUM_LIFETIME_MINUTES);
        $expiresAt = $expiresAt->max(now()->addMinutes(self::MINIMUM_LIFETIME_MINUTES));

        // Outside any transaction: no database lock is held during the HTTP call.
        $charge = $this->gateway->createPixCharge($order, $expiresAt, idempotencyKey: "renice-order-{$order->id}");

        $payment = new Payment;
        $payment->forceFill([
            'order_id' => $order->getKey(),
            'provider' => $this->gateway->name(),
            'provider_payment_id' => $charge->providerPaymentId,
            'status' => $charge->status,
            'amount_cents' => $order->total_cents,
            'pix_code' => $charge->code,
            'pix_qr_image' => $charge->qrImage,
            'expires_at' => $expiresAt,
        ]);

        try {
            // A savepoint when called inside a transaction: on PostgreSQL a
            // failed INSERT would otherwise abort the whole transaction.
            DB::transaction(fn () => $payment->save());
        } catch (UniqueConstraintViolationException) {
            // A concurrent request stored the same charge first.
            return $order->payment()->firstOrFail();
        }

        return $payment;
    }
}
