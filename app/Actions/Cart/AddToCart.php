<?php

namespace App\Actions\Cart;

use App\Exceptions\InsufficientStock;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Adds a product to the customer's own cart, merging with the existing line
 * and never exceeding available stock. The cart always comes from the user,
 * never from request input (v1 took cart and item ids from the URL: SEC-03).
 */
class AddToCart
{
    /**
     * @throws InsufficientStock
     */
    public function handle(User $customer, Product $product, int $quantity): CartItem
    {
        return DB::transaction(function () use ($customer, $product, $quantity) {
            $cart = $customer->cart()->firstOrCreate();

            $item = $cart->items()->where('product_id', $product->getKey())->lockForUpdate()->first()
                ?? $cart->items()->make(['product_id' => $product->getKey(), 'quantity' => 0]);

            $wanted = $item->quantity + $quantity;

            if ($wanted > $product->stock) {
                throw new InsufficientStock($product, $wanted, $product->stock);
            }

            $item->quantity = $wanted;
            $item->save();

            return $item;
        });
    }
}
