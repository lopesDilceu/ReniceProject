<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ActsForCustomer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
#[Description('Set the quantity of a product already in the cart. Quantity 0 removes it.')]
class SetCartQuantity extends WriteTool
{
    use ActsForCustomer;

    public function handle(Request $request): Response|ResponseFactory
    {
        $input = $request->validate([
            'slug' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        // Only the signed-in customer's own cart is ever touched.
        $customer = $this->customer($request);
        $item = $customer->cart?->items()
            ->whereHas('product', fn ($query) => $query->where('slug', $input['slug']))
            ->with('product')
            ->first();

        if ($item === null) {
            return Response::error("\"{$input['slug']}\" is not in the cart.");
        }

        if ($input['quantity'] === 0) {
            $item->delete();
        } elseif ($input['quantity'] > $item->product->stock) {
            return Response::error("Only {$item->product->stock} units of \"{$item->product->name}\" are available.");
        } else {
            $item->update(['quantity' => $input['quantity']]);
        }

        return Response::structured(['cart' => ViewCart::cart($customer->fresh())]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->required(),
            'quantity' => $schema->integer()->min(0)->max(99)->description('0 removes the product.')->required(),
        ];
    }
}
