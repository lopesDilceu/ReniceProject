<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Customers may review a product once, after receiving it.
     */
    public function review(User $user, Product $product): bool
    {
        $received = $user->orders()
            ->where('status', OrderStatus::Delivered)
            ->whereHas('items', fn ($items) => $items->whereBelongsTo($product))
            ->exists();

        return $received && ! $user->reviews()->whereBelongsTo($product)->exists();
    }
}
