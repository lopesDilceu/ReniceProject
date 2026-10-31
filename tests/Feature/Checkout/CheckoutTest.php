<?php

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->address = Address::factory()->for($this->customer)->create();
    $this->product = Product::factory()->inStock(10)->create(['price_cents' => 1500]);
    $this->cart = Cart::factory()->for($this->customer)->create();
    $this->cart->items()->create(['product_id' => $this->product->id, 'quantity' => 2]);
});

it('requires a confirmed e-mail', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('checkout.show'))
        ->assertRedirect(route('verification.notice'));
});

it('sends customers with an empty cart back to the cart', function () {
    $this->cart->items()->delete();

    $this->actingAs($this->customer)->get(route('checkout.show'))->assertRedirect(route('cart.show'));
});

it('shows the items and only the customer\'s own addresses', function () {
    Address::factory()->create();

    $this->actingAs($this->customer)->get(route('checkout.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('checkout/show')
            ->has('items', 1)
            ->where('total_cents', 3000)
            ->has('addresses', 1)
            ->where('addresses.0.id', $this->address->id));
});

it('places the order and shows it', function () {
    $response = $this->actingAs($this->customer)->post(route('checkout.store'), ['address_id' => $this->address->id]);

    $order = Order::sole();

    $response->assertRedirect(route('orders.show', $order));
    expect($order)
        ->status->toBe(OrderStatus::PendingPayment)
        ->total_cents->toBe(3000)
        ->and($this->product->fresh()->stock)->toBe(8)
        ->and($this->cart->items()->count())->toBe(0);

    $this->actingAs($this->customer)->get(route('orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/show')
            ->where('order.status_label', 'Aguardando pagamento')
            ->has('order.items', 1));
});

it('refuses another customer\'s address', function () {
    $this->actingAs($this->customer)->post(route('checkout.store'), ['address_id' => Address::factory()->create()->id])
        ->assertSessionHasErrors('address_id');

    expect(Order::count())->toBe(0);
});

it('reports stock that ran out after the item was added', function () {
    $this->product->forceFill(['stock' => 1])->save();

    $this->actingAs($this->customer)->post(route('checkout.store'), ['address_id' => $this->address->id])
        ->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0);
});

describe('SEC-02 the cart comes from the session', function () {
    it('no longer has the v1 route that put the cart id into SQL', function () {
        $this->actingAs($this->customer)->get('/usuarios/finalizar/1 OR 1=1')->assertNotFound();
    })->issue(18);

    it('ignores a cart id sent in the request', function () {
        $other = Cart::factory()->create();
        $other->items()->create(['product_id' => $this->product->id, 'quantity' => 5]);

        $this->actingAs($this->customer)->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'cart_id' => $other->id,
        ]);

        expect(Order::sole()->user_id)->toBe($this->customer->id)
            ->and($other->items()->sole()->quantity)->toBe(5);
    })->issue(18);
});

describe('SEC-04 orders are placed only by POST, once', function () {
    it('never places an order on GET', function () {
        $this->actingAs($this->customer)->get(route('checkout.show'))->assertOk();

        expect(Order::count())->toBe(0);
    })->issue(20);

    it('creates a single order on double submit', function () {
        $this->actingAs($this->customer)->post(route('checkout.store'), ['address_id' => $this->address->id]);
        $this->actingAs($this->customer)->post(route('checkout.store'), ['address_id' => $this->address->id])
            ->assertRedirect(route('cart.show'));

        expect(Order::count())->toBe(1);
    })->issue(20);
});

describe('order page', function () {
    beforeEach(fn () => $this->order = Order::factory()->for($this->customer)->create());

    it('is visible to its owner and to admins', function (Closure $viewer) {
        $this->actingAs($viewer($this))->get(route('orders.show', $this->order))->assertOk();
    })->with([
        'owner' => fn ($test) => $test->customer,
        'admin' => fn () => User::factory()->admin()->create(),
    ]);

    it('is hidden from other customers', function () {
        $this->actingAs(User::factory()->create())->get(route('orders.show', $this->order))->assertForbidden();
    });
});
