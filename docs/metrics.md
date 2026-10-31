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
| Frontend libraries loaded from CDNs | 4 | 0 | 0 |

## Quality

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Automated tests | 0 (2 framework examples) | 321 passing on SQLite, 330 on PostgreSQL (including a concurrent-checkout test with 8 processes); 0 pending | — |
| Test suite status | 1 of 2 example tests failing | green | green |
| Line coverage on `app/` | 0% | **97.8%** | ≥ 80% |
| Static analysis | not configured | Larastan level 6, **no baseline**: 0 ignored errors | level 6+, empty baseline |
| Enforced code style | no | Pint (Laravel preset) in CI | Pint in CI |
| CI pipeline | none | lint + analyse + test on PHP 8.4/8.5, migrations + tests on PostgreSQL, dependency audit | lint + analyse + test + audit |

## Security and data

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Open audit findings (all severities) | 28 | **0**² | 0 / 0 |
| State-changing routes without `auth` (excluding login, sign-up, logout) | 6 | 0 (the Mercado Pago webhook is authenticated by its HMAC signature instead) | 0 |
| Raw SQL with interpolated input | 1 | 0 | 0 |
| Domain tables with migrations | 0 of 11 | 11 of 11 | all |
| Business rules in procedures / triggers | 2 / 8 | 0 / 0: all replaced by tested actions (`RecordInventoryMovement`, `RecordSupplierPurchase`, `PlaceOrder`) or FK rules | 0 / 0 |
| Fresh clone → running app | not possible | `docker compose up -d` + `migrate --seed` (demo store) | one command |
| Payment | none: orders marked completed at once (BUG-02) | Pix with signed webhooks, idempotent at three levels (charge, notification, status) | — |
| Overselling under concurrency | not handled | 8 simultaneous checkouts for the last unit: exactly 1 succeeds (3 without the row locks) | 0 oversold |

² All 28 findings are resolved: DM-01–09 (schema, #11/#12), SEC-07 (Laravel upgrade), BUG-01 (#14), BUG-02 (#13), BUG-06 (#15), SEC-06 and BUG-05 (#22), SEC-02/03/04 and BUG-03 (#29), SEC-01 and SEC-05 (#30), BUG-04, MNT-01, MNT-04, MNT-05 and MNT-06 (#31), MNT-02 and MNT-03 (Phase 0).

## Size

| Metric | v1.0-academic | Current |
|---|---|---|
| PHP lines (`app/`, `routes/`) | 1,745 | 5,037 (all v2; v1 removed) |
| Blade lines | 2,859 | 47 (the Inertia root view) |
| TypeScript/React lines (excluding shadcn/ui) | 0 | 5,472 |
| Controllers / empty methods | 12 / 56 | 19 / 0 |
| Routes (application, excluding Fortify and Debugbar) | 45 | 53 |

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
