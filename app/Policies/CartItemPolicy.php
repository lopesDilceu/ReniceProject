<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

/**
 * A cart item belongs to whoever owns its cart. v1 let any visitor change
 * or delete any item by id (SEC-03).
 */
class CartItemPolicy
{
    public function update(User $user, CartItem $item): bool
    {
        return $item->cart()->where('user_id', $user->getKey())->exists();
    }

    public function delete(User $user, CartItem $item): bool
    {
        return $this->update($user, $item);
    }
}
