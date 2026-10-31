<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Carbon\CarbonInterface;

/**
 * A Pix payment provider. Mercado Pago in production (ADR 0006); a local
 * fake for development and the demo, so the store runs without credentials.
 */
interface PaymentGateway
{
    /** Stored on payments.provider. */
    public function name(): string;

    /**
     * Creates a Pix charge. The same idempotency key must return the same
     * charge, so a retried request never charges the customer twice.
     *
     * @throws PaymentGatewayException
     */
    public function createPixCharge(Order $order, CarbonInterface $expiresAt, string $idempotencyKey): PixCharge;

    /**
     * The charge's current status, read from the provider (never from the
     * webhook body, which anyone can forge or replay).
     *
     * @throws PaymentGatewayException
     */
    public function status(string $providerPaymentId): PaymentStatus;

    /**
     * Cancels a pending charge, so it can no longer be paid.
     *
     * @throws PaymentGatewayException
     */
    public function cancel(string $providerPaymentId): void;
}
