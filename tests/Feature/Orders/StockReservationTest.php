<?php

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Orders\ChangeOrderStatus;
use App\Actions\Orders\ExpireUnpaidOrders;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['shop.payment_window_minutes' => 30]);
    $this->product = Product::factory()->inStock(10)->create();
    $this->admin = User::factory()->admin()->create();

    $this->placeOrder = function (int $quantity = 3): Order {
        $customer = User::factory()->create();
        Cart::factory()->for($customer)->create()->items()->create(['product_id' => $this->product->id, 'quantity' => $quantity]);

        return app(PlaceOrder::class)->handle($customer, Address::factory()->for($customer)->create());
    };
});

it('reserves the stock at checkout until the payment window closes', function () {
    config(['shop.payment_window_minutes' => 45]);
    $this->freezeSecond();

    $order = ($this->placeOrder)(3);
    $movement = $this->product->inventoryMovements()->sole();

    expect($order->payment_due_at->equalTo(now()->addMinutes(45)))->toBeTrue()
        ->and($movement->type)->toBe(InventoryMovementType::Reservation)
        ->and($movement->quantity)->toBe(-3)
        ->and($this->product->fresh()->stock)->toBe(7);
});

it('records the movement type that matches each way stock comes back', function (OrderStatus $via, InventoryMovementType $type) {
    $order = ($this->placeOrder)(3);

    if ($via === OrderStatus::Refunded) {
        app(ChangeOrderStatus::class)->handle($order, OrderStatus::Paid, $this->admin);
    }

    app(ChangeOrderStatus::class)->handle($order, $via, $this->admin, 'Motivo.');

    $back = $this->product->inventoryMovements()->latest('id')->first();

    expect($back->type)->toBe($type)
        ->and($back->quantity)->toBe(3)
        ->and($this->product->fresh()->stock)->toBe(10);
})->with([
    'cancelled before payment' => [OrderStatus::Cancelled, InventoryMovementType::Release],
    'refunded after payment' => [OrderStatus::Refunded, InventoryMovementType::Return],
]);

describe('expiry', function () {
    it('cancels overdue unpaid orders as the system and releases their stock', function () {
        $overdue = ($this->placeOrder)(3);
        $this->travel(31)->minutes();

        $this->artisan('orders:expire-unpaid')->expectsOutput('Pedidos expirados: 1.')->assertSuccessful();

        $change = $overdue->statusChanges()->get()->last();

        expect($overdue->fresh()->status)->toBe(OrderStatus::Cancelled)
            ->and($change->user_id)->toBeNull()
            ->and($change->reason)->toBe(ExpireUnpaidOrders::REASON)
            ->and($this->product->inventoryMovements()->latest('id')->first()->type)->toBe(InventoryMovementType::Release)
            ->and($this->product->fresh()->stock)->toBe(10);
    });

    it('leaves orders inside the window and orders already paid', function () {
        $paid = ($this->placeOrder)(2);
        app(ChangeOrderStatus::class)->handle($paid, OrderStatus::Paid, $this->admin);
        $this->travel(20)->minutes();
        $recent = ($this->placeOrder)(3);
        $this->travel(15)->minutes();

        expect(app(ExpireUnpaidOrders::class)->handle())->toBe(0)
            ->and($paid->fresh()->status)->toBe(OrderStatus::Paid)
            ->and($recent->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and($this->product->fresh()->stock)->toBe(5);
    });

    it('expires each order once', function () {
        ($this->placeOrder)(3);
        $this->travel(31)->minutes();

        expect(app(ExpireUnpaidOrders::class)->handle())->toBe(1)
            ->and(app(ExpireUnpaidOrders::class)->handle())->toBe(0)
            ->and($this->product->fresh()->stock)->toBe(10);
    });

    it('runs every minute without overlapping', function () {
        $event = collect(app(Schedule::class)->events())
            ->first(fn (Event $event) => str_contains((string) $event->command, 'orders:expire-unpaid'));

        expect($event)->not->toBeNull()
            ->and($event->expression)->toBe('* * * * *')
            ->and($event->withoutOverlapping)->toBeTrue();
    });
});

it('tells the customer until when the items are reserved, only while unpaid', function () {
    $order = ($this->placeOrder)(1);

    $this->actingAs($order->user)->get(route('orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page->where('order.payment_due_at', $order->payment_due_at->toIso8601String()));

    app(ChangeOrderStatus::class)->handle($order, OrderStatus::Paid, $this->admin);

    $this->actingAs($order->user)->get(route('orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page->where('order.payment_due_at', null));
});
