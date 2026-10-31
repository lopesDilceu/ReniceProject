<?php

namespace App\Http\Controllers\Checkout;

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Payments\CreatePixCharge;
use App\Exceptions\EmptyCart;
use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Http\Resources\CartItemResource;
use App\Models\Address;
use App\Payments\PaymentGatewayException;
use App\Support\CheckoutConfirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Where the customer confirms a checkout an AI agent prepared (MCP
 * start-checkout). The URL is signed and expiring; it pins the customer,
 * the address and the exact cart, which are checked again on confirm.
 */
class ConfirmCheckoutController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        if ($redirect = $this->rejectStale($request)) {
            return $redirect;
        }

        $user = $request->user();
        $cart = $user->cart;

        return Inertia::render('checkout/confirm', [
            'items' => CartItemResource::collection($cart?->items()->with('product')->orderBy('id')->get() ?? [])->resolve($request),
            'total_cents' => $cart?->totalCents() ?? 0,
            'address' => (new AddressResource($this->address($request)))->resolve($request),
            // The form posts back to this exact signed URL.
            'confirmUrl' => $request->fullUrl(),
        ]);
    }

    public function store(Request $request, PlaceOrder $placeOrder, CreatePixCharge $createCharge): RedirectResponse
    {
        if ($redirect = $this->rejectStale($request)) {
            return $redirect;
        }

        try {
            $order = $placeOrder->handle($request->user(), $this->address($request));
        } catch (EmptyCart|InsufficientStock $exception) {
            $this->toast('error', $exception->getMessage());

            return to_route('cart.show');
        }

        try {
            $createCharge->handle($order);
        } catch (PaymentGatewayException $exception) {
            report($exception);
        }

        $this->toast('success', 'Pedido confirmado! Pague com Pix para concluir.');

        return to_route('orders.show', $order);
    }

    /**
     * Another account's link is refused outright; a cart that changed since
     * the agent prepared it sends the customer to the normal checkout.
     */
    private function rejectStale(Request $request): ?RedirectResponse
    {
        abort_unless((int) $request->query('customer') === $request->user()->getKey(), 403);

        if (! hash_equals(CheckoutConfirmation::fingerprint($request->user(), $this->address($request)), (string) $request->query('cart'))) {
            $this->toast('warning', 'Seu carrinho mudou desde que o assistente preparou o pedido. Revise e finalize por aqui.');

            return to_route('checkout.show');
        }

        return null;
    }

    private function address(Request $request): Address
    {
        return $request->user()->addresses()->findOrFail((int) $request->query('address'));
    }
}
