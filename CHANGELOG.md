# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Changed
- Laravel 12.70 → 13.39 and PHP requirement `^8.2` → `^8.4`.
- Tinker 2 → 3, Debugbar 3 → 4, Pest 4 → 5 (PHPUnit 12 → 13). No direct dependency is behind a major version anymore.
- Replaced the abandoned `yoeunes/toastr` metapackage with the package it wrapped, `php-flasher/flasher-toastr-laravel`. No code change. CI's dependency audit now also fails on abandoned packages.

### Security
- Adopted the Laravel 13 hardening defaults: `cache.serializable_classes = false` and `session.serialization = json`, which remove PHP object deserialization from cache and session reads.

## [2.0.0-alpha.1] — 2026-10-30

End of Phase 0 (Foundation). The application code is still the legacy code. This release adds the documentation, tooling and pipeline that every later change depends on, plus the Laravel 12 upgrade, which had to come first (see #7).

### Added
- Documentation of the original system and its database design ([`docs/legacy`](docs/legacy/README.md)).
- Initial audit with 28 findings and links to their tracking issues ([`docs/audit/initial-audit.md`](docs/audit/initial-audit.md)).
- Baseline metrics ([`docs/metrics.md`](docs/metrics.md)) and phased roadmap ([`docs/roadmap.md`](docs/roadmap.md)).
- Architecture Decision Records 0001–0004.
- Contribution workflow, pull request template, issue forms and security policy.
- Pest 4 test suite: helper characterization tests, public page smoke tests, architecture tests, and pending tests for every audited security finding.
- Larastan at level 6 with a baseline of 159 legacy errors; Pint with the Laravel preset.
- Composer scripts `lint`, `format`, `analyse`, `test`, `test:coverage` and `check`.
- GitHub Actions CI: style, static analysis and tests with coverage on PHP 8.4 and 8.5, plus a dependency audit that fails on known vulnerabilities.

### Changed
- Codebase formatted with Pint (formatting only).
- PHPUnit replaced by Pest 4 (on PHPUnit 12).
- Laravel 11.10 → 12.70; Tinker, Collision and PHPUnit constraints raised to Laravel 12 minimums.
- `config/database.php` uses `Pdo\Mysql::ATTR_SSL_CA` on PHP 8.5+ (the `PDO::MYSQL_*` constants are deprecated there).

### Security
- Upgrading off Laravel 11 resolves 5 framework advisories that have no fix on the 11.x line (TokenGuard authentication bypass, debug page XSS, signed URL path confusion, CRLF injection in the e-mail rule).

## [1.0-academic] — 2024-06-27

Original version delivered as the final project for the Programming II and Database II courses at Unimontes. See the [release notes](https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic) and [`docs/legacy`](docs/legacy/README.md).

[Unreleased]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-alpha.1...HEAD
[2.0.0-alpha.1]: https://github.com/lopesDilceu/ReniceProject/compare/v1.0-academic...v2.0.0-alpha.1
[1.0-academic]: https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic
