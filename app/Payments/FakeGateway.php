<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\CarbonInterface;

/**
 * Local stand-in for the provider: real QR codes, no money. Development and
 * the demo use it (PAYMENT_GATEWAY=fake); the order page then offers a
 * "simulate payment" button that goes through the same ApplyPaymentStatus
 * path a Mercado Pago webhook takes.
 */
class FakeGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function createPixCharge(Order $order, CarbonInterface $expiresAt, string $idempotencyKey): PixCharge
    {
        $code = '00020126580014br.gov.bcb.pix0136renice-demo-'.$order->id
            .'52040000530398654'.sprintf('%02d', strlen($amount = number_format($order->total_cents / 100, 2, '.', ''))).$amount
            .'5802BR5906RENICE6014MONTES CLAROS62070503***6304DEMO';

        $svg = (new Writer(new ImageRenderer(new RendererStyle(240, 1), new SvgImageBackEnd)))->writeString($code);

        return new PixCharge(
            providerPaymentId: 'fake-'.$idempotencyKey,
            status: PaymentStatus::Pending,
            code: $code,
            qrImage: 'data:image/svg+xml;base64,'.base64_encode($svg),
        );
    }

    public function status(string $providerPaymentId): PaymentStatus
    {
        return Payment::query()
            ->where('provider', $this->name())
            ->where('provider_payment_id', $providerPaymentId)
            ->first()->status ?? PaymentStatus::Pending;
    }

    public function cancel(string $providerPaymentId): void {}
}
