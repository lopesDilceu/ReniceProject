<?php

namespace App\Queries;

use App\Enums\CatalogSort;
use App\Models\Product;
use App\Models\Review;
use App\Search\HybridSearch;
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

    public function __construct(
        private readonly HybridSearch $hybrid,
    ) {}

    /**
     * With semantic search on, a search matches by meaning as well as by
     * keyword (HybridSearch) and the default sort becomes relevance; without
     * it, or when the provider fails, it is the keyword search alone.
     *
     * @param  int|null  $page  Defaults to the request's ?page=; the MCP tools pass it explicitly.
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(?string $search, CatalogSort $sort, bool $onlyAvailable, ?int $page = null): LengthAwarePaginator
    {
        $ranked = $search !== null ? $this->hybrid->rankedIds($search, $onlyAvailable) : null;

        return Product::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when(
                $ranked !== null,
                fn (Builder $query) => $query->whereKey($ranked),
                fn (Builder $query) => $query->when($search, fn (Builder $query, string $search) => $query->where(
                    fn (Builder $query) => $query
                        ->whereLike('name', "%{$search}%")
                        ->orWhereLike('description', "%{$search}%")
                )),
            )
            ->when($onlyAvailable, fn (Builder $query) => $query->where('stock', '>', 0))
            ->tap(fn (Builder $query) => $ranked !== [] && $ranked !== null && $sort === CatalogSort::Featured
                ? $this->byRelevance($query, $ranked)
                : $this->sort($query, $sort))
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page)
            ->withQueryString();
    }

    /**
     * @param  Builder<Product>  $query
     * @param  non-empty-list<int>  $ids
     */
    private function byRelevance(Builder $query, array $ids): void
    {
        // Integer ids from the database, so they are safe to inline.
        $query->orderByRaw('array_position(ARRAY['.implode(',', array_map('intval', $ids)).']::bigint[], products.id)');
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
