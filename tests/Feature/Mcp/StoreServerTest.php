<?php

use App\Mcp\Servers\StoreServer;
use App\Mcp\Tools\AddToCart;
use App\Mcp\Tools\GetProduct;
use App\Mcp\Tools\ListAddresses;
use App\Mcp\Tools\ListOrders;
use App\Mcp\Tools\SearchProducts;
use App\Mcp\Tools\SetCartQuantity;
use App\Mcp\Tools\StartCheckout;
use App\Mcp\Tools\ViewCart;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The customer as an MCP client authenticates: with an API token, here
 * carrying the given scopes.
 *
 * @param  list<string>  $abilities
 */
function agentFor(User $user, array $abilities = ['read', 'write']): User
{
    // A separate instance: withAccessToken() sets the token on the object.
    $agent = User::findOrFail($user->id);

    return $agent->withAccessToken($agent->createToken('agent', $abilities)->accessToken);
}

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->agent = agentFor($this->customer);
    $this->product = Product::factory()->inStock(10)->create(['name' => 'Copo Anime 500ml', 'slug' => 'copo-anime-500ml', 'price_cents' => 1800]);
});

describe('catalog', function () {
    it('searches products', function () {
        Product::factory()->create(['name' => 'Copo Fresh 500ml']);

        StoreServer::actingAs($this->agent)->tool(SearchProducts::class, ['query' => 'anime'])
            ->assertOk()
            ->assertStructuredContent(fn ($json) => $json
                ->where('total', 1)
                ->where('products.0.slug', 'copo-anime-500ml')
                ->where('products.0.price_cents', 1800)
                ->etc());
    });

    it('tells whether a quantity can be ordered without disclosing stock', function () {
        StoreServer::actingAs($this->agent)->tool(GetProduct::class, ['slug' => 'copo-anime-500ml', 'quantity' => 11])
            ->assertOk()
            ->assertStructuredContent(fn ($json) => $json
                ->where('max_quantity', 10)
                ->where('can_order_quantity', false)
                ->missing('product.stock')
                ->etc());

        StoreServer::actingAs($this->agent)->tool(GetProduct::class, ['slug' => 'nope'])
            ->assertHasErrors(['No product with slug "nope"']);
    });
});

describe('cart', function () {
    it('adds products and changes quantities in the customer\'s own cart', function () {
        StoreServer::actingAs($this->agent)->tool(AddToCart::class, ['slug' => 'copo-anime-500ml', 'quantity' => 2])
            ->assertOk()
            ->assertStructuredContent(fn ($json) => $json->where('cart.total_cents', 3600)->etc());

        StoreServer::actingAs($this->agent)->tool(SetCartQuantity::class, ['slug' => 'copo-anime-500ml', 'quantity' => 3])->assertOk();
        expect($this->customer->cart->items()->sole()->quantity)->toBe(3);

        StoreServer::actingAs($this->agent)->tool(SetCartQuantity::class, ['slug' => 'copo-anime-500ml', 'quantity' => 0])->assertOk();
        expect($this->customer->cart->items()->count())->toBe(0);
    });

    it('refuses more than the stock', function () {
        StoreServer::actingAs($this->agent)->tool(AddToCart::class, ['slug' => 'copo-anime-500ml', 'quantity' => 11])
            ->assertHasErrors();

        expect(Cart::count() === 0 || $this->customer->cart?->items()->count() === 0)->toBeTrue();
    });

    it('never touches another customer\'s cart', function () {
        $stranger = User::factory()->create();
        Cart::factory()->for($stranger)->create()->items()->create(['product_id' => $this->product->id, 'quantity' => 1]);

        StoreServer::actingAs($this->agent)->tool(SetCartQuantity::class, ['slug' => 'copo-anime-500ml', 'quantity' => 5])
            ->assertHasErrors(['is not in the cart']);
        StoreServer::actingAs($this->agent)->tool(ViewCart::class)
            ->assertStructuredContent(fn ($json) => $json->where('cart.items', [])->etc());

        expect($stranger->cart->items()->sole()->quantity)->toBe(1);
    });
});

describe('scopes', function () {
    it('hides the write tools from read-only tokens', function () {
        StoreServer::actingAs(agentFor($this->customer, ['read']))->tools()
            ->assertRegistered([SearchProducts::class, GetProduct::class, ViewCart::class, ListAddresses::class, ListOrders::class])
            ->assertNotRegistered([AddToCart::class, SetCartQuantity::class, StartCheckout::class]);

        StoreServer::actingAs($this->agent)->tools()
            ->assertRegistered([AddToCart::class, SetCartQuantity::class, StartCheckout::class]);
    });

    it('refuses to run a write tool with a read-only token', function () {
        StoreServer::actingAs(agentFor($this->customer, ['read']))->tool(AddToCart::class, ['slug' => 'copo-anime-500ml', 'quantity' => 1])
            ->assertNotRegistered();

        expect(Cart::count())->toBe(0);
    });
});

describe('checkout', function () {
    beforeEach(function () {
        $this->address = Address::factory()->for($this->customer)->create();
        Cart::factory()->for($this->customer)->create()->items()->create(['product_id' => $this->product->id, 'quantity' => 2]);

        $this->start = fn (?int $addressId = null) => StoreServer::actingAs($this->agent)->tool(StartCheckout::class, ['address_id' => $addressId ?? $this->address->id]);
    });

    it('prepares the order without placing it', function () {
        ($this->start)()
            ->assertOk()
            ->assertStructuredContent(fn ($json) => $json
                ->where('status', 'awaiting_customer_confirmation')
                ->where('cart.total_cents', 3600)
                ->has('confirmation_url')
                ->etc());

        expect(Order::count())->toBe(0)
            ->and($this->product->fresh()->stock)->toBe(10);
    });

    it('only ships to the customer\'s own addresses', function () {
        ($this->start)(Address::factory()->create()->id)->assertHasErrors(['not one of the customer\'s']);
    });

    it('needs a confirmed e-mail and a non-empty cart', function () {
        $this->customer->cart->items()->delete();
        ($this->start)()->assertHasErrors(['The cart is empty.']);

        // The agent acts on its own instance of the customer.
        $this->agent->forceFill(['email_verified_at' => null])->save();
        ($this->start)()->assertHasErrors(['confirm their e-mail']);
    });
});

describe('orders', function () {
    it('lists only the customer\'s orders', function () {
        $mine = Order::factory()->for($this->customer)->create();
        $theirs = Order::factory()->create();

        StoreServer::actingAs($this->agent)->tool(ListOrders::class)
            ->assertStructuredContent(fn ($json) => $json->has('orders', 1)->where('orders.0.id', $mine->id)->etc());

        StoreServer::actingAs($this->agent)->tool(ListOrders::class, ['order_id' => $theirs->id])
            ->assertHasErrors(["Order #{$theirs->id} not found."]);
    });
});
