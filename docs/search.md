# Search

The catalog search (store, REST API and MCP `search-products`) is keyword search, optionally made **hybrid with semantic search** on PostgreSQL + pgvector ([ADR 0007](adr/0007-semantic-search-with-pgvector.md)).

## Turning it on

```dotenv
SEARCH_EMBEDDINGS_PROVIDER=voyageai
VOYAGEAI_API_KEY=pa-...
```

Then embed the catalog once:

```bash
docker compose exec laravel.test php artisan products:embed
```

From then on, products are re-embedded automatically when their name or description changes. This runs as a queued job, so the `queue` service must be up. Any embeddings provider supported by [`laravel/ai`](https://github.com/laravel/ai) works. Changing the model means running `products:embed` again, and changing the dimensions also needs a migration.

## How a search runs

1. **Keyword candidates:** name or description contains the words. A match in the name ranks first.
2. **Vector candidates:**
   - The query is embedded, with a 3 s timeout and a one-day cache.
   - The products are ranked by cosine similarity with `whereVectorSimilarTo()`, keeping those at or above `SEARCH_MIN_SIMILARITY` (0.35).
3. **Fusion:** the two rankings are fused with Reciprocal Rank Fusion (1/(60 + rank) per list).
4. **Sorting:** with the default sort, results follow the fused relevance. With an explicit sort (price, rating, newest), that order applies to the fused set.

If the provider fails, times out or is not configured, step 2 is skipped and the result is plain keyword search. A warning is logged.

## Measurements

**pgvector query latency.** I ran [`tests/Benchmarks/pgvector-hnsw.sql`](../tests/Benchmarks/pgvector-hnsw.sql) in the Sail container (PostgreSQL 18 + pgvector on Docker Desktop, Windows 11), on 10,000 random 1024-dimension vectors with cosine distance, top 50:

| | p50 | p95 |
|---|---|---|
| HNSW index (200 queries) | **0.96 ms** | 1.26 ms |
| No index, sequential scan (50 queries) | 23.92 ms | 28.15 ms |

Building the index took 7.4 s, and table plus index use 132 MB. The store has 5 products, so the vector step is not where time goes. Random vectors are a pessimistic stand-in for real embeddings, which cluster.

**Provider latency and relevance: not measured yet.** Both need an API key. The query embedding is the slow part, which is why it has a 3 s timeout, falls back to keywords, and is cached. `tests/Feature/Search/SearchRelevanceTest.php` holds six shopper-style queries, such as "copo com desenho japonês" → the anime cup, and checks MRR ≥ 0.7 against the real provider:

```bash
docker compose exec -e VOYAGEAI_API_KEY=pa-... -e SEARCH_EMBEDDINGS_PROVIDER=voyageai \
  laravel.test php vendor/bin/pest --group=provider
```

## Cost

Voyage AI `voyage-4` costs **US$ 0.06 per million tokens**, and the first 200 million tokens are free ([pricing](https://docs.voyageai.com/docs/pricing), checked 2026-10-31). These are estimates from text length, not measured token counts:

| | Tokens | Cost |
|---|---|---|
| Embedding the whole catalog (5 products, about 60 tokens each) | ~300 | ~US$ 0.00002 |
| One search (a few words) | ~10 | ~US$ 0.0000006 |
| 1 million distinct searches | ~10 M | ~US$ 0.60, within the free tier |

Repeated searches are served from the cache, and unchanged products are never re-embedded (`source_hash`).
