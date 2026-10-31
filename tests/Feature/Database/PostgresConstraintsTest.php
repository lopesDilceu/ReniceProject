<?php

/*
| CHECK constraints exist only on PostgreSQL (see the add_check_constraints
| migration). These tests run in the PostgreSQL CI job and are skipped on
| the in-memory SQLite run.
*/

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderStatusChange;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('CHECK constraints are PostgreSQL-only.');
    }
});

it('rejects invalid values at the database level', function (Closure $write) {
    $write();
})->with([
    'rating above 5' => fn () => Review::factory()->create(['rating' => 6]),
    'rating below 1' => fn () => Review::factory()->create(['rating' => 0]),
    'negative price' => fn () => Product::factory()->create(['price_cents' => -1]),
    'negative stock' => fn () => Product::factory()->create(['stock' => -1]),
    'zero quantity in cart' => fn () => CartItem::factory()->create(['quantity' => 0]),
    'CPF with letters' => fn () => User::factory()->create(['cpf' => '1234567890A']),
    'lowercase state' => fn () => Address::factory()->create(['state' => 'mg']),
    'status change to the same status' => fn () => OrderStatusChange::record(Order::factory()->create(), OrderStatus::PendingPayment, null),
])->throws(QueryException::class)->group('pgsql');
