# Renice

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
| 0 — Foundation | In progress |
| 1 — Framework upgrade | Planned |
| 2 — Data layer redesign | Planned |
| 3 — Inertia + React rewrite | Planned |
| 4 — Commerce core | Planned |
| 5 — Showcase features | Planned |
| 6 — Production & case study | Planned |

Before/after numbers are tracked in [`docs/metrics.md`](docs/metrics.md).

## Stack

| | v1.0-academic | Target |
|---|---|---|
| Backend | Laravel 11, PHP 8.2 | Laravel 13, PHP 8.4+ |
| Frontend | Blade, Bootstrap, jQuery | Inertia, React, TypeScript, Tailwind |
| Business rules | MySQL procedures and triggers | Tested application code |
| Quality | — | Pest, Larastan, Pint, GitHub Actions |

## Documentation

| | |
|---|---|
| [Original system](docs/legacy/README.md) | What v1 did, its database design and screenshots |
| [Initial audit](docs/audit/initial-audit.md) | Security, correctness and design findings |
| [Roadmap](docs/roadmap.md) | Phases, milestones and issues |
| [Metrics](docs/metrics.md) | Baseline and progress |
| [Decisions](docs/adr/README.md) | Architecture Decision Records |
| [Contributing](CONTRIBUTING.md) | Workflow, commits and releases |
| [Changelog](CHANGELOG.md) | Notable changes per release |

## Running locally

Not yet possible from a fresh clone: the original database schema was never versioned. A one-command setup with demo data arrives in Phase 0/2 ([#6](https://github.com/lopesDilceu/ReniceProject/issues/6), [#11](https://github.com/lopesDilceu/ReniceProject/issues/11)).

## Authors

- **Dilceu Lopes:** application, database and modernization
- **Renan Iago Figueiredo Silva Ciríaco:** database design in v1

## License

[MIT](LICENSE)
