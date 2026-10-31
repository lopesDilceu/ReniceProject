<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The signed-in customer's cart. A customer without a cart gets an empty one.
 *
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Cart|null $cart */
        $cart = $this->resource;
        $items = $cart->items ?? collect();

        return [
            'items' => $items->map(fn (CartItem $item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_slug' => $item->product->slug,
                'product_name' => $item->product->name,
                'unit_price_cents' => $item->product->price_cents,
                'quantity' => $item->quantity,
                'subtotal_cents' => $item->quantity * $item->product->price_cents,
                'available' => $item->product->isAvailable(),
            ])->values(),
            'total_cents' => $cart?->totalCents() ?? 0,
            'currency' => 'BRL',
        ];
    }
}
