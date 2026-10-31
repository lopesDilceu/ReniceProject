<?php

/*
| Real concurrency: separate PHP processes, each with its own database
| connection, check out the last unit at the same moment. The processes must
| see the test data, so this file commits it (instead of wrapping each test
| in a transaction) and truncates afterwards. Runs in the PostgreSQL CI job.
*/

use App\Actions\Inventory\RecordInventoryMovement;
use App\Enums\InventoryMovementType;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

uses(DatabaseTruncation::class);

beforeEach(function () {
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Needs a database shared between processes (PostgreSQL).');
    }
});

// The rest of the suite rolls back transactions on a clean database.
afterEach(fn () => DB::getDriverName() === 'pgsql' && $this->truncateTablesForAllConnections());

it('sells the last unit exactly once to concurrent checkouts', function () {
    $product = Product::factory()->create(['stock' => 0]);
    app(RecordInventoryMovement::class)->handle($product, InventoryMovementType::Purchase, 1);

    $customers = User::factory(8)->create()->each(function (User $customer) use ($product) {
        Address::factory()->for($customer)->create();
        Cart::factory()->for($customer)->create()->items()->create(['product_id' => $product->id, 'quantity' => 1]);
    });

    // Every process waits for the same instant, after all of them have booted.
    $startAt = (string) (microtime(true) + 3);

    $results = Process::pool(function (Pool $pool) use ($customers, $startAt) {
        foreach ($customers as $customer) {
            $pool->path(base_path())->timeout(30)
                ->command([PHP_BINARY, 'tests/Fixtures/place-order.php', (string) $customer->id, $startAt]);
        }
    })->start()->wait();

    $outcomes = $results->collect()->map(fn ($result) => trim($result->output()) ?: trim($result->errorOutput()));

    expect($outcomes->countBy()->all())->toEqual(['placed' => 1, 'out-of-stock' => 7])
        ->and($product->fresh()->stock)->toBe(0)
        ->and(Order::count())->toBe(1)
        ->and($product->inventoryMovements()->where('type', InventoryMovementType::Reservation)->count())->toBe(1)
        ->and((int) $product->inventoryMovements()->sum('quantity'))->toBe(0);
})->group('pgsql');
