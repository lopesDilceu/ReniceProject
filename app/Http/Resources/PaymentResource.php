<?php

namespace App\Http\Resources;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payable = $this->status === PaymentStatus::Pending;

        return [
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'provider' => $this->provider,
            'provider_payment_id' => $this->provider_payment_id,
            'amount_cents' => $this->amount_cents,
            // The QR code only while it can still be paid.
            'pix_code' => $payable ? $this->pix_code : null,
            'pix_qr_image' => $payable ? $this->pix_qr_image : null,
            'expires_at' => $this->expires_at->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
        ];
    }
}
