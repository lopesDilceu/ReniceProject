<?php

namespace App\Http\Middleware;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * Only what every page needs, and no personal data: CPF, birth date and
     * addresses are sent by the pages that need them, never globally.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'demo' => (bool) config('shop.demo'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'avatar_url' => $user->avatarUrl(),
                ] : null,
            ],
            // Units in the cart, for the header badge. One SUM query.
            'cartCount' => fn () => $user
                ? (int) CartItem::query()->whereHas('cart', fn ($cart) => $cart->whereBelongsTo($user))->sum('quantity')
                : 0,
        ];
    }
}
