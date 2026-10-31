<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Exact stock is business data and is not exposed: the storefront only
 * needs to know whether a product can be bought and whether it is running
 * out.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    public const LOW_STOCK = 5;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rating = $this->reviews_avg_rating;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'excerpt' => Str::limit((string) $this->description, 110),
            'description' => $this->when($request->routeIs('products.show'), $this->description),
            'price_cents' => $this->price_cents,
            'image_url' => $this->imageUrl(),
            'available' => $this->isAvailable(),
            'low_stock' => $this->isAvailable() && $this->stock <= self::LOW_STOCK,
            'rating' => $rating === null ? null : round((float) $rating, 1),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
        ];
    }
}
