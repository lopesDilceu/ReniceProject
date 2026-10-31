# Metrics

Baseline numbers for the modernization, updated at every release. The **v1.0-academic** column is the frozen starting point and never changes.

How each number is collected is in [How to measure](#how-to-measure).

## Platform and dependencies

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Laravel | 11.10.0 (11.57.1 after `composer update`) | 12.70.2 | 13.x |
| PHP constraint | `^8.2` | `^8.2` | `^8.4` |
| Known security advisories (`composer audit`) | 5 | 0 | 0 |
| Abandoned packages | 1 | 1 | 0 |
| Direct dependencies behind a major version | 4 | 5¹ | 0 |
| Frontend libraries loaded from CDNs | 4 | 4 | 0 |

¹ Laravel 13, Tinker 3, Debugbar 4, Pest 5 and its Laravel plugin. Pest 4 was added in Phase 0; Pest 5 needs Laravel 13 (#8, #10).

## Quality

| Metric | v1.0-academic | Current | Target |
|---|---|---|---|
| Automated tests | 0 (2 framework examples) | 19 passing + 12 pending security tests | — |
| Test suite status | 1 of 2 example tests failing | green | green |
| Line coverage on `app/` | 0% | reported by CI | ≥ 80% |
| Static analysis | not configured | Larastan level 6, 159 legacy errors baselined | level 6+, empty baseline |
| Enforced code style | no | Pint (Laravel preset) in CI | Pint in CI |
| CI pipeline | none | lint + analyse + test on PHP 8.4/8.5, dependency audit | lint + analyse + test + audit |

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
| PHP lines (`app/`, `routes/`) | 1,745 | 1,768 (after Pint formatting) |
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
