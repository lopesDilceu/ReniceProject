<?php

use App\Actions\Orders\ExpireUnpaidOrders;
use App\Actions\Payments\CreatePixCharge;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['payments.gateway' => 'fake', 'shop.payment_window_minutes' => 60]);

    $this->customer = User::factory()->create();
    $this->address = Address::factory()->for($this->customer)->create();
    $this->product = Product::factory()->inStock(10)->create(['price_cents' => 1500]);
    Cart::factory()->for($this->customer)->create()->items()->create(['product_id' => $this->product->id, 'quantity' => 2]);

    $this->checkout = fn () => $this->actingAs($this->customer)->post(route('checkout.store'), ['address_id' => $this->address->id]);

    $this->useMercadoPago = function () {
        config([
            'payments.gateway' => 'mercadopago',
            'services.mercadopago.access_token' => 'TEST-token',
            'services.mercadopago.base_url' => 'https://api.mercadopago.test',
        ]);
    };
    $this->mercadoPagoCreates = fn () => Http::response([
        'id' => 555,
        'status' => 'pending',
        'point_of_interaction' => ['transaction_data' => ['qr_code' => '00020126PIX', 'qr_code_base64' => 'iVBORw0KGgo=']],
    ], 201);
});

it('generates the Pix at checkout and shows it on the order page', function () {
    $this->freezeSecond();

    ($this->checkout)()->assertInertiaFlash('toast.type', 'success');

    $order = Order::sole();
    $payment = $order->payment;

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->amount_cents)->toBe(3000)
        ->and($payment->expires_at->equalTo($order->payment_due_at))->toBeTrue()
        ->and($payment->pix_qr_image)->toStartWith('data:image/svg+xml;base64,');

    $this->actingAs($this->customer)->get(route('orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->where('payment.status', 'pending')
            ->where('payment.pix_code', $payment->pix_code)
            ->where('canSimulatePayment', true));
});

it('still places the order when the provider is down, and lets the customer retry', function () {
    ($this->useMercadoPago)();
    Http::fake(['api.mercadopago.test/*' => Http::sequence()
        ->push(['message' => 'unavailable'], 503)
        ->pushResponse(($this->mercadoPagoCreates)())]);

    ($this->checkout)()->assertInertiaFlash('toast.type', 'warning');

    $order = Order::sole();
    expect($order->payment)->toBeNull()
        ->and($this->product->fresh()->stock)->toBe(8);

    $this->actingAs($this->customer)->post(route('orders.pix.store', $order))->assertRedirect();

    expect($order->fresh()->payment->provider_payment_id)->toBe('555');
});

it('creates one charge however many times it is asked', function () {
    ($this->useMercadoPago)();
    Http::fake(['api.mercadopago.test/*' => ($this->mercadoPagoCreates)()]);
    ($this->checkout)();
    $order = Order::sole();

    app(CreatePixCharge::class)->handle($order);
    $this->actingAs($this->customer)->post(route('orders.pix.store', $order));

    expect(Payment::count())->toBe(1);
    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => $request->hasHeader('X-Idempotency-Key', "renice-order-{$order->id}"));
});

it('never asks the provider for a Pix that expires in under 30 minutes', function () {
    config(['shop.payment_window_minutes' => 10]);
    $this->freezeSecond();

    ($this->checkout)();

    expect(Order::sole()->payment->expires_at->equalTo(now()->addMinutes(31)))->toBeTrue();
});

it('cancels the charge at the provider when the order expires', function () {
    ($this->useMercadoPago)();
    Http::fake(['api.mercadopago.test/v1/payments' => ($this->mercadoPagoCreates)(), 'api.mercadopago.test/v1/payments/555' => Http::response(['id' => 555, 'status' => 'cancelled'])]);
    ($this->checkout)();

    $this->travel(61)->minutes();
    app(ExpireUnpaidOrders::class)->handle();

    expect(Order::sole()->status)->toBe(OrderStatus::Cancelled)
        ->and(Payment::sole()->status)->toBe(PaymentStatus::Cancelled);
    Http::assertSent(fn (Request $request) => $request->method() === 'PUT' && $request['status'] === 'cancelled');
});

it('simulates the payment in the demo through the webhook path', function () {
    ($this->checkout)();
    $order = Order::sole();

    $this->actingAs($this->customer)->post(route('orders.pix.simulate', $order))->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($order->statusChanges()->get()->last()->reason)->toBe('Pix confirmado.');

    $this->actingAs($this->customer)->get(route('orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page->where('payment.status', 'approved')->where('payment.pix_code', null));
});

it('offers no simulation with a real provider', function () {
    ($this->checkout)();
    ($this->useMercadoPago)();
    $order = Order::sole();

    $this->actingAs($this->customer)->post(route('orders.pix.simulate', $order))->assertNotFound();
    $this->actingAs($this->customer)->get(route('orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page->where('canSimulatePayment', false));

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

it('keeps other customers away from the Pix actions', function (string $route) {
    ($this->checkout)();

    $this->actingAs(User::factory()->create())->post(route($route, Order::sole()))->assertForbidden();
})->with(['orders.pix.store', 'orders.pix.simulate']);

it('shows the admin the payment of an order', function () {
    ($this->checkout)();
    $order = Order::sole();

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->where('payment.provider', 'fake')
            ->where('payment.status_label', 'Aguardando Pix'));
});
