<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment window
    |--------------------------------------------------------------------------
    |
    | How long a new order keeps its stock reserved while waiting for payment.
    | After that, orders:expire-unpaid cancels it and releases the stock. Pix
    | charges are created with the same expiry (#33).
    |
    */

    'payment_window_minutes' => (int) env('SHOP_PAYMENT_WINDOW_MINUTES', 30),

];
