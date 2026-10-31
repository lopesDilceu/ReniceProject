<?php

namespace App\Search;

use App\Models\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Responses\EmbeddingsResponse;

/**
 * Embeddings for products and search queries, through laravel/ai. Off
 * unless a provider is configured and the database is PostgreSQL.
 */
final class SemanticSearch
{
    public static function enabled(): bool
    {
        // filled(): an empty SEARCH_EMBEDDINGS_PROVIDER= in .env reads as "".
        return filled(Config::get('search.embeddings.provider')) && DB::getDriverName() === 'pgsql';
    }

    /**
     * What gets embedded for a product: what a shopper would describe.
     */
    public static function text(Product $product): string
    {
        return trim($product->name."\n".$product->description);
    }

    /**
     * The embedding plus the model that actually produced it (response meta).
     */
    public static function embed(string $text, ?int $timeout = null, ?int $cacheSeconds = null): EmbeddingsResponse
    {
        $request = Embeddings::for([$text])->dimensions(Config::integer('search.embeddings.dimensions'));

        if ($timeout !== null) {
            $request->timeout($timeout);
        }

        if ($cacheSeconds !== null) {
            $request->cache($cacheSeconds);
        }

        return $request->generate(self::provider(), self::model());
    }

    public static function provider(): string
    {
        return Config::string('search.embeddings.provider');
    }

    /**
     * The model to record next to each embedding; null means the provider's default.
     */
    public static function model(): ?string
    {
        $model = Config::get('search.embeddings.model');

        return is_string($model) && $model !== '' ? $model : null;
    }
}
