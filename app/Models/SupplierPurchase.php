<?php

namespace App\Models;

use Database\Factories\SupplierPurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Cast attribute types, which Larastan otherwise reads from the migration:
 *
 * @property Carbon $purchased_at
 * @property int $quantity
 * @property int $unit_cost_cents
 */
class SupplierPurchase extends Model
{
    /** @use HasFactory<SupplierPurchaseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'supplier',
        'quantity',
        'unit_cost_cents',
        'purchased_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost_cents' => 'integer',
            'purchased_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
