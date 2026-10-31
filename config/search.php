<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Semantic search
    |--------------------------------------------------------------------------
    |
    | Products are embedded with this laravel/ai provider and searched by
    | meaning on PostgreSQL + pgvector (ADR 0007). Leave the provider empty to
    | keep keyword search only, which is also the fallback whenever the
    | provider fails. Changing the model or dimensions requires re-running
    | `php artisan products:embed` (and a migration for the dimensions).
    |
    */

    'embeddings' => [
        'provider' => env('SEARCH_EMBEDDINGS_PROVIDER'),
        // Null uses the provider's default (voyage-4 for Voyage AI).
        'model' => env('SEARCH_EMBEDDINGS_MODEL'),
        'dimensions' => 1024,
        // Embedding the search query blocks the request: give up quickly.
        'query_timeout' => 3,
        // Repeated searches reuse the query's embedding for a day.
        'query_cache_seconds' => 86400,
    ],

    // Cosine similarity a product needs to count as a match (0 to 1).
    'min_similarity' => (float) env('SEARCH_MIN_SIMILARITY', 0.35),

    // How many keyword and vector candidates are fused per search.
    'candidates' => 50,

];
