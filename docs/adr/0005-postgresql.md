# 0005 — Use PostgreSQL as the database

- **Status:** Accepted
- **Date:** 2026-10-30
- **Issues:** [#6](https://github.com/lopesDilceu/ReniceProject/issues/6), [#11](https://github.com/lopesDilceu/ReniceProject/issues/11), [#36](https://github.com/lopesDilceu/ReniceProject/issues/36)

## Context

v1 ran on MySQL because the Database II course used it. Nothing from that database survives: the schema is being rebuilt from scratch ([ADR 0002](0002-business-rules-in-the-application.md), [ADR 0003](0003-schema-naming-conventions.md)), so changing engine now costs almost nothing. Changing later would mean migrating data.

The roadmap needs semantic search ([#36](https://github.com/lopesDilceu/ReniceProject/issues/36)), concurrency-safe inventory ([#34](https://github.com/lopesDilceu/ReniceProject/issues/34)) and a cheap public demo ([#42](https://github.com/lopesDilceu/ReniceProject/issues/42)).

Requirement from the project owner: use PostgreSQL only if Laravel integrates with it strongly; otherwise stay on MySQL.

## Research

**Laravel's integration with PostgreSQL is first-class, and ahead of MySQL in the areas this project needs:**

- **Native vector search is PostgreSQL-only.** Laravel 13 ships `Schema::ensureVectorExtensionExists()`, a `vector()` column type with HNSW indexes, and `whereVectorSimilarTo()` on the query builder. It supports PostgreSQL with pgvector (and MongoDB through a separate package), not MySQL. ([Laravel 13 docs — Search](https://laravel.com/docs/13.x/search))
- **Laravel's own hosting defaults to Postgres.** Laravel Cloud offers serverless Postgres (hibernates when idle, pgbouncer pooling, pgvector preinstalled). Its MySQL offering is fixed-size, does not hibernate, and is labeled a developer preview. ([Laravel Cloud — Databases](https://cloud.laravel.com/docs/resources/databases))
- The PostgreSQL driver ships in the framework, with full schema-builder and Eloquent support and Sail support for local development.

**What comparable projects use:**

| Project | Database |
|---|---|
| Medusa, Saleor, Vendure (modern open-source commerce engines) | PostgreSQL only ([OpenReplay](https://blog.openreplay.com/5-open-source-ecommerce-platforms/), [Netguru](https://www.netguru.com/blog/saleor-vs-medusa)) |
| Lunar (Laravel e-commerce), Sylius (Symfony) | MySQL or PostgreSQL ([Packagist — lunarphp/core](https://packagist.org/packages/lunarphp/core)) |
| Shopify, Etsy | MySQL, sharded with Vitess at petabyte scale ([Shopify Engineering](https://shopify.engineering/horizontally-scaling-the-rails-backend-of-shop-app-with-vitess), [InfoQ](https://infoq.com/news/2026/04/etsy-vitess-sharding-migration)) |

The giants run MySQL because of choices made over a decade ago and an enormous investment in sharding tooling around it. Commerce platforms started in recent years default to PostgreSQL. Among developers, PostgreSQL was the most-used database in the 2025 Stack Overflow survey, ahead of MySQL by roughly 18 points ([secondary report](https://enstacked.com/stack-overflow-developer-survey-insights/)).

## Decision

Use **PostgreSQL 18 with the pgvector extension** in every environment except the unit and feature test run, which keeps using in-memory SQLite for speed. CI adds a job that runs migrations and the suite against PostgreSQL, so engine-specific behaviour is covered.

Local development uses Laravel Sail with the `pgvector/pgvector` image, so the extension is available from day one.

## Alternatives considered

| Option | Why not |
|---|---|
| Stay on MySQL | No native vector search in Laravel. Semantic search would need an external vector store (another service to run and pay for). Laravel Cloud's MySQL is a preview without scale-to-zero |
| MySQL + external vector DB (e.g. Qdrant, Pinecone) | Two sources of truth for products; extra service, credentials and sync jobs for a small catalog |
| SQLite in production | Fine for small sites, but no row-level locking for concurrent checkouts ([#34](https://github.com/lopesDilceu/ReniceProject/issues/34)) and no pgvector |

## Consequences

- Semantic search ([#36](https://github.com/lopesDilceu/ReniceProject/issues/36)) uses Laravel's built-in vector API instead of an extra service.
- `SELECT … FOR UPDATE` and transactional DDL are available for the inventory and checkout work.
- Tests on SQLite can hide PostgreSQL-specific behaviour (strict typing, case-sensitive `LIKE`, check constraints). The PostgreSQL CI job exists to catch that. Features that need Postgres (vector columns) are tested only there.
- The v1 MySQL SQL in the course report is reference material only. It is not ported.
