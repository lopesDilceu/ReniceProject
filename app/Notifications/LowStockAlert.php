<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells admins a product just reached the low-stock threshold. Sent once per
 * crossing (see RecordInventoryMovement), after the movement commits.
 */
class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public readonly Product $product,
        public readonly int $stock,
    ) {
        $this->afterCommit();
    }

    /**
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

    public function toMail(object $notifiable): MailMessage
    {
        $units = $this->stock === 1 ? 'unidade' : 'unidades';

        return (new MailMessage)
            ->subject("Estoque baixo: {$this->product->name}")
            ->line($this->stock === 0
                ? "**{$this->product->name}** esgotou."
                : "**{$this->product->name}** está com {$this->stock} {$units} em estoque.")
            ->line('Registre uma compra de fornecedor para repor.')
            ->action('Ver estoque', route('admin.inventory.show', $this->product));
    }
}
