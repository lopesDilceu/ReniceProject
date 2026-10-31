<?php

namespace App\Jobs;

use App\Actions\Payments\ApplyPaymentStatus;
use App\Models\Payment;
use App\Models\WebhookCall;
use App\Payments\PaymentGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Handles a stored webhook notification: asks the provider for the
 * payment's current status (the notification body is not trusted) and
 * applies it. Retried with backoff when the provider is unreachable.
 */
class ProcessPaymentNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public readonly WebhookCall $call,
        public readonly string $providerPaymentId,
    ) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(PaymentGateway $gateway, ApplyPaymentStatus $apply): void
    {
        if ($this->call->processed_at !== null) {
            return;
        }

        $payment = Payment::query()
            ->where('provider', $gateway->name())
            ->where('provider_payment_id', $this->providerPaymentId)
            ->first();

        // Not ours (another integration on the same account) or not stored yet.
        if ($payment !== null) {
            $apply->handle($payment, $gateway->status($this->providerPaymentId));
        }

        $this->call->forceFill(['processed_at' => now()])->save();
    }
}
