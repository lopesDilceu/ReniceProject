<?php

namespace App\Mcp\Tools;

use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('Get one product by its slug, and check whether a quantity can be ordered. Exact stock is not disclosed: `max_quantity` is the most a customer can add (capped at 99).')]
class GetProduct extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $input = $request->validate([
            'slug' => ['required', 'string'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::query()->where('slug', $input['slug'])->withAvg('reviews', 'rating')->withCount('reviews')->first();

        if ($product === null) {
            return Response::error("No product with slug \"{$input['slug']}\". Use search-products to find one.");
        }

        $maxQuantity = min($product->stock, 99);

        return Response::structured([
            'product' => (new ProductResource($product))->resolve(request()),
            'max_quantity' => $maxQuantity,
            'can_order_quantity' => isset($input['quantity']) ? $input['quantity'] <= $maxQuantity : $maxQuantity > 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The product slug, from search-products.')->required(),
            'quantity' => $schema->integer()->min(1)->description('Check whether this many units can be ordered.'),
        ];
    }
}
