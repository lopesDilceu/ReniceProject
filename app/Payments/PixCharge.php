<?php

namespace App\Payments;

use App\Enums\PaymentStatus;

final readonly class PixCharge
{
    /**
     * @param  string  $code  Pix "copia e cola".
     * @param  string  $qrImage  QR code image as a data URI.
     */
    public function __construct(
        public string $providerPaymentId,
        public PaymentStatus $status,
        public string $code,
        public string $qrImage,
    ) {}
}
