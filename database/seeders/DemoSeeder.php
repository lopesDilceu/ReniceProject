<?php

namespace Database\Seeders;

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Inventory\RecordSupplierPurchase;
use App\Actions\Orders\ChangeOrderStatus;
use App\Actions\Orders\ExpireUnpaidOrders;
use App\Actions\Payments\ApplyPaymentStatus;
use App\Actions\Payments\CreatePixCharge;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * Demo store: the original Renice product line, two known accounts and a
 * few weeks of orders and reviews. Stock and orders go through the real
 * domain actions, so the seeded data obeys the same rules as production.
 */
class DemoSeeder extends Seeder
{
    public const PASSWORD = 'password';

    /**
     * The v1 catalog (see docs/legacy). Prices for the two products v1 left
     * at R$ 0,00 because of BUG-04 are new.
     *
     * @var list<array{name: string, description: string, price_cents: int, stock: int}>
     */
    private const PRODUCTS = [
        ['name' => 'Copo Anime 500ml gelo médio', 'description' => 'Copo de 500ml com embalagem customizada tema anime. O gelo que acompanha o copo é de tamanho médio.', 'price_cents' => 885, 'stock' => 150],
        ['name' => 'Copo 300ml gelo médio', 'description' => 'Copo de 300ml com gelo de tamanho médio.', 'price_cents' => 1800, 'stock' => 200],
        ['name' => 'Copo 300ml gelo pequeno', 'description' => 'Copo de 300ml com gelo de tamanho pequeno.', 'price_cents' => 1125, 'stock' => 200],
        ['name' => 'Copo 300ml gelo grande', 'description' => 'Copo de 300ml com gelo de tamanho grande.', 'price_cents' => 2250, 'stock' => 120],
        ['name' => 'Copo Fresh 500ml gelo médio', 'description' => 'Copo de 500ml com embalagem customizada tema refrescante. O gelo que acompanha o copo é de tamanho médio.', 'price_cents' => 1490, 'stock' => 0],
    ];

    /**
     * Steps from a new order to each demo status, through ChangeOrderStatus.
     *
     * @var array<string, list<OrderStatus>>
     */
    private const LIFECYCLE = [
        'pending_payment' => [],
        'paid' => [OrderStatus::Paid],
        'shipped' => [OrderStatus::Paid, OrderStatus::Shipped],
        'delivered' => [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered],
        'cancelled' => [OrderStatus::Cancelled],
    ];

    public function __construct(
        private readonly RecordSupplierPurchase $recordPurchase,
        private readonly PlaceOrder $placeOrder,
        private readonly ChangeOrderStatus $changeStatus,
    ) {}

    public function run(): void
    {
        // The demo customers are fictional: their order e-mails (and the
        // low-stock alerts) are rendered and dropped, not queued for Mailpit,
        // their Pix charges are local fakes, never real provider calls, and
        // weeks of history are not replayed live to the admin dashboard.
        $previous = [
            'queue.default' => config('queue.default'),
            'mail.default' => config('mail.default'),
            'payments.gateway' => config('payments.gateway'),
            'broadcasting.default' => config('broadcasting.default'),
        ];
        config(['queue.default' => 'sync', 'mail.default' => 'array', 'payments.gateway' => 'fake', 'broadcasting.default' => 'null']);

        try {
            $this->seedStore();
        } finally {
            config($previous);
        }
    }

