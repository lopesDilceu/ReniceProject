<?php

namespace App\Search;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Keyword and vector search fused with Reciprocal Rank Fusion: each list
 * contributes 1 / (k + rank) to a product's score. Products found by both
 * rise to the top; a product found only by meaning ("copo com tema de
 * desenho japonês" → the anime cup) still shows up.
 */
final class HybridSearch
{
    /** RRF's usual constant: dampens the weight of the very first ranks. */
    private const K = 60;

    /**
     * Product ids by relevance, or null when semantic search is off or the
     * provider failed: the caller then keeps keyword search only.
     *
     * @return list<int>|null
     */
    public function rankedIds(string $search, bool $onlyAvailable): ?array
    {
        if (! SemanticSearch::enabled()) {
            return null;
        }

        try {
            $vector = SemanticSearch::embed(
                $search,
                timeout: Config::integer('search.embeddings.query_timeout'),
                cacheSeconds: Config::integer('search.embeddings.query_cache_seconds'),
            )->first();
        } catch (Throwable $exception) {
            Log::warning('Semantic search unavailable, falling back to keywords.', ['error' => $exception->getMessage()]);

            return null;
        }

        return $this->fuse($this->keywordIds($search, $onlyAvailable), $this->vectorIds($vector, $onlyAvailable));
    }

    /**
     * @param  list<int>  ...$rankings
     * @return list<int>
     */
    public function fuse(array ...$rankings): array
    {
        $scores = [];

        foreach ($rankings as $ranking) {
            foreach ($ranking as $rank => $id) {
                $scores[$id] = ($scores[$id] ?? 0) + 1 / (self::K + $rank + 1);
            }
        }

        arsort($scores);

        return array_keys($scores);
    }

    /**
     * @return list<int>
     */
    private function keywordIds(string $search, bool $onlyAvailable): array
    {
        return Product::query()
            ->where(fn (Builder $query) => $query->whereLike('name', "%{$search}%")->orWhereLike('description', "%{$search}%"))
            ->when($onlyAvailable, fn (Builder $query) => $query->where('stock', '>', 0))
            // A match in the name ranks above one in the description.
            ->orderByRaw('CASE WHEN name ILIKE ? THEN 0 ELSE 1 END', ["%{$search}%"])
            ->orderBy('id')
            ->limit(Config::integer('search.candidates'))
            ->pluck('id')
            ->all();
    }

    /**
     * @param  list<float>  $vector
     * @return list<int>
     */
    private function vectorIds(array $vector, bool $onlyAvailable): array
    {
        return DB::table('product_embeddings')
            ->join('products', 'products.id', '=', 'product_embeddings.product_id')
            ->whereNull('products.deleted_at')
            ->when($onlyAvailable, fn ($query) => $query->where('products.stock', '>', 0))
            ->whereVectorSimilarTo('product_embeddings.embedding', $vector, minSimilarity: (float) Config::get('search.min_similarity'))
            ->limit(Config::integer('search.candidates'))
            ->pluck('product_embeddings.product_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
