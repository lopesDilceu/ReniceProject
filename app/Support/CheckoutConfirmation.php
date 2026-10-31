<?php

namespace App\Support;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\URL;

/**
 * Checkouts prepared by an AI agent are placed only when the customer
 * confirms them in the store. The agent gets a signed link that pins the
 * customer, the address and the exact cart (products, quantities, prices):
 * if any of it changes, or the link expires, it no longer places an order.
 * Placing the order empties the cart, so a link works once.
 */
final class CheckoutConfirmation
{
    public const LIFETIME_MINUTES = 15;

    public static function url(User $customer, Address $address): string
    {
        return URL::temporarySignedRoute('checkout.confirm', self::expiresAt(), [
            'address' => $address->getKey(),
            'customer' => $customer->getKey(),
            'cart' => self::fingerprint($customer, $address),
        ]);
    }

    public static function expiresAt(): CarbonInterface
    {
        return now()->addMinutes(self::LIFETIME_MINUTES);
    }

    /**
     * Changes whenever the cart or its prices change.
     */
    public static function fingerprint(User $customer, Address $address): string
    {
        $items = $customer->cart?->items()->with('product')->orderBy('product_id')->get() ?? collect();

        $lines = $items
            ->map(fn (CartItem $item) => "{$item->product_id}:{$item->quantity}:{$item->product->price_cents}")
            ->implode(',');

        return hash_hmac('sha256', "{$customer->getKey()}|{$address->getKey()}|{$lines}", (string) config('app.key'));
    }
}
