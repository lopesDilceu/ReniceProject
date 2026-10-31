<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_cents' => $this->total_cents,
            'shipping_address' => AddressResource::format($this->shipping_address),
            'created_at' => $this->created_at?->toIso8601String(),
            'items_count' => $this->whenCounted('items'),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn (OrderItem $item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_slug' => $item->product?->slug,
                'product_name' => $item->product_name,
                'unit_price_cents' => $item->unit_price_cents,
                'quantity' => $item->quantity,
                'subtotal_cents' => $item->subtotalCents(),
            ])),
        ];
    }
}
