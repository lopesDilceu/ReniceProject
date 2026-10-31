<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Orders\ChangeOrderStatus;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $status = OrderStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q'));

        $orders = Order::query()
            ->with('user:id,name,email')
            ->withCount('items')
            ->when($status, fn (Builder $query, OrderStatus $status) => $query->where('status', $status))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('id', ctype_digit(ltrim($search, '#')) ? (int) ltrim($search, '#') : 0)
                ->orWhereHas('user', fn (Builder $user) => $user
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('email', "%{$search}%"))))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/orders/index', [
            'orders' => $orders->through(fn (Order $order) => [
                ...(new OrderResource($order))->resolve($request),
                'customer' => $order->user->name,
            ]),
            'filters' => ['status' => $status?->value, 'q' => $search],
            'statuses' => collect(OrderStatus::cases())->map(fn (OrderStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        // Loading the actor makes the resource show who made each change.
        $order->load(['items.product', 'user:id,name,email', 'statusChanges.user:id,name', 'payment']);

        return Inertia::render('admin/orders/show', [
            'order' => (new OrderResource($order))->resolve($request),
            'customer' => ['name' => $order->user->name, 'email' => $order->user->email],
            'payment' => $order->payment ? (new PaymentResource($order->payment))->resolve($request) : null,
            'nextStatuses' => collect($order->status->nextStatuses())
                ->map(fn (OrderStatus $s) => ['value' => $s->value, 'label' => $s->label(), 'requires_reason' => $s->requiresReason()]),
        ]);
    }

    public function update(ChangeOrderStatusRequest $request, Order $order, ChangeOrderStatus $change): RedirectResponse
    {
        try {
            $change->handle($order, $request->status(), $request->user(), $request->validated('reason'));
        } catch (InvalidOrderTransition $exception) {
            throw ValidationException::withMessages(['status' => $exception->getMessage()]);
        }

        $this->toast('success', 'Status do pedido atualizado.');

        return back();
    }
}
