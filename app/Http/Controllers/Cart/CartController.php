<?php

namespace App\Http\Controllers\Cart;

use App\Actions\Cart\AddToCart;
use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function show(Request $request): Response
    {
        $cart = $request->user()?->cart;
        $items = $cart?->items()->with('product')->orderBy('id')->get() ?? collect();

        return Inertia::render('cart/show', [
            'items' => CartItemResource::collection($items)->resolve($request),
            // Only this customer's items (v1 summed every cart: BUG-03).
            'total_cents' => $cart?->totalCents() ?? 0,
        ]);
    }

    public function store(AddToCartRequest $request, AddToCart $addToCart): RedirectResponse
    {
        try {
            $addToCart->handle($request->user(), $request->product(), $request->integer('quantity'));
        } catch (InsufficientStock $exception) {
            throw ValidationException::withMessages(['quantity' => $exception->getMessage()]);
        }

        $this->toast('success', 'Produto adicionado ao carrinho.');

        return back();
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        $item->update(['quantity' => $request->integer('quantity')]);

        return back();
    }

    public function destroy(CartItem $item): RedirectResponse
    {
        Gate::authorize('delete', $item);

        $item->delete();

        $this->toast('success', 'Produto removido do carrinho.');

        return back();
    }
}