    private function seedStore(): void
    {
        fake()->seed(2024);

        $admin = User::factory()->admin()->create([
            'name' => 'Admin Renice',
            'email' => 'admin@renice.test',
            'password' => self::PASSWORD,
        ]);

        $customer = User::factory()->create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@renice.test',
            'password' => self::PASSWORD,
        ]);

        $customers = User::factory(8)->create()->prepend($customer);

        foreach ($customers as $user) {
            Address::factory()->for($user)->create(['city' => 'Montes Claros', 'state' => 'MG']);
            $user->phones()->create(['number' => fake()->numerify('389########')]);
        }

        $products = collect(self::PRODUCTS)->map(function (array $data) use ($admin) {
            $product = Product::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price_cents' => $data['price_cents'],
            ]);

            if ($data['stock'] > 0) {
                $this->recordPurchase->handle(
                    $product,
                    supplier: 'Gelo Norte Distribuidora',
                    quantity: $data['stock'],
                    unitCostCents: intdiv($data['price_cents'], 2),
                    purchasedAt: now()->subWeeks(6),
                    actor: $admin,
                );
            }

            return $product;
        });

        $inStock = $products->filter(fn (Product $product) => $product->stock > 0)->values();

        foreach ($customers as $user) {
            foreach (range(1, fake()->numberBetween(1, 3)) as $ignored) {
                $this->placeDemoOrder($user, $inStock->random(fake()->numberBetween(1, 3)), $admin);
            }
        }
    }

    /**
     * @param  iterable<Product>  $products
     */
    private function placeDemoOrder(User $user, iterable $products, User $admin): void
    {
        $cart = $user->cart()->firstOrCreate();

        foreach ($products as $product) {
            $cart->items()->create(['product_id' => $product->getKey(), 'quantity' => fake()->numberBetween(1, 4)]);
        }

        $status = fake()->randomElement([
            OrderStatus::Delivered, OrderStatus::Delivered, OrderStatus::Delivered,
            OrderStatus::Shipped, OrderStatus::Paid, OrderStatus::PendingPayment, OrderStatus::Cancelled,
        ]);

        // The clock is moved back so the order and every step of its timeline
        // (and the stock movements they cause) get realistic dates.
        // Orders still waiting for payment are recent, inside the payment window.
        $at = $status === OrderStatus::PendingPayment
            ? now()->subMinutes(fake()->numberBetween(1, 15))
            : now()->subDays(fake()->numberBetween(5, 40))->setTime(fake()->numberBetween(8, 21), fake()->numberBetween(0, 59));

        try {
            Date::setTestNow($at);
            $order = $this->placeOrder->handle($user, $user->addresses()->firstOrFail());
            $payment = app(CreatePixCharge::class)->handle($order);

            foreach (self::LIFECYCLE[$status->value] as $next) {
                match ($next) {
                    // Paid within the window, the way a provider webhook confirms it.
                    OrderStatus::Paid => $this->at($at = $at->copy()->addMinutes(fake()->numberBetween(2, 50)),
                        fn () => app(ApplyPaymentStatus::class)->handle($payment, PaymentStatus::Approved)),
                    // Never paid: expired by the scheduler at the deadline.
                    OrderStatus::Cancelled => $this->at($at = $order->payment_due_at ?? $at,
                        fn () => app(ExpireUnpaidOrders::class)->handle()),
                    default => $this->at($at = $at->copy()->addHours(fake()->numberBetween(2, 30)),
                        fn () => $this->changeStatus->handle($order, $next, $admin)),
                };
            }
        } finally {
            Date::setTestNow();
        }

        if ($status === OrderStatus::Delivered) {
            $this->review($user, $order);
        }
    }

    private function at(CarbonInterface $time, callable $step): void
    {
        Date::setTestNow($time);
        $step();
    }

    private function review(User $user, Order $order): void
    {
        foreach ($order->items as $item) {
            if (fake()->boolean(60) && ! $user->reviews()->where('product_id', $item->product_id)->exists()) {
                Review::factory()->for($user)->create([
                    'product_id' => $item->product_id,
                    'rating' => fake()->randomElement([3, 4, 4, 5, 5, 5]),
                    'comment' => fake()->randomElement([
                        'Ótimo produto!', 'Gelo chegou inteiro, recomendo.', 'Muito prático para o dia a dia.',
                        'Bom, mas poderia ser maior.', null,
                    ]),
                ]);
            }
        }
    }
}
