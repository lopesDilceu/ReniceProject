<?php

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Orders\ChangeOrderStatus;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransition;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusChange;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->product = Product::factory()->inStock(10)->create();
    Cart::factory()->for($this->customer)->create()->items()->create(['product_id' => $this->product->id, 'quantity' => 2]);

    $this->order = app(PlaceOrder::class)->handle($this->customer, Address::factory()->for($this->customer)->create());
    $this->change = fn (OrderStatus $next, ?User $actor = null, ?string $reason = null) => app(ChangeOrderStatus::class)->handle($this->order, $next, $actor, $reason);
});

it('starts the timeline when the order is placed', function () {
    $first = $this->order->statusChanges()->sole();

    expect($first->from_status)->toBeNull()
        ->and($first->to_status)->toBe(OrderStatus::PendingPayment)
        ->and($first->user->is($this->customer))->toBeTrue()
        ->and($first->reason)->toBeNull();
});

it('records every transition with who, when and why', function () {
    $this->travelTo(now()->addDay());
    ($this->change)(OrderStatus::Paid, $this->admin);
    $this->travelTo(now()->addDay());
    ($this->change)(OrderStatus::Shipped, $this->admin);
    $this->travelTo(now()->addDays(3));
    ($this->change)(OrderStatus::Refunded, $this->admin, 'Chegou quebrado.');

    $timeline = $this->order->statusChanges()->with('user')->get();

    expect($timeline->map(fn (OrderStatusChange $change) => [$change->from_status?->value, $change->to_status->value])->all())->toBe([
        [null, 'pending_payment'],
        ['pending_payment', 'paid'],
        ['paid', 'shipped'],
        ['shipped', 'refunded'],
    ])
        ->and($timeline->last()->reason)->toBe('Chegou quebrado.')
        ->and($timeline->last()->user->is($this->admin))->toBeTrue()
        ->and($timeline->last()->created_at->toDateString())->toBe(now()->toDateString())
        ->and($timeline->pluck('created_at')->sort()->values()->all())->toEqual($timeline->pluck('created_at')->all());
});

it('records system changes without an actor', function () {
    ($this->change)(OrderStatus::Cancelled, null, 'Pix expirado.');

    expect($this->order->statusChanges()->get()->last()->user_id)->toBeNull();
});

it('throws on a transition the lifecycle does not allow and records nothing', function () {
    expect(fn () => ($this->change)(OrderStatus::Delivered, $this->admin))
        ->toThrow(InvalidOrderTransition::class, 'Um pedido "Aguardando pagamento" não pode passar para "Entregue".');

    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and($this->order->statusChanges()->count())->toBe(1);
});

it('requires a reason to undo a sale', function (OrderStatus $next, ?string $reason) {
    expect(fn () => ($this->change)($next, $this->admin, $reason))->toThrow(InvalidOrderTransition::class);

    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and($this->product->fresh()->stock)->toBe(8)
        ->and($this->order->statusChanges()->count())->toBe(1);
})->with([
    'cancel without reason' => [OrderStatus::Cancelled, null],
    'cancel with blank reason' => [OrderStatus::Cancelled, '   '],
]);

it('keeps the timeline append-only', function () {
    $change = $this->order->statusChanges()->first();

    expect(fn () => $change->forceFill(['reason' => 'editado'])->save())->toThrow(LogicException::class)
        ->and(fn () => $change->delete())->toThrow(LogicException::class);
});

it('keeps the order status equal to the last timeline entry', function () {
    ($this->change)(OrderStatus::Paid, $this->admin);
    ($this->change)(OrderStatus::Shipped, $this->admin);

    expect(Order::find($this->order->id)->status)->toBe($this->order->statusChanges()->get()->last()->to_status);
});
