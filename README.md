# Renice

[![CI](https://github.com/lopesDilceu/ReniceProject/actions/workflows/ci.yml/badge.svg)](https://github.com/lopesDilceu/ReniceProject/actions/workflows/ci.yml)

**English** · [Português](README.pt-BR.md)

An e-commerce application that started as a university project in 2024 and is being rebuilt, in the open, to a 2026 production standard.

> **Status: modernization in progress.** The code on `main` is still close to the original version, which has known security issues and is not meant to be deployed. Follow the progress in the [roadmap](docs/roadmap.md).

![Renice v1 product catalog](docs/legacy/images/03-product-catalog.jpg)

## The story

Renice is a fictional brand that sells ready-to-drink cups with ice. In June 2024 it became the final project for two courses in the Information Systems program at Unimontes: a Laravel storefront with an admin back office (Programming II) and a MySQL design built on stored procedures and triggers (Database II).

That version is frozen at [`v1.0-academic`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic). This repository now documents the work of taking it from coursework to a codebase I would put in production:

1. **Audit:** 28 findings, from privilege escalation and SQL injection to business rules hidden in triggers ([read the audit](docs/audit/initial-audit.md)).
2. **Platform:** Laravel 11 → 13, PHP 8.4+, CI with static analysis and tests.
3. **Data layer:** a versioned schema and business rules moved from the database into tested application code.
4. **Frontend:** a rewrite with Inertia, React and TypeScript.
5. **Commerce:** order lifecycle, Pix payments with webhooks, concurrency-safe inventory.
6. **2026 features:** semantic search, AI review summaries, a realtime admin, a public API and an MCP server for AI agents.

Every step is an issue, a pull request and, when there is a real trade-off, an [architecture decision record](docs/adr/README.md).

## Progress

| Phase | Status |
|---|---|
| 0 — Foundation | Done ([`v2.0.0-alpha.1`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.1)) |
| 1 — Framework upgrade | Done ([`v2.0.0-alpha.2`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.2)) |
| 2 — Data layer redesign | Done ([`v2.0.0-alpha.3`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.3)) |
| 3 — Inertia + React rewrite | In progress |
| 4 — Commerce core | Planned |
| 5 — Showcase features | Planned |
| 6 — Production & case study | Planned |

Before/after numbers are tracked in [`docs/metrics.md`](docs/metrics.md).

## Stack

| | v1.0-academic | Target |
|---|---|---|
| Backend | Laravel 11, PHP 8.2 | Laravel 13, PHP 8.4+ |
| Frontend | Blade, Bootstrap, jQuery | Inertia 3, React 19 (React Compiler), TypeScript, Tailwind 4, shadcn/ui, Wayfinder |
| Database | MySQL | PostgreSQL 18 + pgvector |
| Business rules | MySQL procedures and triggers | Tested application code |
| Local environment | manual | Docker (Laravel Sail) |
| Quality | — | Pest, Larastan (level 6), Pint, GitHub Actions |

## Documentation

| | |
|---|---|
| [Original system](docs/legacy/README.md) | What v1 did, its database design and screenshots |
| [Initial audit](docs/audit/initial-audit.md) | Security, correctness and design findings |
| [Database](docs/database.md) | Schema diagram and design rules |
| [Roadmap](docs/roadmap.md) | Phases, milestones and issues |
| [Metrics](docs/metrics.md) | Baseline and progress |
| [Decisions](docs/adr/README.md) | Architecture Decision Records |
| [Contributing](CONTRIBUTING.md) | Workflow, commits and releases |
| [Changelog](CHANGELOG.md) | Notable changes per release |

## Running locally

Requirements: Docker. The environment runs on [Laravel Sail](https://laravel.com/docs/sail): PHP 8.5, PostgreSQL 18 with pgvector ([ADR 0005](docs/adr/0005-postgresql.md)) and Mailpit.

```bash
composer install            # or: docker run --rm -v "$(pwd)":/app -w /app composer install
cp .env.example .env
vendor/bin/sail up -d
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate --seed
```

**Windows without WSL:** the `sail` script only runs on Linux, macOS and WSL2. Use Docker Compose directly from PowerShell. It is the same stack, and `.env.example` already has the `WWWUSER`/`WWWGROUP` values it needs:

```powershell
docker compose up -d
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate --seed
```

For better file-system performance, Laravel recommends keeping the project inside the WSL2 file system and using `sail` from there.

| Service | URL |
|---|---|
| Application | http://localhost |
| Mailpit (caught e-mails) | http://localhost:8025 |
| PostgreSQL | `localhost:5432`, user `sail`, password `password` |

The seed builds a demo store with the original product line, nine customers, orders, stock history and reviews, all through the real domain actions. Run `migrate:fresh --seed` to reset it.

| Demo account | E-mail | Password |
|---|---|---|
| Admin | `admin@renice.test` | `password` |
| Customer | `cliente@renice.test` | `password` |

The schema is documented in [`docs/database.md`](docs/database.md). The legacy screens still query the v1 tables until each module is rewritten in Phase 3, so only the pages that do not need the database work today.

Quality checks (no Docker needed; tests use in-memory SQLite):

```bash
composer check   # Pint, Larastan and Pest, as in CI
```

## Authors

- **Dilceu Lopes:** application, database and modernization
- **Renan Iago Figueiredo Silva Ciríaco:** database design in v1

## License

[MIT](LICENSE)
