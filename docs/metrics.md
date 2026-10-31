# Metrics

Baseline numbers for the modernization, updated at every release. The **v1.0-academic** column is the frozen starting point and never changes.

How each number is collected is in [How to measure](#how-to-measure).

## Platform and dependencies

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Laravel | 11.10.0 (11.57.1 after `composer update`) | 13.39.0 | 13.x |
| PHP constraint | `^8.2` | `^8.4` | `^8.4` |
| Known security advisories (`composer audit`) | 5 | 0 | 0 |
| Abandoned packages | 1 | 0 | 0 |
| Direct dependencies behind a major version | 4 | 0 | 0 |
| Frontend libraries loaded from CDNs | 4 | 4 | 0 |

## Quality

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Automated tests | 0 (2 framework examples) | 123 passing on SQLite, 130 on PostgreSQL; 10 pending security tests | — |
| Test suite status | 1 of 2 example tests failing | green | green |
| Line coverage on `app/` | 0% | 50.3% (new code covered; legacy controllers: 0%) | ≥ 80% |
| Static analysis | not configured | Larastan level 6, 148 legacy errors baselined (new code: 0) | level 6+, empty baseline |
| Enforced code style | no | Pint (Laravel preset) in CI | Pint in CI |
| CI pipeline | none | lint + analyse + test on PHP 8.4/8.5, migrations + tests on PostgreSQL, dependency audit | lint + analyse + test + audit |

## Security and data

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Open audit findings (critical / high) | 3 / 6 | 2 / 3² | 0 / 0 |
| State-changing routes without `auth` (excluding login, sign-up, logout) | 6 | 6 | 0 |
| Raw SQL with interpolated input | 1 | 1 | 0 |
| Domain tables with migrations | 0 of 11 | 11 of 11 | all |
| Business rules in procedures / triggers | 2 / 8 | 0 / 0: all replaced by tested actions (`RecordInventoryMovement`, `RecordSupplierPurchase`, `PlaceOrder`) or FK rules. The legacy checkout controller still issues `CALL finalizar_venda` until it is rewritten (#29) | 0 / 0 |
| Fresh clone → running app | not possible | `docker compose up -d` + `migrate --seed` (demo store) | one command |

² Resolved: DM-01 (schema and rules versioned), SEC-07 (Laravel upgrade), BUG-01 (#14), BUG-02 (#13), SEC-06 and BUG-05 (#22). Still open in the legacy controllers until Phase 3: SEC-01, SEC-02 (critical), SEC-03, SEC-04, BUG-03 (high).

## Size

| Metric | v1.0-academic | Current |
|---|---|---|
| PHP lines (`app/`, `routes/`) | 1,745 | 1,768 (after Pint formatting) |
| Blade lines | 2,859 | 2,859 |
| Controllers / empty methods | 12 / 56 | 12 / 56 |
| Routes | 45 | 45 |

## Performance

Measured once the application can run from migrations (Phase 2). Recorded per page: database queries, response time (p50) and payload size.

| Page | v1.0-academic | Current |
|---|---|---|
| Catalog (`/produtos`) | not measurable¹ | 5 queries, constant in product count (tested) |
| Product page | — | 7 queries |
| Cart | not measurable¹ | — |
| Admin sales | not measurable¹ | — |

¹ v1 cannot run without the original MySQL database, which was not preserved.

## How to measure

| Metric | Command / method |
|---|---|
| Advisories, abandoned packages | `composer audit --locked` |
| Outdated majors | `composer outdated --direct --major-only` |
| Tests and coverage | `composer test` (Pest with coverage) |
| Static analysis | `composer analyse` |
| Unauthenticated state-changing routes | `php artisan route:list --json`, filtering non-GET routes without `auth` middleware |
| Lines | `find app routes -name '*.php' \| xargs cat \| wc -l` and the same for `resources/views` |
| Queries per page | Debugbar locally, plus query-count assertions in feature tests |
