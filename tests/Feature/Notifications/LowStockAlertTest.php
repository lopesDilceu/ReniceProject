<?php

use App\Actions\Inventory\RecordInventoryMovement;
use App\Enums\InventoryMovementType;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['shop.low_stock_threshold' => 5]);
    Notification::fake();

    $this->admins = User::factory(2)->admin()->create();
    $this->customer = User::factory()->create();
    $this->product = Product::factory()->create(['name' => 'Copo Anime 500ml', 'stock' => 0]);
    $this->move = fn (int $quantity) => app(RecordInventoryMovement::class)->handle($this->product, InventoryMovementType::Adjustment, $quantity);

    ($this->move)(8);
});

it('alerts every admin, and only admins, when stock reaches the threshold', function () {
    ($this->move)(-3);

    Notification::assertSentTo($this->admins, LowStockAlert::class, fn (LowStockAlert $alert) => $alert->stock === 5 && $alert->product->is($this->product));
    Notification::assertNotSentTo($this->customer, LowStockAlert::class);
});

it('alerts once per crossing, and again after a restock', function () {
    ($this->move)(-1);
    ($this->move)(-3); // 8 → 7 → 4: crossed
    ($this->move)(-2); // 4 → 2: already low
    Notification::assertSentTimes(LowStockAlert::class, 2);

    ($this->move)(10); // 12: armed again
    ($this->move)(-12); // 0: crossed

    Notification::assertSentTimes(LowStockAlert::class, 4);
});

it('says when the product sold out and links to its stock page', function () {
    ($this->move)(-8);

    $mail = (new LowStockAlert($this->product, 0))->toMail($this->admins[0]);

    expect($mail->subject)->toBe('Estoque baixo: Copo Anime 500ml')
        ->and($mail->introLines[0])->toBe('**Copo Anime 500ml** esgotou.')
        ->and($mail->actionUrl)->toBe(route('admin.inventory.show', $this->product))
        ->and(new LowStockAlert($this->product, 0))->toBeInstanceOf(ShouldQueue::class);
});

it('uses the same threshold on the dashboard', function () {
    config(['shop.low_stock_threshold' => 8]);

    $this->actingAs($this->admins[0])->get(route('admin.home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('lowStockThreshold', 8)
            ->where('lowStock.0.name', 'Copo Anime 500ml'));
});
