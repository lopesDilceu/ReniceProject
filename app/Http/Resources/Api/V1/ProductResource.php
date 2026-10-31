<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\ProductResource as WebProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A product in the API contract. Separate from the Inertia resources, so the
 * web pages can change without breaking API clients. Exact stock is never
 * exposed, only availability.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rating = $this->reviews_avg_rating;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => (string) $this->description,
            'price_cents' => $this->price_cents,
            'currency' => 'BRL',
            'image_url' => $this->imageUrl(),
            'available' => $this->isAvailable(),
            'low_stock' => $this->isAvailable() && $this->stock <= WebProductResource::LOW_STOCK,
            /** Average rating from 1 to 5, or null without reviews. */
            'rating' => $rating === null ? null : round((float) $rating, 1),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'url' => route('products.show', $this->resource),
        ];
    }
}
