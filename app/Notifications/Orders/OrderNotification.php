<?php

namespace App\Notifications\Orders;

use App\Models\Order;
use App\Support\Money;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * E-mail to the customer about their order. Queued, retried with backoff,
 * and dispatched only after the transaction that changed the order commits:
 * a rolled-back checkout or status change never sends anything.
 */
abstract class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly Order $order)
    {
        $this->afterCommit();
    }

    /**
     * Seconds between attempts: SMTP hiccups usually clear in seconds,
     * provider outages in minutes.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    abstract public function subject(): string;

    /**
     * Paragraphs above the order summary.
     *
     * @return list<string>
     */
    abstract protected function intro(): array;

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing(['items', 'user']);

        $message = (new MailMessage)
            ->subject($this->subject())
            ->greeting('Olá, '.strtok($this->order->user->name, ' ').'!');

        foreach ($this->intro() as $line) {
            $message->line($line);
        }

        foreach ($this->order->items as $item) {
            $message->line("{$item->quantity} × {$item->product_name}: ".Money::format($item->subtotalCents()));
        }

        return $message
            ->line('**Total: '.Money::format($this->order->total_cents).'**')
            ->action('Ver pedido', route('orders.show', $this->order));
    }

    /**
     * A date in the store's timezone, e.g. "31/10/2026 às 14:30".
     */
    protected function localTime(DateTimeInterface $date): string
    {
        return Carbon::instance($date)->timezone(Config::string('shop.timezone'))->format('d/m/Y \à\s H:i');
    }
}
