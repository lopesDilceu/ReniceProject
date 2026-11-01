<?php

use App\Actions\Orders\ExpireUnpaidOrders;
use App\Support\Health\HealthCheck;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('orders:expire-unpaid', function (ExpireUnpaidOrders $expire) {
    $this->info("Pedidos expirados: {$expire->handle()}.");
})->purpose('Cancel orders past their payment window and release their stock');

Schedule::command('orders:expire-unpaid')->everyMinute()->withoutOverlapping();

// Proves the scheduler is running: /health reports it degraded when stale.
Schedule::call(fn () => Cache::forever(HealthCheck::SCHEDULER_HEARTBEAT, now()->getTimestamp()))
    ->everyMinute()
    ->name('health:scheduler-heartbeat');
