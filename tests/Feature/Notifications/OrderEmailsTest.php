<?php

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Orders\ChangeOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\Orders\OrderCancelled;
use App\Notifications\Orders\OrderPaid;
use App\Notifications\Orders\OrderPlaced;
use App\Notifications\Orders\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create(['name' => 'Joana Lima', 'email' => 'joana@example.com']);
    $this->admin = User::factory()->admin()->create();
    $this->product = Product::factory()->inStock(20)->create(['name' => 'Copo 300ml gelo médio', 'price_cents' => 1800]);

    $this->placeOrder = function (): Order {
        Cart::factory()->for($this->customer)->create()->items()->create(['product_id' => $this->product->id, 'quantity' => 2]);

        return app(PlaceOrder::class)->handle($this->customer, Address::factory()->for($this->customer)->create());
    };
    $this->change = fn (Order $order, OrderStatus $next, ?string $reason = null) => app(ChangeOrderStatus::class)->handle($order, $next, $this->admin, $reason);
});

/**
 * E-mails that actually went out through the array mailer (phpunit.xml),
 * after the sync queue ran the notifications.
 *
 * @return Collection<int, Email>
 */
function sentEmails(): Collection
{
    return Mail::mailer('array')->getSymfonyTransport()->messages()
        ->map(fn (SentMessage $message) => $message->getOriginalMessage());
}

it('e-mails the customer at each step they care about', function () {
    Notification::fake();

    $order = ($this->placeOrder)();
    ($this->change)($order, OrderStatus::Paid);
    ($this->change)($order, OrderStatus::Shipped);
    ($this->change)($order, OrderStatus::Delivered);

    Notification::assertSentTo($this->customer, OrderPlaced::class);
    Notification::assertSentTo($this->customer, OrderPaid::class);
    Notification::assertSentTo($this->customer, OrderShipped::class);
    Notification::assertSentTimes(OrderPlaced::class, 1);
    Notification::assertCount(3);
});

it('tells the customer why the order was cancelled', function () {
    Notification::fake();

    $order = ($this->placeOrder)();
    ($this->change)($order, OrderStatus::Cancelled, 'Pagamento não identificado.');

    Notification::assertSentTo($this->customer, OrderCancelled::class, fn (OrderCancelled $mail) => $mail->reason === 'Pagamento não identificado.');
});

it('sends nothing when the change is rolled back', function () {
    $order = ($this->placeOrder)();

    try {
        DB::transaction(function () use ($order) {
            ($this->change)($order, OrderStatus::Paid);

            throw new RuntimeException('payment provider failed');
        });
    } catch (RuntimeException) {
    }

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and(sentEmails()->map(fn (Email $email) => $email->getSubject())->all())->toBe(["Recebemos seu pedido #{$order->id}"]);
});

it('writes the confirmation in Portuguese, in the store\'s timezone', function () {
    $this->travelTo('2026-10-31 15:00:00 UTC');
    config(['shop.payment_window_minutes' => 30]);

    $order = ($this->placeOrder)();
    $email = sentEmails()->sole();
    $html = $email->getHtmlBody();

    expect($email->getSubject())->toBe("Recebemos seu pedido #{$order->id}")
        ->and($email->getTo()[0]->getAddress())->toBe('joana@example.com')
        ->and($html)->toContain('Olá, Joana!')
        ->toContain('2 × Copo 300ml gelo médio: R$ 36,00')
        ->toContain('Total: R$ 36,00')
        // 15:30 UTC is 12:30 in São Paulo.
        ->toContain('reservados até 31/10/2026 às 12:30')
        ->toContain(route('orders.show', $order));
});

it('queues every order e-mail after commit, with retries', function (string $class) {
    $order = Order::factory()->create();
    $notification = $class === OrderCancelled::class ? new $class($order, null) : new $class($order);

    expect($notification)->toBeInstanceOf(ShouldQueue::class)
        ->and($notification->afterCommit)->toBeTrue()
        ->and($notification->tries)->toBe(5)
        ->and($notification->backoff())->toBe([10, 60, 300]);
})->with([OrderPlaced::class, OrderPaid::class, OrderShipped::class, OrderCancelled::class]);

it('does not e-mail deleted accounts', function () {
    $order = ($this->placeOrder)();
    Notification::fake();
    $this->customer->delete();

    ($this->change)($order->fresh(), OrderStatus::Paid);

    Notification::assertNothingSent();
});
