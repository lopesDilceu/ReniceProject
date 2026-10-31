<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment gateway
    |--------------------------------------------------------------------------
    |
    | "mercadopago" charges real Pix through Mercado Pago (credentials in
    | config/services.php; use test credentials for the sandbox). "fake"
    | generates demo QR codes locally and lets you simulate the payment from
    | the order page; it is the default, so the store runs without accounts.
    |
    */

    'gateway' => env('PAYMENT_GATEWAY', 'fake'),

];
