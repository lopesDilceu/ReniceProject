<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): Response
    {
        Gate::authorize('view', $order);

        return Inertia::render('orders/show', [
            'order' => (new OrderResource($order->load('items')))->resolve($request),
        ]);
    }
}
