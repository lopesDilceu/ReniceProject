<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AddToCart;
use App\Mcp\Tools\GetProduct;
use App\Mcp\Tools\ListAddresses;
use App\Mcp\Tools\ListOrders;
use App\Mcp\Tools\SearchProducts;
use App\Mcp\Tools\SetCartQuantity;
use App\Mcp\Tools\StartCheckout;
use App\Mcp\Tools\ViewCart;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Renice')]
#[Version('1.0.0')]
#[Instructions(<<<'TEXT'
Renice is a Brazilian store selling ready-to-drink cups with ice. You act for one signed-in customer (the owner of the API token) and only ever see their cart, addresses and orders.

- Prices are integer cents in BRL: 1800 is R$ 18,00.
- To buy: search-products → add-to-cart → list-addresses → start-checkout.
- You cannot place orders. start-checkout returns a link; the customer opens it and confirms in the store. Never tell the customer an order was placed before list-orders shows it.
- Payment is Pix: after confirmation, list-orders with the order_id returns the copy-and-paste code until it is paid or expires.
- Tokens with only the "read" scope cannot change the cart; the write tools are then not listed.
TEXT)]
class StoreServer extends Server
{
    protected array $tools = [
        SearchProducts::class,
        GetProduct::class,
        ViewCart::class,
        AddToCart::class,
        SetCartQuantity::class,
        ListAddresses::class,
        StartCheckout::class,
        ListOrders::class,
    ];
}
