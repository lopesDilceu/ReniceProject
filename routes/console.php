<?php

use App\Actions\Orders\ExpireUnpaidOrders;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('orders:expire-unpaid', function (ExpireUnpaidOrders $expire) {
    $this->info("Pedidos expirados: {$expire->handle()}.");
})->purpose('Cancel orders past their payment window and release their stock');

Schedule::command('orders:expire-unpaid')->everyMinute()->withoutOverlapping();
