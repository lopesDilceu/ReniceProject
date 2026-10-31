<?php

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Payments\MercadoPago\MercadoPagoGateway;
use App\Payments\PaymentGatewayException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->gateway = new MercadoPagoGateway('TEST-token', 'https://api.mercadopago.test');
    $this->order = Order::factory()
        ->for(User::factory()->state(['name' => 'Joana Lima Souza', 'email' => 'joana@example.com', 'cpf' => '52998224725']))
        ->create(['total_cents' => 3650]);
});

it('creates a Pix charge with an idempotency key and reads the QR code', function () {
    Http::fake(['api.mercadopago.test/v1/payments' => Http::response([
        'id' => 1325968743,
        'status' => 'pending',
        'point_of_interaction' => ['transaction_data' => ['qr_code' => '00020126PIX', 'qr_code_base64' => 'iVBORw0KGgo=']],
    ], 201)]);

    $charge = $this->gateway->createPixCharge($this->order, Carbon::parse('2026-10-31 18:00:00', 'UTC'), 'renice-order-7');

    expect($charge->providerPaymentId)->toBe('1325968743')
        ->and($charge->status)->toBe(PaymentStatus::Pending)
        ->and($charge->code)->toBe('00020126PIX')
        ->and($charge->qrImage)->toBe('data:image/png;base64,iVBORw0KGgo=');

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->hasHeader('Authorization', 'Bearer TEST-token')
        && $request->hasHeader('X-Idempotency-Key', 'renice-order-7')
        && $request['transaction_amount'] === 36.5
        && $request['payment_method_id'] === 'pix'
        && $request['external_reference'] === "order-{$this->order->id}"
        && $request['date_of_expiration'] === '2026-10-31T18:00:00.000+00:00'
        && $request['payer'] === [
            'email' => 'joana@example.com',
            'first_name' => 'Joana',
            'last_name' => 'Lima Souza',
            'identification' => ['type' => 'CPF', 'number' => '52998224725'],
        ]);
});

it('reads the current status of a charge', function (string $theirs, PaymentStatus $ours) {
    Http::fake(['api.mercadopago.test/v1/payments/42' => Http::response(['id' => 42, 'status' => $theirs])]);

    expect($this->gateway->status('42'))->toBe($ours);
})->with([
    ['approved', PaymentStatus::Approved],
    ['pending', PaymentStatus::Pending],
    ['in_process', PaymentStatus::Pending],
    ['rejected', PaymentStatus::Rejected],
    ['cancelled', PaymentStatus::Cancelled],
    ['refunded', PaymentStatus::Refunded],
    ['charged_back', PaymentStatus::Refunded],
]);

it('cancels a charge', function () {
    Http::fake(['api.mercadopago.test/v1/payments/42' => Http::response(['id' => 42, 'status' => 'cancelled'])]);

    $this->gateway->cancel('42');

    Http::assertSent(fn (Request $request) => $request->method() === 'PUT' && $request['status'] === 'cancelled');
});

it('turns provider errors into PaymentGatewayException', function (Closure $response) {
    Http::fake(['api.mercadopago.test/*' => $response]);

    $this->gateway->status('42');
})->with([
    'server error' => fn () => Http::response(['message' => 'internal_error'], 500),
    'invalid token' => fn () => Http::response(['message' => 'invalid access token'], 401),
    'connection refused' => fn () => throw new ConnectionException('refused'),
    'unknown status' => fn () => Http::response(['id' => 42, 'status' => 'something_new']),
])->throws(PaymentGatewayException::class);

it('refuses a charge without a QR code', function () {
    Http::fake(['api.mercadopago.test/v1/payments' => Http::response(['id' => 1, 'status' => 'pending'], 201)]);

    $this->gateway->createPixCharge($this->order, now()->addHour(), 'renice-order-1');
})->throws(PaymentGatewayException::class, 'did not return a Pix QR code');
