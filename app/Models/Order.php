<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Cast attribute types, which Larastan otherwise reads from the migration:
 *
 * @property OrderStatus $status
 * @property int $total_cents
 * @property array<string, string|null> $shipping_address
 * @property Carbon|null $payment_due_at
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Orders are created by the checkout action, never from request input,
     * so nothing is mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['*'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total_cents' => 'integer',
            'shipping_address' => 'array',
            'payment_due_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * The timeline, oldest first.
     *
     * @return HasMany<OrderStatusChange, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(OrderStatusChange::class)->orderBy('id');
    }
}
