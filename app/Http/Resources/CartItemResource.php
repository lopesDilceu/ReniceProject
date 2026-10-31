<?php

namespace App\Http\Resources;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'subtotal_cents' => $this->quantity * $product->price_cents,
            // The cap the quantity input may offer; exact stock stays private
            // beyond what the customer could order anyway.
            'max_quantity' => min($product->stock, 99),
            'product' => [
                'name' => $product->name,
                'slug' => $product->slug,
                'price_cents' => $product->price_cents,
                'image_url' => $product->image_path ? asset($product->image_path) : null,
                'available' => $product->isAvailable(),
            ],
        ];
    }
}
