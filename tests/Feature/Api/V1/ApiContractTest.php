<?php

/*
| Contract tests: every response below is validated against the committed
| OpenAPI document (docs/api/openapi.json). If the code changes a response
| shape without regenerating the document, these fail; if the document is
| regenerated, the change shows up in the PR diff.
*/

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Osteel\OpenApi\Testing\ValidatorBuilder;

uses(RefreshDatabase::class);

function assertMatchesContract(TestResponse $response, string $path, string $method): TestResponse
{
    static $validator;
    $validator ??= ValidatorBuilder::fromJsonFile(base_path('docs/api/openapi.json'))->getValidator();

    // Throws with the mismatch (missing property, wrong type...) when it fails.
    expect($validator->validate($response->baseResponse, $path, $method))->toBeTrue();

    return $response;
}

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->token = $this->customer->createToken('tests')->plainTextToken;
    $this->product = Product::factory()->inStock(10)->create(['price_cents' => 1500]);
});

it('lists and shows products', function () {
    Review::factory()->for($this->product)->create(['rating' => 4]);

    assertMatchesContract($this->getJson('/api/v1/products?sort=price_asc&available=1')->assertOk(), '/v1/products', 'get')
        ->assertJsonPath('data.0.slug', $this->product->slug)
        ->assertJsonPath('meta.per_page', 12);

    assertMatchesContract($this->getJson("/api/v1/products/{$this->product->slug}")->assertOk(), '/v1/products/{product}', 'get')
        ->assertJsonPath('data.rating', 4)
        ->assertJsonMissingPath('data.stock');
});

it('issues tokens', function () {
    assertMatchesContract($this->postJson('/api/v1/tokens', [
        'email' => $this->customer->email,
        'password' => 'password',
        'device_name' => 'Integração',
    ])->assertCreated(), '/v1/tokens', 'post');
});

it('manages the cart', function () {
    $response = $this->withToken($this->token)->postJson('/api/v1/cart/items', ['product_id' => $this->product->id, 'quantity' => 2])->assertOk();
    assertMatchesContract($response, '/v1/cart/items', 'post')->assertJsonPath('data.total_cents', 3000);

    $item = Cart::sole()->items()->sole();

    assertMatchesContract($this->withToken($this->token)->patchJson("/api/v1/cart/items/{$item->id}", ['quantity' => 3])->assertOk(), '/v1/cart/items/{item}', 'patch')
        ->assertJsonPath('data.items.0.quantity', 3);
    assertMatchesContract($this->withToken($this->token)->getJson('/api/v1/cart')->assertOk(), '/v1/cart', 'get');
    assertMatchesContract($this->withToken($this->token)->deleteJson("/api/v1/cart/items/{$item->id}")->assertOk(), '/v1/cart/items/{item}', 'delete')
        ->assertJsonPath('data.items', []);
});

it('places an order with its Pix and lists orders', function () {
    $address = Address::factory()->for($this->customer)->create();
    Cart::factory()->for($this->customer)->create()->items()->create(['product_id' => $this->product->id, 'quantity' => 2]);

    assertMatchesContract($this->withToken($this->token)->getJson('/api/v1/addresses')->assertOk(), '/v1/addresses', 'get');

    $placed = $this->withToken($this->token)->postJson('/api/v1/orders', ['address_id' => $address->id])->assertCreated();
    assertMatchesContract($placed, '/v1/orders', 'post')
        ->assertJsonPath('data.status', 'pending_payment')
        ->assertJsonPath('data.payment.method', 'pix')
        ->assertJsonPath('data.items.0.quantity', 2);

    $id = $placed->json('data.id');
    assertMatchesContract($this->withToken($this->token)->getJson("/api/v1/orders/{$id}")->assertOk(), '/v1/orders/{order}', 'get');
    assertMatchesContract($this->withToken($this->token)->getJson('/api/v1/orders')->assertOk(), '/v1/orders', 'get')
        ->assertJsonPath('data.0.items_count', 1);
});

it('documents its errors', function () {
    assertMatchesContract($this->getJson('/api/v1/cart')->assertUnauthorized(), '/v1/cart', 'get');
    assertMatchesContract($this->getJson('/api/v1/products/nope')->assertNotFound(), '/v1/products/{product}', 'get');
    assertMatchesContract($this->getJson('/api/v1/products?sort=random')->assertUnprocessable(), '/v1/products', 'get');
    assertMatchesContract(
        $this->withToken($this->token)->getJson('/api/v1/orders/'.Order::factory()->create()->id)->assertForbidden(),
        '/v1/orders/{order}',
        'get',
    );
});
