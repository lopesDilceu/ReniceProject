<?php

namespace App\Mcp\Tools;

use App\Actions\Cart\AddToCart as AddToCartAction;
use App\Exceptions\InsufficientStock;
use App\Mcp\Tools\Concerns\ActsForCustomer;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Add a product to the cart. If it is already there, the quantity is increased. Checked against stock.')]
class AddToCart extends WriteTool
{
    use ActsForCustomer;

    public function handle(Request $request, AddToCartAction $addToCart): Response|ResponseFactory
    {
        $input = $request->validate([
            'slug' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::query()->where('slug', $input['slug'])->first();

        if ($product === null) {
            return Response::error("No product with slug \"{$input['slug']}\".");
        }

        try {
            $addToCart->handle($this->customer($request), $product, $input['quantity']);
        } catch (InsufficientStock $exception) {
            return Response::error($exception->getMessage());
        }

        return Response::structured(['cart' => ViewCart::cart($this->customer($request))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The product slug, from search-products.')->required(),
            'quantity' => $schema->integer()->min(1)->max(99)->required(),
        ];
    }
}
