<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Cart\AddToCart;
use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * The cart is always the token owner's: no endpoint takes a cart id, and
 * items are authorized by CartItemPolicy, as on the web.
 */
class CartController extends Controller
{
    /**
     * Show the cart.
     */
    public function show(Request $request): CartResource
    {
        return $this->cart($request);
    }

    /**
     * Add a product.
     *
     * Adding a product already in the cart increases its quantity. The
     * quantity is checked against stock.
     */
    public function store(AddToCartRequest $request, AddToCart $addToCart): CartResource
    {
        try {
            $addToCart->handle($request->user(), $request->product(), $request->integer('quantity'));
        } catch (InsufficientStock $exception) {
            throw ValidationException::withMessages(['quantity' => $exception->getMessage()]);
        }

        return $this->cart($request);
    }

    /**
     * Change a quantity.
     */
    public function update(UpdateCartItemRequest $request, CartItem $item): CartResource
    {
        $item->update(['quantity' => $request->integer('quantity')]);

        return $this->cart($request);
    }

    /**
     * Remove an item.
     */
    public function destroy(Request $request, CartItem $item): CartResource
    {
        Gate::authorize('delete', $item);

        $item->delete();

        return $this->cart($request);
    }

    private function cart(Request $request): CartResource
    {
        $cart = $request->user()->cart()->with(['items' => fn ($query) => $query->with('product')->orderBy('id')])->first();

        return new CartResource($cart);
    }
}
