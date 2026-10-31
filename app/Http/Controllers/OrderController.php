<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest('id')
            ->paginate(10);

        return Inertia::render('orders/index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        Gate::authorize('view', $order);

        // Without the actor: customers see when and why, not which admin.
        $order->load(['items.product', 'statusChanges', 'payment']);
        $user = $request->user();

        // Products from this order the customer can still review: delivered,
        // their own order, not reviewed yet (see ProductPolicy::review).
        $reviewable = $order->status === OrderStatus::Delivered && $order->user_id === $user->getKey()
            ? $order->items->pluck('product_id')->diff($user->reviews()->pluck('product_id'))->values()
            : collect();

        return Inertia::render('orders/show', [
            'order' => (new OrderResource($order))->resolve($request),
            'reviewableProductIds' => $reviewable,
            'payment' => $order->payment ? (new PaymentResource($order->payment))->resolve($request) : null,
            'canSimulatePayment' => PixController::canSimulate() && $order->user_id === $user->getKey(),
        ]);
    }
}
