# Case study: from a 2024 university project to a 2026 production-grade store

**Renice** is a fictional store that sells ready-to-drink cups with ice. In June 2024 it was the final project for two courses at Unimontes: a Laravel storefront with an admin area (Programming II) and a MySQL design built on stored procedures and triggers (Database II). That version is frozen as [`v1.0-academic`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic).

This document is about the second life of that code: auditing it, upgrading it, rebuilding it in place, and adding what a 2026 store is expected to have. All of it happened in public, through issues, pull requests, ADRs and releases.

## In one table

| | v1.0-academic (2024) | v2 (2026) |
|---|---|---|
| Framework | Laravel 11, PHP 8.2, 5 known advisories | Laravel 13, PHP 8.4+, 0 advisories |
| Frontend | Blade, Bootstrap and jQuery from CDNs | Inertia 3, React 19 with React Compiler, strict TypeScript, Tailwind 4 |
| Database | MySQL schema only in a PDF; rules in triggers | PostgreSQL 18 + pgvector, versioned migrations, rules in tested PHP |
| Tests | 2 framework examples (1 failing) | 420 on SQLite, 438 on PostgreSQL; Larastan level 6 with no baseline |
| Security | 28 audit findings, 3 critical (an anonymous visitor could become admin) | 0 open findings, each closed with a regression test |
| Payments | orders marked "completed" at once | Pix through Mercado Pago, with signed and idempotent webhooks |
| Sign-in | password | password, TOTP two-factor, passkeys |
| Integrations | none | REST API with an OpenAPI contract, MCP server for AI agents |
| Running it | not possible from the repository | `docker compose up -d` + `migrate --seed` |

The numbers and how they are measured are in [`metrics.md`](metrics.md).

## How the work was organized

1. **Audit before code.** The first deliverable was [`audit/initial-audit.md`](audit/initial-audit.md): 28 findings with severities, each tracked as an issue. The top four set the plan:
   - privilege escalation through mass assignment;
   - SQL injection in checkout;
   - a schema that existed only in a PDF;
   - a framework with unpatched advisories.
2. **Phases, each ending in a release.** The phases are in the [roadmap](roadmap.md): foundation, framework upgrade, data layer, UI rewrite, commerce core, showcase features, production. Each one is a GitHub milestone and ends in a tag (`alpha.1` to `rc.1`) with notes taken from the changelog.
3. **Modernize in place** ([ADR 0004](adr/0004-modernization-strategy.md)). There was no green-field rewrite next to the old code. Each v1 module was replaced, its old URLs were redirected permanently, and the v1 code was deleted when nothing used it any more.
4. **Every change through a PR with green CI.** The CI runs:
   - lint, static analysis and tests on PHP 8.4 and 8.5;
   - migrations up, down and up again, then the full suite, on PostgreSQL;
   - the frontend build and type check;
   - `composer audit`.

   `main` is protected by all five jobs.
5. **Decisions written down** as ADRs: the [frontend stack](adr/0001-frontend-stack-inertia-react.md), [rules in the application](adr/0002-business-rules-in-the-application.md), [naming](adr/0003-schema-naming-conventions.md), [strategy](adr/0004-modernization-strategy.md), [PostgreSQL](adr/0005-postgresql.md), [Pix with Mercado Pago](adr/0006-pix-with-mercado-pago.md) and [semantic search](adr/0007-semantic-search-with-pgvector.md).

## Decisions worth explaining

**Business rules moved out of the database** ([ADR 0002](adr/0002-business-rules-in-the-application.md)). v1's triggers had two silent bugs:
- every restock overwrote the sale price with the supplier's cost;
- the best-sellers view counted order lines instead of units.

Stock is now an append-only ledger. `RecordInventoryMovement` locks the product row, refuses negative stock and records the source, actor and resulting balance of every change. The database still enforces integrity with foreign keys and CHECK constraints, and the rules themselves are PHP that can be tested.

**PostgreSQL over MySQL** ([ADR 0005](adr/0005-postgresql.md)). The deciding factor was Laravel 13's native vector search, which only exists for pgvector, and that made semantic search possible without a second datastore. The research is in the ADR.

**Money as integer cents, orders as snapshots.** Prices are stored as cents, so sums are exact. An order keeps the product name, unit price and delivery address as they were at checkout, so renaming or repricing a product never rewrites history.

**Payment providers and AI providers behind interfaces, off by default.** Mercado Pago sits behind `PaymentGateway`, with a local fake that produces real QR codes. Embeddings and LLM summaries go through `laravel/ai`. Without credentials, the store runs fully, and nothing pretends to be AI.

## Problems that turned into the interesting parts

**Proving there is no overselling.** Locks are easy to write and hard to trust. [`ConcurrentCheckoutTest`](../tests/Feature/Checkout/ConcurrentCheckoutTest.php) starts eight PHP processes, each with its own database connection, which check out the last unit at the same instant: exactly one succeeds. To check that the test can fail, the row locks were removed: **three orders took the same unit**, and the test failed as it should.

**A CSRF exemption that did nothing.** The Mercado Pago webhook excluded `ValidateCsrfToken`, and every test passed. A curl against the running app answered **419**. The cause: Laravel skips CSRF during tests, and in Laravel 13 the middleware that actually runs is `PreventRequestForgery`, of which `ValidateCsrfToken` is only a subclass alias. The fix was one line. The lesson was to check security boundaries against the real app, and to add a regression test that inspects the route's middleware.

