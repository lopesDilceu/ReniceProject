<?php

use App\Enums\CatalogSort;
use App\Jobs\EmbedProduct;
use App\Models\Product;
use App\Queries\CatalogQuery;
use App\Search\HybridSearch;
use App\Search\SemanticSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

uses(RefreshDatabase::class);

/**
 * Deterministic "meaning": each concept is one axis of the vector space, so
 * texts about the same concept have cosine similarity 1 and different
 * concepts 0. The words are deliberately different from the product text
 * ("desenho japonês" vs "anime") to prove vector search finds by meaning.
 *
 * @return list<float>
 */
function conceptVector(string $text): array
{
    $text = mb_strtolower($text);
    $axis = match (true) {
        str_contains($text, 'anime'), str_contains($text, 'desenho japonês') => 0,
        str_contains($text, 'gelo grande'), str_contains($text, 'bastante gelo') => 1,
        str_contains($text, 'fresh'), str_contains($text, 'refrescante') => 2,
        default => 3,
    };

    $vector = array_fill(0, 1024, 0.0);
    $vector[$axis] = 1.0;

    return $vector;
}

function fakeEmbeddings(): void
{
    Embeddings::fake(fn (EmbeddingsPrompt $prompt) => array_map(conceptVector(...), $prompt->inputs));
}

function search(string $query, CatalogSort $sort = CatalogSort::Featured): array
{
    return app(CatalogQuery::class)->paginate($query, $sort, false)->pluck('name')->all();
}

it('fuses rankings with Reciprocal Rank Fusion', function () {
    // 7 is second in one list and first in the other: it beats 3 (first in one only).
    expect(app(HybridSearch::class)->fuse([3, 7, 9], [7, 5]))->toBe([7, 3, 5, 9]);
});

describe('without a provider', function () {
    beforeEach(fn () => config(['search.embeddings.provider' => null]));

    it('keeps keyword search and never calls a provider', function () {
        fakeEmbeddings();
        Queue::fake();
        Product::factory()->create(['name' => 'Copo Anime 500ml']);

        expect(search('anime'))->toBe(['Copo Anime 500ml'])
            ->and(search('desenho japonês'))->toBe([]);

        Embeddings::assertNothingGenerated();
        Queue::assertNotPushed(EmbedProduct::class);
    });
});

describe('with a provider', function () {
    beforeEach(function () {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('pgvector is PostgreSQL-only.');
        }

        config(['search.embeddings.provider' => 'voyageai']);
        fakeEmbeddings();

        $this->anime = Product::factory()->inStock(5)->create(['name' => 'Copo Anime 500ml', 'description' => 'Embalagem com tema anime.']);
        $this->big = Product::factory()->inStock(5)->create(['name' => 'Copo 300ml gelo grande', 'description' => 'Gelo de tamanho grande.']);
        $this->fresh = Product::factory()->inStock(5)->create(['name' => 'Copo Fresh 500ml', 'description' => 'Embalagem refrescante.']);
    });

    it('embeds products when they are created', function () {
        $row = DB::table('product_embeddings')->where('product_id', $this->anime->id)->first();

        expect(DB::table('product_embeddings')->count())->toBe(3)
            ->and($row->source_hash)->toHaveLength(64)
            ->and(json_decode($row->embedding)[0])->toEqual(1.0);
    });

    it('re-embeds only when the text changes', function () {
        Queue::fake();

        $this->anime->update(['price_cents' => 999]);
        Queue::assertNotPushed(EmbedProduct::class);

        $this->anime->update(['description' => 'Agora com tema refrescante.']);
        Queue::assertPushed(EmbedProduct::class, 1);
    });

    it('does not pay for an embedding whose text and model are unchanged', function () {
        Embeddings::fake(fn () => throw new RuntimeException('should not be called'));

        EmbedProduct::dispatchSync($this->anime);

        expect(DB::table('product_embeddings')->count())->toBe(3);
    });

    it('finds products by meaning, not only by words', function () {
        // No product contains "desenho japonês"; the anime cup means the same.
        expect(search('desenho japonês'))->toBe(['Copo Anime 500ml']);
    });

    it('puts products found both ways first', function () {
        // "gelo" is in two products' text, but only one is about lots of ice.
        expect(search('bastante gelo'))->toBe(['Copo 300ml gelo grande'])
            ->and(search('gelo grande')[0])->toBe('Copo 300ml gelo grande');
    });

    it('applies the chosen sort to the semantic results', function () {
        $this->anime->update(['price_cents' => 3000]);
        Product::factory()->inStock(5)->create(['name' => 'Copo Anime 300ml', 'price_cents' => 1000, 'description' => 'Tema anime.']);

        expect(search('desenho japonês', CatalogSort::PriceAsc))->toBe(['Copo Anime 300ml', 'Copo Anime 500ml']);
    });

    it('falls back to keywords when the provider fails', function () {
        Embeddings::fake(fn () => throw new RuntimeException('provider down'));
        Log::spy();

        expect(search('anime'))->toBe(['Copo Anime 500ml'])
            ->and(search('desenho japonês'))->toBe([]);

        Log::shouldHaveReceived('warning')->withArgs(fn (string $message) => str_contains($message, 'falling back to keywords'));
    });

    it('backfills every product with products:embed', function () {
        DB::table('product_embeddings')->delete();

        $this->artisan('products:embed')->assertSuccessful();

        expect(DB::table('product_embeddings')->count())->toBe(3);
    });
})->group('pgsql');

it('refuses to backfill without a provider', function () {
    config(['search.embeddings.provider' => null]);

    $this->artisan('products:embed')->assertFailed();
});

it('stays off with an empty provider setting', function () {
    // An empty `SEARCH_EMBEDDINGS_PROVIDER=` in .env reads as "", not null.
    config(['search.embeddings.provider' => '']);

    expect(SemanticSearch::enabled())->toBeFalse();
});
