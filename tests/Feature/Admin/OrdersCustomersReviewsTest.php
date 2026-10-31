<?php

use App\Actions\Checkout\PlaceOrder;
use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

// Names and e-mails are fixed wherever a test searches: Faker pt_BR can
// generate a "Joana" and turn a search assertion flaky.
beforeEach(fn () => $this->admin = User::factory()->admin()->create(['name' => 'Admin Renice', 'email' => 'admin@renice.test']));

function placedOrder(int $stock = 10, int $quantity = 3): array
{
    $customer = User::factory()->create();
    $product = Product::factory()->inStock($stock)->create();
    Cart::factory()->for($customer)->create()->items()->create(['product_id' => $product->id, 'quantity' => $quantity]);
    $order = app(PlaceOrder::class)->handle($customer, Address::factory()->for($customer)->create());

    return [$order, $product];
}

describe('orders', function () {
    it('moves an order through its lifecycle', function () {
        [$order] = placedOrder();

        foreach ([OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
            $this->actingAs($this->admin)->patch(route('admin.orders.update', $order), ['status' => $status->value])
                ->assertSessionHasNoErrors();
        }

        expect($order->fresh()->status)->toBe(OrderStatus::Delivered);
    });

    it('refuses transitions that skip or reverse steps', function (OrderStatus $from, OrderStatus $to) {
        $order = Order::factory()->status($from)->create();

        $this->actingAs($this->admin)->patch(route('admin.orders.update', $order), ['status' => $to->value])
            ->assertSessionHasErrors('status');

        expect($order->fresh()->status)->toBe($from);
    })->with([
        'pending → delivered' => [OrderStatus::PendingPayment, OrderStatus::Delivered],
        'delivered → paid' => [OrderStatus::Delivered, OrderStatus::Paid],
        'cancelled → paid' => [OrderStatus::Cancelled, OrderStatus::Paid],
    ]);

    it('puts the items back in stock when an order is cancelled', function () {
        [$order, $product] = placedOrder(stock: 10, quantity: 3);
        expect($product->fresh()->stock)->toBe(7);

        $this->actingAs($this->admin)->patch(route('admin.orders.update', $order), ['status' => 'cancelled']);

        $restock = $product->inventoryMovements()->latest('id')->first();

        expect($product->fresh()->stock)->toBe(10)
            ->and($restock->quantity)->toBe(3)
            ->and($restock->source->is($order))->toBeTrue()
            ->and($restock->note)->toBe("Pedido #{$order->id} Cancelado");
    });

    it('filters by status and searches by number or customer', function () {
        $paid = Order::factory()->status(OrderStatus::Paid)->for(User::factory()->state(['name' => 'Joana Lima']))->create();
        Order::factory()->status(OrderStatus::PendingPayment)
            ->for(User::factory()->state(['name' => 'Pedro Alves', 'email' => 'pedro@example.com']))
            ->create();

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'paid']))
            ->assertInertia(fn (Assert $page) => $page->has('orders.data', 1)->where('orders.data.0.id', $paid->id));

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['q' => 'joana']))
            ->assertInertia(fn (Assert $page) => $page->has('orders.data', 1));

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['q' => "#{$paid->id}"]))
            ->assertInertia(fn (Assert $page) => $page->has('orders.data', 1)->where('orders.data.0.id', $paid->id));
    });

    it('offers only the allowed next statuses', function () {
        $order = Order::factory()->status(OrderStatus::Paid)->create();

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('nextStatuses', [
                    ['value' => 'shipped', 'label' => 'Enviado'],
                    ['value' => 'refunded', 'label' => 'Reembolsado'],
                ]));
    });
});

describe('customers', function () {
    it('grants and removes admin access as an explicit action', function () {
        $customer = User::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.customers.toggle-admin', $customer));
        expect($customer->fresh()->is_admin)->toBeTrue();

        $this->actingAs($this->admin)->patch(route('admin.customers.toggle-admin', $customer));
        expect($customer->fresh()->is_admin)->toBeFalse();
    });

    it('does not let admins remove their own access', function () {
        $this->actingAs($this->admin)->patch(route('admin.customers.toggle-admin', $this->admin));

        expect($this->admin->fresh()->is_admin)->toBeTrue();
    });

    it('searches by name or e-mail', function () {
        User::factory()->create(['name' => 'Joana Lima', 'email' => 'joana@example.com']);

        $this->actingAs($this->admin)->get(route('admin.customers.index', ['q' => 'joana']))
            ->assertInertia(fn (Assert $page) => $page->has('customers.data', 1));
    });
});

it('removes a review', function () {
    $review = Review::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.reviews.destroy', $review));

    expect($review->fresh())->toBeNull();
});

it('summarizes the last 30 days on the dashboard', function () {
    $customer = User::factory()->create();
    Order::factory()->for($customer)->status(OrderStatus::Delivered)->create(['total_cents' => 5000]);
    Order::factory()->for($customer)->status(OrderStatus::Paid)->create(['total_cents' => 2000]);
    Order::factory()->for($customer)->status(OrderStatus::PendingPayment)->create(['total_cents' => 9999]);
    Order::factory()->for($customer)->status(OrderStatus::Paid)->create(['total_cents' => 7000, 'created_at' => now()->subDays(45)]);
    Product::factory()->inStock(2)->create(['name' => 'Quase acabando']);

    $this->actingAs($this->admin)->get(route('admin.home'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('revenue', ['current' => 7000, 'previous' => 7000])
            ->where('orders.current', 3)
            ->where('pendingPayment', 1)
            ->where('toShip', 2)
            ->where('lowStock.0.name', 'Quase acabando'));
});
