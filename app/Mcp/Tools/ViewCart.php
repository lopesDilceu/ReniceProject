<?php

namespace App\Mcp\Tools;

use App\Http\Resources\Api\V1\CartResource;
use App\Mcp\Tools\Concerns\ActsForCustomer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description("Show the signed-in customer's cart: items, quantities and total in cents (BRL).")]
class ViewCart extends Tool
{
    use ActsForCustomer;

    public function handle(Request $request): ResponseFactory
    {
        return Response::structured(['cart' => self::cart($this->customer($request))]);
    }

    /**
     * The cart as the REST API shows it, shared by the cart tools.
     *
     * @return array<string, mixed>
     */
    public static function cart(?User $customer): array
    {
        $cart = $customer?->cart()->with(['items' => fn ($query) => $query->with('product')->orderBy('id')])->first();

        return (new CartResource($cart))->resolve(request());
    }
}
