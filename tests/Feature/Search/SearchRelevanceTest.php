<?php

/*
| Relevance against the real embeddings provider: the store's catalog and
| shopper-style queries that share few or no words with the product text.
| Opt-in, since it calls a paid API: runs only on PostgreSQL with
| VOYAGEAI_API_KEY set, e.g.
|
|   docker compose exec -e VOYAGEAI_API_KEY=... laravel.test \
|     php vendor/bin/pest --group=provider
*/

use App\Enums\CatalogSort;
use App\Queries\CatalogQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/** Query => the product a shopper means. */
const RELEVANCE_SET = [
    'copo com desenho japonês' => 'copo-anime-500ml-gelo-medio',
    'quero bastante gelo' => 'copo-300ml-gelo-grande',
    'copo pequeno com pouco gelo' => 'copo-300ml-gelo-pequeno',
    'algo bem refrescante para o calor' => 'copo-fresh-500ml-gelo-medio',
    'copo grande com tema de anime' => 'copo-anime-500ml-gelo-medio',
    'gelo médio, 300 ml' => 'copo-300ml-gelo-medio',
];

it('ranks what the shopper means near the top', function () {
    if (DB::getDriverName() !== 'pgsql' || ! env('VOYAGEAI_API_KEY')) {
        $this->markTestSkipped('Needs PostgreSQL and VOYAGEAI_API_KEY (calls the real provider).');
    }

    Http::allowStrayRequests();
    config(['search.embeddings.provider' => 'voyageai', 'ai.providers.voyageai.key' => env('VOYAGEAI_API_KEY')]);
    $this->seed();

    $reciprocalRanks = [];

    foreach (RELEVANCE_SET as $query => $expected) {
        $slugs = app(CatalogQuery::class)->paginate($query, CatalogSort::Featured, false)->pluck('slug')->all();
        $rank = array_search($expected, $slugs, true);
        $reciprocalRanks[$query] = $rank === false ? 0 : 1 / ($rank + 1);
    }

    $mrr = array_sum($reciprocalRanks) / count($reciprocalRanks);
    fwrite(STDERR, sprintf("\nMRR %.2f over %d queries: %s\n", $mrr, count($reciprocalRanks), json_encode($reciprocalRanks, JSON_UNESCAPED_UNICODE)));

    expect($mrr)->toBeGreaterThanOrEqual(0.7);
})->group('provider');
