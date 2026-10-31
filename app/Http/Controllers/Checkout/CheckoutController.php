<?php

namespace App\Http\Controllers\Checkout;

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Payments\CreatePixCharge;
use App\Exceptions\EmptyCart;
use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\CartItemResource;
use App\Payments\PaymentGatewayException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Checkout is a POST with CSRF protection. v1 placed orders with a GET to
 * /usuarios/finalizar/{cart}, which any page could trigger (SEC-04).
 */
class CheckoutController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $cart = $user->cart;
        $items = $cart?->items()->with('product')->orderBy('id')->get() ?? collect();

        if ($items->isEmpty()) {
            $this->toast('info', 'Seu carrinho está vazio.');

            return to_route('cart.show');
        }

        return Inertia::render('checkout/show', [
            'items' => CartItemResource::collection($items)->resolve($request),
            'total_cents' => $cart?->totalCents() ?? 0,
            'addresses' => AddressResource::collection($user->addresses()->latest('id')->get())->resolve($request),
        ]);
    }

    public function store(PlaceOrderRequest $request, PlaceOrder $placeOrder, CreatePixCharge $createCharge): RedirectResponse
    {
        try {
            $order = $placeOrder->handle($request->user(), $request->address());
        } catch (EmptyCart) {
            $this->toast('info', 'Seu carrinho está vazio.');

            return to_route('cart.show');
        } catch (InsufficientStock $exception) {
            throw ValidationException::withMessages(['cart' => $exception->getMessage()]);
        }

        // The order and its stock reservation are already committed; if the
        // provider is down, the customer generates the Pix from the order page.
        try {
            $createCharge->handle($order);
            $this->toast('success', 'Pedido recebido! Pague com Pix para confirmar.');
        } catch (PaymentGatewayException $exception) {
            report($exception);
            $this->toast('warning', 'Pedido recebido, mas não conseguimos gerar o Pix agora. Tente novamente na página do pedido.');
        }

        return to_route('orders.show', $order);
    }
}
