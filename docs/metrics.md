# Metrics

Baseline numbers for the modernization, updated at every release. The **v1.0-academic** column is the frozen starting point and never changes.

How each number is collected is in [How to measure](#how-to-measure).

## Platform and dependencies

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Laravel | 11.10.0 (11.57.1 after `composer update`) | 11.57.1 | 13.x |
| PHP constraint | `^8.2` | `^8.2` | `^8.4` |
| Known security advisories (`composer audit`) | 5 | 5 | 0 |
| Abandoned packages | 1 | 1 | 0 |
| Direct dependencies behind a major version | 4 | 4 | 0 |
| Frontend libraries loaded from CDNs | 4 | 4 | 0 |

## Quality

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Automated tests (domain) | 0 | 0 | — |
| Test suite status | 1 of 2 example tests failing | same | green |
| Line coverage | 0% | 0% | ≥ 80% on `app/` |
| Static analysis (Larastan level) | not configured | not configured | ≥ 6 |
| Enforced code style | no | no | Pint in CI |
| CI pipeline | none | none | lint + analyse + test + audit |

## Security and data

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Open audit findings (critical / high) | 3 / 6 | 3 / 6 | 0 / 0 |
| State-changing routes without `auth` (excluding login, sign-up, logout) | 6 | 6 | 0 |
| Raw SQL with interpolated input | 1 | 1 | 0 |
| Domain tables with migrations | 0 of 11 | 0 of 11 | all |
| Business rules in procedures / triggers | 2 / 8 | 2 / 8 | 0 / 0 |
| Fresh clone → running app | not possible | not possible | one command |

## Size

| Metric | v1.0-academic | Current |
|---|---|---|
| PHP lines (`app/`, `routes/`) | 1,745 | 1,745 |
| Blade lines | 2,859 | 2,859 |
| Controllers / empty methods | 12 / 56 | 12 / 56 |
| Routes | 45 | 45 |

## Performance

Measured once the application can run from migrations (Phase 2). Recorded per page: database queries, response time (p50) and payload size.

| Page | v1.0-academic | Current |
|---|---|---|
| Catalog | not measurable¹ | — |
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
