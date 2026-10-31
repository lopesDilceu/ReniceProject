<?php

/*
| Child process for ConcurrentCheckoutTest: boots the application with the
| environment inherited from the test run, waits for the shared start time
| so every process hits the database together, then checks out the given
| customer's cart. Prints "placed" or "out-of-stock".
|
| Usage: php tests/Fixtures/place-order.php <user id> <unix start time>
*/

use App\Actions\Checkout\PlaceOrder;
use App\Exceptions\InsufficientStock;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$customer = User::findOrFail((int) $argv[1]);
$address = $customer->addresses()->firstOrFail();

while (microtime(true) < (float) $argv[2]) {
    usleep(200);
}

try {
    app(PlaceOrder::class)->handle($customer, $address);
    echo 'placed';
} catch (InsufficientStock) {
    echo 'out-of-stock';
}
