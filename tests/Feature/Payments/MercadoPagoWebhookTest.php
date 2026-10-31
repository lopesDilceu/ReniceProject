<?php

use App\Actions\Orders\ChangeOrderStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WebhookCall;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

const WEBHOOK_SECRET = 'whsec-test';

beforeEach(function () {
    config([
        'payments.gateway' => 'mercadopago',
        'services.mercadopago.access_token' => 'TEST-token',
        'services.mercadopago.base_url' => 'https://api.mercadopago.test',
        'services.mercadopago.webhook_secret' => WEBHOOK_SECRET,
    ]);

    $this->order = Order::factory()->create(['payment_due_at' => now()->addHour()]);
    $this->payment = (new Payment)->forceFill([
        'order_id' => $this->order->id,
        'provider' => 'mercadopago',
        'provider_payment_id' => '1325968743',
        'status' => PaymentStatus::Pending,
        'amount_cents' => $this->order->total_cents,
        'pix_code' => '00020126PIX',
        'pix_qr_image' => 'data:image/png;base64,AA==',
        'expires_at' => now()->addHour(),
    ]);
    $this->payment->save();

    // Successive GETs answer each status in turn; a PUT (cancellation) succeeds.
    $this->providerSays = function (string ...$statuses) {
        $reads = Http::sequence();

        foreach ($statuses as $status) {
            $reads->push(['id' => 1325968743, 'status' => $status]);
        }

        // Later reads repeat the last status.
        $reads->whenEmpty(Http::response(['id' => 1325968743, 'status' => end($statuses)]));

        Http::fake(['api.mercadopago.test/v1/payments/1325968743' => fn (Request $request) => $request->method() === 'PUT'
            ? Http::response(['id' => 1325968743, 'status' => 'cancelled'])
            : $reads($request)]);
    };

    // Sends a notification the way Mercado Pago does: data.id in the query
    // string and the body, signed over data.id, x-request-id and ts.
    $this->notify = function (array $overrides = [], ?string $signature = null) {
        $body = array_replace_recursive([
            'id' => 12345678901,
            'type' => 'payment',
            'action' => 'payment.updated',
            'data' => ['id' => '1325968743'],
        ], $overrides);
        $requestId = 'req-'.$body['id'];
        $ts = '1761926400000';
        $manifest = "id:{$body['data']['id']};request-id:{$requestId};ts:{$ts};";

        return $this->withHeaders([
            'x-request-id' => $requestId,
            'x-signature' => $signature ?? 'ts='.$ts.',v1='.hash_hmac('sha256', $manifest, WEBHOOK_SECRET),
        ])->postJson(route('webhooks.mercadopago', ['data.id' => $body['data']['id'], 'type' => $body['type']]), $body);
    };
});

it('pays the order when the provider confirms the Pix', function () {
    ($this->providerSays)('approved');

    ($this->notify)()->assertOk();

    $change = $this->order->statusChanges()->get()->last();

    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Approved)
        ->and($this->payment->fresh()->paid_at)->not->toBeNull()
        ->and($change->user_id)->toBeNull()
        ->and($change->reason)->toBe('Pix confirmado.')
        ->and(WebhookCall::sole()->processed_at)->not->toBeNull();
});

it('asks the provider for the status instead of trusting the notification', function () {
    // The body claims nothing about the status; the provider says it is still pending.
    ($this->providerSays)('pending');

    ($this->notify)(['action' => 'payment.approved'])->assertOk();

    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    Http::assertSent(fn (Request $request) => $request->method() === 'GET' && str_ends_with($request->url(), '/v1/payments/1325968743'));
});

it('rejects notifications with a missing or wrong signature', function (?string $signature) {
    ($this->providerSays)('approved');

    ($this->notify)(signature: $signature)->assertUnauthorized();

    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and(WebhookCall::count())->toBe(0);
    Http::assertNothingSent();
})->with([
    'missing' => '',
    'wrong hash' => 'ts=1761926400000,v1='.str_repeat('0', 64),
    'tampered timestamp' => 'ts=1,v1='.hash_hmac('sha256', 'id:1325968743;request-id:req-12345678901;ts:1761926400000;', WEBHOOK_SECRET),
]);

it('rejects everything when no webhook secret is configured', function () {
    config(['services.mercadopago.webhook_secret' => '']);

    ($this->notify)()->assertUnauthorized();
});

it('processes a notification the provider sends twice only once', function () {
    ($this->providerSays)('approved');

    ($this->notify)()->assertOk();
    ($this->notify)()->assertOk()->assertContent('Already received.');

    expect(WebhookCall::count())->toBe(1)
        ->and($this->order->statusChanges()->where('to_status', OrderStatus::Paid)->count())->toBe(1);
    Http::assertSentCount(1);
});

it('stays consistent when different notifications report the same payment', function () {
    ($this->providerSays)('approved');

    ($this->notify)(['id' => 1, 'action' => 'payment.created'])->assertOk();
    ($this->notify)(['id' => 2, 'action' => 'payment.updated'])->assertOk();

    expect(WebhookCall::count())->toBe(2)
        ->and($this->order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($this->order->statusChanges()->where('to_status', OrderStatus::Paid)->count())->toBe(1);
});

it('ignores other topics and payments that are not ours', function () {
    ($this->providerSays)('approved');
    Http::fake(['api.mercadopago.test/v1/payments/999' => Http::response(['id' => 999, 'status' => 'approved'])]);

    ($this->notify)(['type' => 'merchant_order'])->assertOk()->assertContent('Ignored.');
    ($this->notify)(['id' => 3, 'data' => ['id' => '999']])->assertOk();

    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and(WebhookCall::where('event_key', '3')->sole()->processed_at)->not->toBeNull();
});

it('refunds the order when the provider reports a refund', function () {
    ($this->providerSays)('approved', 'refunded');

    ($this->notify)(['id' => 1])->assertOk();
    ($this->notify)(['id' => 2])->assertOk();

    expect($this->order->fresh()->status)->toBe(OrderStatus::Refunded)
        ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Refunded);
});

it('keeps an expired order cancelled when a late payment arrives, and flags it for refund', function () {
    ($this->providerSays)('approved');
    app(ChangeOrderStatus::class)->handle($this->order, OrderStatus::Cancelled, null, 'Expirado.');
    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Cancelled);
    Log::spy();

    ($this->notify)()->assertOk();

    expect($this->order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Approved);
    Log::shouldHaveReceived('warning')->withArgs(fn (string $message) => str_contains($message, 'refund it'));
});

it('takes webhooks without a CSRF token, which the provider cannot send', function () {
    // The CSRF middleware is skipped while running tests, so the HTTP tests
    // above cannot catch this: check the route excludes Laravel 13's
    // PreventRequestForgery (ValidateCsrfToken is only a subclass alias).
    $route = Route::getRoutes()->getByName('webhooks.mercadopago');

    expect($route->excludedMiddleware())->toContain(PreventRequestForgery::class)
        ->and($route->gatherMiddleware())->toContain('web');
});
