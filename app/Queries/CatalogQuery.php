<?php

namespace App\Queries;

use App\Enums\CatalogSort;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * The storefront listing: search, availability filter and sorting, with the
 * rating aggregates loaded in the same query (v1 loaded every product and
 * every stock row, then matched them in nested Blade loops).
 */
class CatalogQuery
{
    public const PER_PAGE = 12;

    /**
     * @param  int|null  $page  Defaults to the request's ?page=; the MCP tools pass it explicitly.
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(?string $search, CatalogSort $sort, bool $onlyAvailable, ?int $page = null): LengthAwarePaginator
    {
        return Product::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($search, fn (Builder $query, string $search) => $query->where(
                fn (Builder $query) => $query
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('description', "%{$search}%")
            ))
            ->when($onlyAvailable, fn (Builder $query) => $query->where('stock', '>', 0))
            ->tap(fn (Builder $query) => $this->sort($query, $sort))
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page)
            ->withQueryString();
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function sort(Builder $query, CatalogSort $sort): void
    {
        match ($sort) {
            // Available first, then best rated, then alphabetical.
            CatalogSort::Featured => $query
                ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
                ->orderByDesc($this->averageRating())
                ->orderBy('name'),
            CatalogSort::PriceAsc => $query->orderBy('price_cents'),
            CatalogSort::PriceDesc => $query->orderByDesc('price_cents'),
            CatalogSort::Rating => $query->orderByDesc($this->averageRating())->orderByDesc('reviews_count'),
            CatalogSort::Newest => $query->orderByDesc('created_at'),
        };
    }

    /**
     * Unrated products count as 0. Ordering by the withAvg alias would put
     * NULLs first on PostgreSQL (DESC sorts NULLS FIRST) but last on SQLite.
     *
     * @return Builder<Review>
     */
    private function averageRating(): Builder
    {
        return Review::query()
            ->selectRaw('COALESCE(AVG(rating), 0)')
            ->whereColumn('reviews.product_id', 'products.id');
    }
}
