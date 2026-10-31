<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('lists only the customer\'s orders, newest first, with item counts', function () {
    $customer = User::factory()->create();
    $old = Order::factory()->for($customer)->create();
    $new = Order::factory()->for($customer)->create();
    OrderItem::factory(3)->for($new)->create();
    Order::factory()->create();

    $this->actingAs($customer)->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/index')
            ->has('orders.data', 2)
            ->where('orders.data.0.id', $new->id)
            ->where('orders.data.0.items_count', 3)
            ->where('orders.data.1.id', $old->id));
});

it('paginates ten orders per page', function () {
    $customer = User::factory()->create();
    Order::factory(11)->for($customer)->create();

    $this->actingAs($customer)->get(route('orders.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page->has('orders.data', 1));
});

it('shows the customer when and why the order changed, not which admin did it', function () {
    $order = Order::factory()->status(OrderStatus::Cancelled)->create();
    OrderStatusChange::record($order, OrderStatus::PendingPayment, User::factory()->admin()->create(), 'Pagamento não identificado.');

    $this->actingAs($order->user)->get(route('orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->where('order.timeline.0.status_label', 'Cancelado')
            ->where('order.timeline.0.reason', 'Pagamento não identificado.')
            ->missing('order.timeline.0.actor'));
});

it('requires sign-in', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});
