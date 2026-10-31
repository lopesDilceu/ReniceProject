<?php

namespace App\Payments\MercadoPago;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Payments\PaymentGateway;
use App\Payments\PaymentGatewayException;
use App\Payments\PixCharge;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Pix through the Mercado Pago Payments API (POST/GET/PUT /v1/payments).
 * Notifications arrive through the webhook configured in the Mercado Pago
 * panel, which signs them (see WebhookSignature).
 */
class MercadoPagoGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $accessToken,
        private readonly string $baseUrl = 'https://api.mercadopago.com',
    ) {}

    public function name(): string
    {
        return 'mercadopago';
    }

    public function createPixCharge(Order $order, CarbonInterface $expiresAt, string $idempotencyKey): PixCharge
    {
        $order->loadMissing('user');
        [$firstName, $lastName] = array_pad(explode(' ', $order->user->name, 2), 2, '');

        $payment = $this->send(fn (PendingRequest $http) => $http
            ->withHeaders(['X-Idempotency-Key' => $idempotencyKey])
            ->post('/v1/payments', [
                'transaction_amount' => round($order->total_cents / 100, 2),
                'description' => "Pedido #{$order->id} - Renice",
                'payment_method_id' => 'pix',
                'external_reference' => "order-{$order->id}",
                // ISO 8601 with milliseconds and offset, as the API expects.
                'date_of_expiration' => $expiresAt->format('Y-m-d\TH:i:s.vP'),
                'payer' => [
                    'email' => $order->user->email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'identification' => ['type' => 'CPF', 'number' => $order->user->cpf],
                ],
            ]));

        $pix = $payment->json('point_of_interaction.transaction_data');

        if (! is_array($pix) || empty($pix['qr_code']) || empty($pix['qr_code_base64'])) {
            throw new PaymentGatewayException('Mercado Pago did not return a Pix QR code.');
        }

        return new PixCharge(
            providerPaymentId: (string) $payment->json('id'),
            status: self::mapStatus((string) $payment->json('status')),
            code: $pix['qr_code'],
            qrImage: 'data:image/png;base64,'.$pix['qr_code_base64'],
        );
    }

    public function status(string $providerPaymentId): PaymentStatus
    {
        $payment = $this->send(fn (PendingRequest $http) => $http->get('/v1/payments/'.rawurlencode($providerPaymentId)));

        return self::mapStatus((string) $payment->json('status'));
    }

    public function cancel(string $providerPaymentId): void
    {
        $this->send(fn (PendingRequest $http) => $http->put('/v1/payments/'.rawurlencode($providerPaymentId), ['status' => 'cancelled']));
    }

    /**
     * Mercado Pago payment statuses onto ours. An expired Pix is reported as
     * cancelled.
     */
    public static function mapStatus(string $status): PaymentStatus
    {
        return match ($status) {
            'approved' => PaymentStatus::Approved,
            'pending', 'in_process', 'authorized', 'in_mediation' => PaymentStatus::Pending,
            'rejected' => PaymentStatus::Rejected,
            'cancelled' => PaymentStatus::Cancelled,
            'refunded', 'charged_back' => PaymentStatus::Refunded,
            default => throw new PaymentGatewayException("Unknown Mercado Pago payment status \"{$status}\"."),
        };
    }

    /**
     * @param  callable(PendingRequest): Response  $request
     */
    private function send(callable $request): Response
    {
        $http = Http::baseUrl($this->baseUrl)
            ->withToken($this->accessToken)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(15)
            // Safe to retry: creation carries an idempotency key, reads and
            // cancellation are idempotent by nature.
            ->retry(2, 500, fn (Throwable $exception) => $exception instanceof ConnectionException, throw: false);

        try {
            return $request($http)->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new PaymentGatewayException('Mercado Pago request failed: '.$exception->getMessage(), previous: $exception);
        }
    }
}
