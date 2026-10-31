<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Aggregates loaded on demand with withAvg('reviews', 'rating') and
 * withCount('reviews'):
 *
 * @property-read float|string|null $reviews_avg_rating
 * @property-read int|null $reviews_count
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * `stock` is absent: it only changes through inventory movements (#12).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_cents',
        'image_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'stock' => 'integer',
        ];
    }

    /**
     * A URL slug not used by any product, removed ones included, so old
     * links never point at a different product.
     */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;

        for ($suffix = 2; static::withTrashed()->where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    /**
     * @return HasMany<InventoryMovement, $this>
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * @return HasMany<SupplierPurchase, $this>
     */
    public function supplierPurchases(): HasMany
    {
        return $this->hasMany(SupplierPurchase::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
