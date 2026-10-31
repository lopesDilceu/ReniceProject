<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Cancels the Pix charge of a cancelled order at the provider, so the
 * customer can no longer pay for it.
 */
class CancelPixCharge implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly Payment $payment)
    {
        $this->afterCommit();
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(PaymentGateway $gateway): void
    {
        if ($this->payment->status !== PaymentStatus::Pending) {
            return;
        }

        $gateway->cancel($this->payment->provider_payment_id);

        $this->payment->forceFill(['status' => PaymentStatus::Cancelled])->save();
    }
}
