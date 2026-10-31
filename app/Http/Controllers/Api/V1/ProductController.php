<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ProductIndexRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Queries\CatalogQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * List products.
     *
     * The same catalog as the store: search across name and description,
     * sorting, and an "available only" filter; 12 per page.
     *
     * @unauthenticated
     */
    public function index(ProductIndexRequest $request, CatalogQuery $catalog): AnonymousResourceCollection
    {
        return ProductResource::collection(
            $catalog->paginate($request->search(), $request->sort(), $request->onlyAvailable())
        );
    }

    /**
     * Show a product.
     *
     * @unauthenticated
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->loadAvg('reviews', 'rating')->loadCount('reviews'));
    }
}
