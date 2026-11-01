<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment window
    |--------------------------------------------------------------------------
    |
    | How long a new order keeps its stock reserved while waiting for payment.
    | After that, orders:expire-unpaid cancels it and releases the stock. Pix
    | charges are created with the same expiry; Mercado Pago requires at
    | least 30 minutes.
    |
    */

    'payment_window_minutes' => (int) env('SHOP_PAYMENT_WINDOW_MINUTES', 60),

    /*
    |--------------------------------------------------------------------------
    | Display timezone
    |--------------------------------------------------------------------------
    |
    | Dates are stored in UTC. Text the server writes for people, such as
    | e-mails, shows them in the store's timezone.
    |
    */

    'timezone' => env('SHOP_TIMEZONE', 'America/Sao_Paulo'),

    /*
    |--------------------------------------------------------------------------
    | Low stock
    |--------------------------------------------------------------------------
    |
    | Admins are e-mailed when a product's stock drops to this level, and the
    | dashboard lists products at or below it.
    |
    */

    'low_stock_threshold' => (int) env('SHOP_LOW_STOCK_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Public demo
    |--------------------------------------------------------------------------
    |
    | On the public demo the store says so on every page, shows the demo
    | accounts on the login page and resets itself (fresh database and seed)
    | every night, so whatever visitors change does not last.
    |
    */

    'demo' => (bool) env('DEMO_MODE', false),

    'demo_reset_at' => env('DEMO_RESET_AT', '04:00'),

];