**PostgreSQL is stricter than SQLite.** A unique-constraint violation inside a transaction aborts the whole transaction on PostgreSQL, so catching the exception is not enough. The fast SQLite suite missed it; the PostgreSQL CI job caught it. Webhook deduplication now uses `INSERT … ON CONFLICT DO NOTHING`, and charge creation uses a savepoint.

**Webhooks that cannot be forged or replayed.** Idempotency works at three levels:
- one charge per order, with the same idempotency key on every attempt;
- one stored row per notification, with a unique key;
- status changes that ignore a status already applied.

The notification body is never trusted: the job reads the payment's status back from the provider's API.

**AI agents that cannot spend money.** The MCP server lets an assistant search, fill a cart and prepare a checkout, but never place an order. `start-checkout` returns a signed, 15-minute link that pins the customer, the address and the exact cart with its prices, and only the customer, signed in, can confirm it. Even an agent misled by prompt injection can at most fill a cart.

**A paid API call on every update.** The first version of "re-embed when the product changes" used Eloquent's `saved` event and `wasRecentlyCreated`. That flag stays `true` on the instance after creation, so a later price change would have paid for a new embedding. A test caught it before it shipped.

**A test that failed one run in many.** A search test failed on CI whenever Faker generated a customer named "Joana", the name it was searching for. Tests that search now use fixed names.

**A dependency trade-off made on purpose.** Laravel Reverb 1.12 still requires Guzzle's PSR-7 v2, so adding WebSockets meant going back from Guzzle 8 to 7. The 7.x line is maintained and Guzzle is a transitive dependency here. Moving back is tracked in [#72](https://github.com/lopesDilceu/ReniceProject/issues/72), waiting for Reverb's next release.

## What a visitor sees

The [showcase](showcase.md) has every v1 screen next to its v2 version and a GIF of a purchase. `docker compose -f compose.demo.yaml up --build` runs the demo store locally.

- **Catalog:** search by meaning ("copo com desenho japonês" finds the anime cup) when a provider is configured, and AI review summaries labeled as such.
- **Checkout:** Pix with a QR code. The order page turns to "Pago" live over WebSockets, and the stock stays reserved for 60 minutes.
- **Account:** passkeys and two-factor authentication under "Minha conta → Segurança".
- **Admin:** a dashboard that updates live, an audited order timeline (who, when and why), a stock ledger, and Pulse for metrics and exceptions.
- **Developers:** `/docs/api` (OpenAPI 3.1, contract-tested), `/mcp` for AI agents, and `/health` for monitors.

## What is not done, or not proven yet

- **No public deploy, on purpose.** Hosting a demo costs money every month, so the project stops at a deployable artifact: CI builds and boots the production image on every pull request and publishes it to the GitHub Container Registry from `main`. A platform's deploy hook is one secret away ([deploy](deploy.md)). Meanwhile the [showcase](showcase.md) and the one-command local demo stand in for a public URL ([#42](https://github.com/lopesDilceu/ReniceProject/issues/42)).
- **The AI features have only run against fakes.** Real-provider relevance and summary quality are untested, and the relevance test is ready (`--group=provider`). Provider latency is not measured; pgvector latency is (0.96 ms p50 over 10,000 vectors).
- **Mercado Pago and passkeys have not met real credentials or a real authenticator.** Both are tested against their documented contracts, and the Pix flow runs end to end in the browser with the fake gateway.
- **Coverage (93.4%) is measured on the SQLite job only.** The PostgreSQL-only code is tested in the other job but sits outside that number.

## Lessons

- **An audit is a better plan than a feature list.** It made the order of the work obvious, and "closed with a regression test" made "done" verifiable.
- **The fast test suite is not the real environment.** SQLite, `runningUnitTests()` and fake gateways each hid one real bug in this project. A second CI job on the production database, and a few manual checks against the running app, paid for themselves.
- **Look at it.** Running the demo and scripting a browser through it, for the screenshots, found six bugs that 440 passing tests did not: checkout copy still promising Pix "in the next phase", internal class names ("SupplierPurchase #2") in the admin stock ledger, demo orders numbered out of date order, the product photos lost in the rewrite, `demo:reset` deleting the photos it had just restored, and "Laravel" as the page title when `APP_NAME` is unset.
- **Mutation-check the tests that guard invariants.** Removing the locks, the after-commit dispatch or the signature check and watching the test fail is the only way to know the test protects anything.
- **Put the expensive or dangerous edge behind an explicit seam:** payments, AI and agent checkouts. Then the default can be safe and free, and the real thing is a configuration change.

## How it was built

The work was done in pair-programming sessions with an AI coding assistant ([Claude Code](https://claude.com/claude-code)), as the `Co-Authored-By` trailers on the commits show. The direction, the review of each pull request and the merges were done by the author.

## Timeline

| Release | Date | Phase |
|---|---|---|
| [`v1.0-academic`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic) | 2024-06 | Original university project |
| [`v2.0.0-alpha.1`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.1) | 2026-10-31 | Foundation: docs, tooling, CI |
| [`v2.0.0-alpha.2`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.2) | 2026-10-31 | Laravel 11 → 13, PHP 8.4+ |
| [`v2.0.0-alpha.3`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.3) | 2026-10-31 | Versioned schema, rules out of the database |
| [`v2.0.0-beta.1`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-beta.1) | 2026-10-31 | Inertia + React rewrite; all audit findings closed |
| [`v2.0.0-beta.2`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-beta.2) | 2026-10-31 | Order lifecycle, Pix, stock reservation, e-mails |
| [`v2.0.0-rc.1`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-rc.1) | 2026-10-31 | Passkeys, API, realtime, MCP, semantic search, AI summaries |
| [`v2.0.0`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0) | 2026-11-01 | Production image, this case study and the showcase |
