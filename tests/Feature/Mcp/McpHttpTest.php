<?php

/*
| The MCP server over its real transport (streamable HTTP at /mcp), the way
| a client such as Claude Desktop or Claude Code talks to it.
*/

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->rpc = fn (?string $token, string $method, array $params = []) => $this
        ->withHeaders(array_filter([
            'Accept' => 'application/json, text/event-stream',
            'Authorization' => $token ? "Bearer {$token}" : null,
        ]))
        ->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => $method, 'params' => $params]);
});

it('requires an API token', function () {
    ($this->rpc)(null, 'tools/list')->assertUnauthorized();
});

it('lists the tools a token may use', function () {
    $full = $this->user->createToken('agent')->plainTextToken;
    $readOnly = $this->user->createToken('agent', ['read'])->plainTextToken;

    $names = fn (string $token) => collect(($this->rpc)($token, 'tools/list')->assertOk()->json('result.tools'))->pluck('name');

    expect($names($full)->all())->toContain('search-products', 'add-to-cart', 'start-checkout');

    auth()->forgetGuards();
    expect($names($readOnly)->all())->toContain('search-products', 'list-orders')
        ->not->toContain('add-to-cart', 'set-cart-quantity', 'start-checkout');
});

it('calls a tool', function () {
    Product::factory()->create(['name' => 'Copo Anime 500ml']);
    $token = $this->user->createToken('agent')->plainTextToken;

    ($this->rpc)($token, 'tools/call', ['name' => 'search-products', 'arguments' => ['query' => 'anime']])
        ->assertOk()
        ->assertJsonPath('result.structuredContent.products.0.name', 'Copo Anime 500ml');
});
