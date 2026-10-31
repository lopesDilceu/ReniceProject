# Roadmap

The modernization runs in phases. Each phase is a [GitHub milestone](https://github.com/lopesDilceu/ReniceProject/milestones) and ends with a release, so progress can be compared against the [baseline metrics](metrics.md). The strategy behind this order is in [ADR 0004](adr/0004-modernization-strategy.md).

| Phase | Goal | Release |
|---|---|---|
| [0 — Foundation](https://github.com/lopesDilceu/ReniceProject/milestone/1) | Documentation, tooling and CI so every change is measurable and reviewable | `v2.0.0-alpha.1` |
| [1 — Framework upgrade](https://github.com/lopesDilceu/ReniceProject/milestone/2) | Laravel 11 → 13, PHP 8.4+, no vulnerable or abandoned dependencies | `v2.0.0-alpha.2` |
| [2 — Data layer redesign](https://github.com/lopesDilceu/ReniceProject/milestone/3) | Versioned schema; business rules moved from the database to the application | `v2.0.0-alpha.3` |
| [3 — Inertia + React rewrite](https://github.com/lopesDilceu/ReniceProject/milestone/4) | New UI module by module, closing every security finding with a regression test | `v2.0.0-beta.1` |
| [4 — Commerce core](https://github.com/lopesDilceu/ReniceProject/milestone/5) | Order lifecycle, Pix payments, concurrency-safe inventory | `v2.0.0-beta.2` |
| [5 — Showcase features](https://github.com/lopesDilceu/ReniceProject/milestone/6) | Semantic search, AI review summaries, realtime admin, API and MCP server | `v2.0.0-rc.1` |
| [6 — Production & case study](https://github.com/lopesDilceu/ReniceProject/milestone/7) | Observability, public deploy, final write-up | `v2.0.0` |

## Phase 0 — Foundation

- [#1](https://github.com/lopesDilceu/ReniceProject/issues/1) Document the legacy system and the initial audit
- [#2](https://github.com/lopesDilceu/ReniceProject/issues/2) ADRs, PR/issue templates and contribution workflow
- [#3](https://github.com/lopesDilceu/ReniceProject/issues/3) Code style and static analysis (Pint + Larastan)
- [#4](https://github.com/lopesDilceu/ReniceProject/issues/4) Pest and the test baseline
- [#5](https://github.com/lopesDilceu/ReniceProject/issues/5) CI pipeline on GitHub Actions
- [#6](https://github.com/lopesDilceu/ReniceProject/issues/6) Containerized local environment

## Phase 1 — Framework upgrade

- [#7](https://github.com/lopesDilceu/ReniceProject/issues/7) Laravel 11 → 12 (clears 5 security advisories)
- [#8](https://github.com/lopesDilceu/ReniceProject/issues/8) Laravel 12 → 13 and PHP 8.4+
- [#9](https://github.com/lopesDilceu/ReniceProject/issues/9) Replace the abandoned `yoeunes/toastr`
- [#10](https://github.com/lopesDilceu/ReniceProject/issues/10) Dev dependencies to current majors

## Phase 2 — Data layer redesign

- [#11](https://github.com/lopesDilceu/ReniceProject/issues/11) Schema as migrations, English names, legacy design fixes
- [#12](https://github.com/lopesDilceu/ReniceProject/issues/12) Inventory movements ledger
- [#13](https://github.com/lopesDilceu/ReniceProject/issues/13) Transactional `PlaceOrder` action replacing `finalizar_venda`
- [#14](https://github.com/lopesDilceu/ReniceProject/issues/14) Fix: supplier purchase overwrites the sale price
- [#15](https://github.com/lopesDilceu/ReniceProject/issues/15) Fix: best-sellers report counts rows instead of units
- [#16](https://github.com/lopesDilceu/ReniceProject/issues/16) Factories and demo seeders

## Phase 3 — Inertia + React rewrite

Security findings, each closed with a regression test:

- [#17](https://github.com/lopesDilceu/ReniceProject/issues/17) Privilege escalation via mass assignment — **critical**
- [#18](https://github.com/lopesDilceu/ReniceProject/issues/18) SQL injection in checkout — **critical**
- [#19](https://github.com/lopesDilceu/ReniceProject/issues/19) IDOR on cart items
- [#20](https://github.com/lopesDilceu/ReniceProject/issues/20) Order placement through GET (CSRF)
- [#21](https://github.com/lopesDilceu/ReniceProject/issues/21) Unauthenticated, unvalidated reviews
- [#22](https://github.com/lopesDilceu/ReniceProject/issues/22) Authentication hardening

Bugs and performance:

- [#23](https://github.com/lopesDilceu/ReniceProject/issues/23) Cart total includes every user's cart
- [#24](https://github.com/lopesDilceu/ReniceProject/issues/24) Sign-up crashes without a photo
- [#25](https://github.com/lopesDilceu/ReniceProject/issues/25) Product creation ignores the price
- [#26](https://github.com/lopesDilceu/ReniceProject/issues/26) N+1 queries and nested loops in views

Rewrite:

- [#27](https://github.com/lopesDilceu/ReniceProject/issues/27) Inertia + React + TypeScript setup
- [#28](https://github.com/lopesDilceu/ReniceProject/issues/28) Storefront
- [#29](https://github.com/lopesDilceu/ReniceProject/issues/29) Cart and checkout
- [#30](https://github.com/lopesDilceu/ReniceProject/issues/30) Customer account
- [#31](https://github.com/lopesDilceu/ReniceProject/issues/31) Admin panel with Policies and Form Requests

## Phase 4 — Commerce core

- [#32](https://github.com/lopesDilceu/ReniceProject/issues/32) Order lifecycle as a state machine
- [#33](https://github.com/lopesDilceu/ReniceProject/issues/33) Pix payments with idempotent webhooks
- [#34](https://github.com/lopesDilceu/ReniceProject/issues/34) Concurrency-safe stock reservation
- [#35](https://github.com/lopesDilceu/ReniceProject/issues/35) Transactional e-mails via queues

## Phase 5 — Showcase features

- [#36](https://github.com/lopesDilceu/ReniceProject/issues/36) Semantic product search with embeddings
- [#37](https://github.com/lopesDilceu/ReniceProject/issues/37) AI-generated review summaries
- [#38](https://github.com/lopesDilceu/ReniceProject/issues/38) Realtime admin dashboard (Reverb)
- [#39](https://github.com/lopesDilceu/ReniceProject/issues/39) Versioned REST API with OpenAPI docs
- [#40](https://github.com/lopesDilceu/ReniceProject/issues/40) MCP server for AI agents

## Phase 6 — Production & case study

- [#41](https://github.com/lopesDilceu/ReniceProject/issues/41) Observability
- [#42](https://github.com/lopesDilceu/ReniceProject/issues/42) Public deploy with demo data
- [#43](https://github.com/lopesDilceu/ReniceProject/issues/43) Case study and `v2.0.0`
