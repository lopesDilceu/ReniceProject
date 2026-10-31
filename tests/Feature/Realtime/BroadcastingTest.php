<?php

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Inventory\RecordInventoryMovement;
use App\Actions\Orders\ChangeOrderStatus;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Events\Broadcast\OrderUpdated;
use App\Events\Broadcast\StockRunningLow;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

/**
 * Broadcast events that reached the queue, which only happens after the
 * surrounding transaction commits (ShouldDispatchAfterCommit).
 *
 * @return list<object>
 */
function broadcasted(string $class): array
{
    return Queue::pushed(BroadcastEvent::class)
        ->map(fn (BroadcastEvent $job) => $job->event)
        ->filter(fn (object $event) => $event instanceof $class)
        ->values()
        ->all();
}

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->product = Product::factory()->inStock(10)->create();

    $this->placeOrder = function (int $quantity = 2): Order {
        Cart::factory()->for($this->customer)->create()->items()->create(['product_id' => $this->product->id, 'quantity' => $quantity]);

        return app(PlaceOrder::class)->handle($this->customer, Address::factory()->for($this->customer)->create());
    };
});

describe('events', function () {
    beforeEach(fn () => Queue::fake());

    it('pushes a new order to the admin dashboard and to its order page', function () {
        $order = ($this->placeOrder)();

        [$event] = broadcasted(OrderUpdated::class);

        expect(collect($event->broadcastOn())->map->name->all())->toBe(['private-admin', "private-orders.{$order->id}"])
            ->and($event->broadcastAs())->toBe('order.updated')
            ->and($event->broadcastWith())->toBe([
                'id' => $order->id,
                'status' => 'pending_payment',
                'status_label' => 'Aguardando pagamento',
                'total_cents' => $order->total_cents,
                'placed' => true,
            ]);
    });

    it('pushes each status change, such as a confirmed payment', function () {
        $order = ($this->placeOrder)();
        app(ChangeOrderStatus::class)->handle($order, OrderStatus::Paid, null);

        $last = collect(broadcasted(OrderUpdated::class))->last();

        expect($last->broadcastWith())->toMatchArray(['status' => 'paid', 'placed' => false]);
    });

    it('pushes nothing for a change that is rolled back', function () {
        $order = ($this->placeOrder)();

        try {
            DB::transaction(function () use ($order) {
                app(ChangeOrderStatus::class)->handle($order, OrderStatus::Paid, null);

                throw new RuntimeException('rolled back');
            });
        } catch (RuntimeException) {
        }

        expect(collect(broadcasted(OrderUpdated::class))->map(fn (OrderUpdated $event) => $event->broadcastWith()['status'])->all())
            ->toBe(['pending_payment']);
    });

    it('pushes low stock to admins only, with no customer data', function () {
        config(['shop.low_stock_threshold' => 5]);
        app(RecordInventoryMovement::class)->handle($this->product, InventoryMovementType::Adjustment, -6);

        [$event] = broadcasted(StockRunningLow::class);

        expect(collect($event->broadcastOn())->map->name->all())->toBe(['private-admin'])
            ->and($event->broadcastAs())->toBe('stock.low')
            ->and($event->broadcastWith())->toBe(['product_id' => $this->product->id, 'name' => $this->product->name, 'stock' => 4]);
    });
});

describe('channel authorization', function () {
    beforeEach(function () {
        // Tests broadcast to the null driver; authorize against Reverb's
        // (Pusher protocol), which signs the subscription locally.
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test',
        ]);
        app(BroadcastManager::class)->purge();
        require base_path('routes/channels.php');

        $this->join = fn (string $channel) => $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => $channel,
        ]);
    });

    it('lets only admins into the admin channel', function () {
        $this->actingAs(User::factory()->admin()->create());
        ($this->join)('private-admin')->assertOk()->assertJsonStructure(['auth']);

        $this->actingAs($this->customer);
        ($this->join)('private-admin')->assertForbidden();
    });

    it('lets an order\'s channel be joined by its customer and admins only', function () {
        $order = Order::factory()->for($this->customer)->create();

        $this->actingAs($this->customer);
        ($this->join)("private-orders.{$order->id}")->assertOk();

        $this->actingAs(User::factory()->create());
        ($this->join)("private-orders.{$order->id}")->assertForbidden();

        $this->actingAs(User::factory()->admin()->create());
        ($this->join)("private-orders.{$order->id}")->assertOk();
    });

    it('keeps guests out', function () {
        ($this->join)('private-admin')->assertForbidden();
    });
});
