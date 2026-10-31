<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\CatalogSort;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\ReviewSummary;
use App\Queries\CatalogQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request, CatalogQuery $catalog): Response
    {
        $products = $catalog->paginate($request->search(), $request->sort(), $request->onlyAvailable());

        return Inertia::render('products/index', [
            'products' => ProductResource::collection($products),
            'filters' => [
                'q' => $request->search(),
                'sort' => $request->sort()->value,
                'available' => $request->onlyAvailable(),
            ],
            'sortOptions' => collect(CatalogSort::cases())
                ->map(fn (CatalogSort $sort) => ['value' => $sort->value, 'label' => $sort->label()]),
        ]);
    }

    public function show(Request $request, Product $product): Response
    {
        $product->loadAvg('reviews', 'rating')->loadCount('reviews');

        $distribution = $product->reviews()
            ->selectRaw('rating, COUNT(*) AS total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        // resolve() returns plain arrays; a resource passed as-is is serialized
        // as an HTTP response and wrapped in "data".
        return Inertia::render('products/show', [
            'product' => (new ProductResource($product))->resolve($request),
            'ratingDistribution' => collect(range(5, 1))
                ->map(fn (int $stars) => ['stars' => $stars, 'count' => (int) ($distribution[$stars] ?? 0)]),
            'reviews' => ReviewResource::collection(
                $product->reviews()->with('user:id,name')->latest()->limit(20)->get()
            )->resolve($request),
            // Read from the database only: the page never waits on an AI provider.
            'reviewSummary' => ReviewSummary::query()->whereKey($product->getKey())->first()?->only(['summary', 'pros', 'cons', 'reviews_count', 'updated_at']),
        ]);
    }
}
