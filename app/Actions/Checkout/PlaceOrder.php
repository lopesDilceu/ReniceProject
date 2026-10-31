<?php

namespace App\Actions\Checkout;

use App\Actions\Inventory\RecordInventoryMovement;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Exceptions\EmptyCart;
use App\Exceptions\InsufficientStock;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusChange;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Turns the customer's cart into an order. Replaces v1's finalizar_venda
 * procedure, which had no transaction, accepted empty carts and only found
 * out about missing stock halfway through (#13).
 *
 * Everything is checked before anything is written; the whole operation is
 * one transaction, so a failure leaves cart, stock and orders untouched.
 */
class PlaceOrder
{
    public function __construct(
        private readonly RecordInventoryMovement $recordMovement,
    ) {}

    /**
     * @throws EmptyCart
     * @throws InsufficientStock
     * @throws AuthorizationException when the address belongs to someone else.
     */
    public function handle(User $customer, Address $shippingAddress): Order
    {
        if ($shippingAddress->user_id !== $customer->getKey()) {
            throw new AuthorizationException('O endereço de entrega não pertence ao cliente.');
        }

        return DB::transaction(function () use ($customer, $shippingAddress) {
            // Locking the cart serializes concurrent submits of the same cart:
            // the second one waits, then finds it empty.
            $cart = Cart::query()->whereBelongsTo($customer)->lockForUpdate()->first();
            $items = $cart?->items()->get() ?? new Collection;

            if ($items->isEmpty()) {
                throw new EmptyCart;
            }

            $products = $this->lockProducts($items);

            foreach ($items as $item) {
                $product = $products[$item->product_id];

                if ($product->trashed() || $product->stock < $item->quantity) {
                    throw new InsufficientStock($product, $item->quantity, $product->trashed() ? 0 : $product->stock);
                }
            }

            $order = new Order;
            $order->forceFill([
                'user_id' => $customer->getKey(),
                'status' => OrderStatus::PendingPayment,
                'total_cents' => $items->sum(fn (CartItem $item) => $products[$item->product_id]->price_cents * $item->quantity),
                'shipping_address' => $shippingAddress->toSnapshot(),
                'payment_due_at' => now()->addMinutes(Config::integer('shop.payment_window_minutes')),
            ])->save();

            OrderStatusChange::record($order, from: null, actor: $customer);

            foreach ($items as $item) {
                $product = $products[$item->product_id];

                (new OrderItem)->forceFill([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'product_name' => $product->name,
                    'unit_price_cents' => $product->price_cents,
                    'quantity' => $item->quantity,
                ])->save();

                $this->recordMovement->handle(
                    $product,
                    InventoryMovementType::Reservation,
                    -$item->quantity,
                    source: $order,
                    actor: $customer,
                );
            }

            $cart?->items()->delete();

            return $order->load('items');
        });
    }

    /**
     * Locks every product in the cart, in id order so two checkouts sharing
     * products cannot deadlock.
     *
     * @param  Collection<int, CartItem>  $items
     * @return Collection<int, Product>
     */
    private function lockProducts(Collection $items): Collection
    {
        return Product::withTrashed()
            ->whereKey($items->pluck('product_id')->unique()->all())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }
}
