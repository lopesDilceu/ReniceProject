# 0007 — Semantic search with pgvector embeddings, hybrid with keywords

- **Status:** Accepted
- **Date:** 2026-10-31
- **Issues:** [#36](https://github.com/lopesDilceu/ReniceProject/issues/36)

## Context

The catalog search matches words: "desenho japonês" finds nothing, although the anime cup is exactly that. Shoppers describe what they want; product texts use the store's vocabulary.

[ADR 0005](0005-postgresql.md) chose PostgreSQL partly for this: Laravel 13 ships vector columns, HNSW indexes and `whereVectorSimilarTo()` for pgvector.

## Options for storing vectors

| Option | For | Against |
|---|---|---|
| **pgvector in the existing PostgreSQL** | No new service. Vectors live next to the products, so filters (in stock, not deleted) are one SQL query. First-party Laravel 13 support. Transactional, and part of the existing backups | Recall and speed depend on HNSW tuning at very large scale |
| Dedicated vector database (Pinecone, Qdrant, Weaviate) | Built for billions of vectors and managed scaling | Another service, another bill, and data duplicated outside the database, kept in sync by hand. Filtering by stock needs extra metadata |
| Search engine with vectors (Meilisearch, Typesense, through Scout) | Typo tolerance and facets | Another service to run in Docker and in production, for a catalog of a few products |

## Decision

- **Storage:** pgvector, in a separate table (`product_embeddings`), so catalog queries never load 1024 floats per row. HNSW index with cosine distance.
- **Provider:** embeddings through `laravel/ai`. Any provider it supports can be chosen with `SEARCH_EMBEDDINGS_PROVIDER`; the recommended one is **Voyage AI `voyage-4`** (1024 dimensions).
- **Ranking:** hybrid. Keyword candidates and vector candidates are fused with **Reciprocal Rank Fusion** (score = Σ 1/(60 + rank)). Products found both ways rise to the top, and a product found only by meaning still appears. The shopper's explicit sorts (price, rating, newest) apply to the fused set.
- **Off by default.** With no provider, or when the provider fails or takes more than 3 s, search is the keyword search it always was, and the failure is logged. Without a key, nothing pretends to be AI.

## Consequences

- **When products are embedded:** on creation, and again only when the name or description changes. A queued, after-commit job does it, unique per product for a minute. A `source_hash` of model + text skips calls that could not change the vector. `php artisan products:embed` backfills the catalog.
- **Query embeddings** are cached for a day, so a repeated search costs nothing.
- **Changing models:** a different model means re-embedding, and different dimensions also mean a migration, because vectors from different models are not comparable. The model is stored on each row.
- **Measured** (see [`docs/search.md`](../search.md)): query latency in pgvector. **Not measured:** the provider's own latency and the relevance numbers, which need an API key. The relevance test exists and runs with `--group=provider`.
