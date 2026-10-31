<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
| Private channels. Every subscription goes through /broadcasting/auth with
| the session, and these callbacks decide who may listen.
*/

// The admin dashboard: new orders, confirmed payments, low stock.
Broadcast::channel('admin', fn (User $user): bool => $user->is_admin);

// One order: its customer (the order page) and admins.
Broadcast::channel('orders.{order}', fn (User $user, Order $order): bool => $order->user_id === $user->getKey() || $user->is_admin);
