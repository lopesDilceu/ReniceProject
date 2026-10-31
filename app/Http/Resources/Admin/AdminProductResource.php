<?php

namespace App\Http\Resources\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The admin sees what the storefront hides: exact stock and removal state.
 *
 * @mixin Product
 */
class AdminProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'stock' => $this->stock,
            'image_url' => $this->imageUrl(),
            'deleted' => $this->trashed(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
