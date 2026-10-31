<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property OrderStatus|null $from_status
 * @property OrderStatus $to_status
 *
 * One step of an order's timeline: who moved it, when and why. Rows are
 * written only through record(), which also fires OrderStatusChanged, and
 * are never updated or deleted.
 */
class OrderStatusChange extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $guarded = ['*'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Order status changes are append-only.'));
        static::deleting(fn () => throw new LogicException('Order status changes are append-only.'));
    }

    /**
     * Records that the order is now in its current status.
     */
    public static function record(Order $order, ?OrderStatus $from, ?User $actor, ?string $reason = null): self
    {
        $change = new self;
        $change->forceFill([
            'order_id' => $order->getKey(),
            'from_status' => $from,
            'to_status' => $order->status,
            'user_id' => $actor?->getKey(),
            'reason' => $reason,
        ])->save();

        OrderStatusChanged::dispatch($order, $change);

        return $change;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => OrderStatus::class,
            'to_status' => OrderStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Who made the change; null when it was the system.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
