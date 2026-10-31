<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Payments\CreatePixCharge;
use App\Exceptions\EmptyCart;
use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Payments\PaymentGatewayException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * List orders.
     *
     * The customer's orders, newest first; 10 per page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return OrderResource::collection(
            $request->user()->orders()->withCount('items')->latest('id')->paginate(10)
        );
    }

    /**
     * Show an order.
     *
     * With its items, timeline and Pix payment. While the order waits for
     * payment, `payment.pix_code` and `payment.pix_qr_image` are what the
     * customer pays with.
     */
    public function show(Request $request, Order $order): OrderResource
    {
        Gate::authorize('view', $order);

        return new OrderResource($order->load(['items', 'statusChanges', 'payment']));
    }

    /**
     * Place an order.
     *
     * Turns the cart into an order, reserves the stock until
     * `payment_due_at` and creates the Pix charge. If the payment provider is
     * unavailable, the order is still placed and `payment` is null; the
     * customer can pay from the store's order page. Requires a confirmed
     * e-mail.
     */
    public function store(PlaceOrderRequest $request, PlaceOrder $placeOrder, CreatePixCharge $createCharge): JsonResponse
    {
        try {
            $order = $placeOrder->handle($request->user(), $request->address());
        } catch (EmptyCart $exception) {
            throw ValidationException::withMessages(['cart' => $exception->getMessage()]);
        } catch (InsufficientStock $exception) {
            throw ValidationException::withMessages(['cart' => $exception->getMessage()]);
        }

        try {
            $createCharge->handle($order);
        } catch (PaymentGatewayException $exception) {
            report($exception);
        }

        return (new OrderResource($order->load(['items', 'statusChanges', 'payment'])))
            ->response()
            ->setStatusCode(201);
    }
}
