<?php

namespace App\Mcp\Tools;

use App\Enums\CatalogSort;
use App\Http\Resources\Api\V1\ProductResource;
use App\Queries\CatalogQuery;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('Search the Renice catalog (cups with ice) by name or description. Returns up to 12 products per page with price in cents (BRL), availability and rating.')]
class SearchProducts extends Tool
{
    public function handle(Request $request, CatalogQuery $catalog): ResponseFactory
    {
        $input = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::enum(CatalogSort::class)],
            'available_only' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $products = $catalog->paginate(
            trim((string) ($input['query'] ?? '')) ?: null,
            CatalogSort::tryFrom((string) ($input['sort'] ?? '')) ?? CatalogSort::Featured,
            (bool) ($input['available_only'] ?? false),
            page: (int) ($input['page'] ?? 1),
        );

        return Response::structured([
            'products' => ProductResource::collection($products->items())->resolve(request()),
            'page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Words to look for, e.g. "anime" or "gelo grande".'),
            'sort' => $schema->string()->enum(array_column(CatalogSort::cases(), 'value'))->description('Defaults to featured.'),
            'available_only' => $schema->boolean()->description('Only products in stock.'),
            'page' => $schema->integer()->min(1),
        ];
    }
}
