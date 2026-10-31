<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusChange;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An order in the API contract: items, timeline and Pix payment come with
 * the single order; the list carries the summary.
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payment = $this->relationLoaded('payment') ? $this->payment : null;
        $payable = $payment?->status === PaymentStatus::Pending;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'total_cents' => $this->total_cents,
            'currency' => 'BRL',
            'created_at' => $this->created_at?->toIso8601String(),
            /** Until when the stock is reserved; only while waiting for payment. */
            'payment_due_at' => $this->status === OrderStatus::PendingPayment ? $this->payment_due_at?->toIso8601String() : null,
            'items_count' => $this->whenCounted('items'),
            'shipping_address' => $this->whenLoaded('items', fn () => AddressResource::fields($this->shipping_address)),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn (OrderItem $item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'unit_price_cents' => $item->unit_price_cents,
                'quantity' => $item->quantity,
                'subtotal_cents' => $item->subtotalCents(),
            ])),
            'timeline' => $this->whenLoaded('statusChanges', fn () => $this->statusChanges->map(fn (OrderStatusChange $change) => [
                'status' => $change->to_status,
                'reason' => $change->reason,
                'created_at' => $change->created_at->toIso8601String(),
            ])),
            'payment' => $this->whenLoaded('payment', fn () => $payment === null ? null : [
                'method' => 'pix',
                'status' => $payment->status,
                /** Pix "copia e cola", only while the charge can be paid. */
                'pix_code' => $payable ? $payment->pix_code : null,
                /** QR code image as a data URI, only while the charge can be paid. */
                'pix_qr_image' => $payable ? $payment->pix_qr_image : null,
                'expires_at' => $payment->expires_at->toIso8601String(),
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ]),
        ];
    }
}
