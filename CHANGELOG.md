# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- Documentation of the original system and its database design ([`docs/legacy`](docs/legacy/README.md)).
- Initial audit with 28 findings and links to their tracking issues ([`docs/audit/initial-audit.md`](docs/audit/initial-audit.md)).
- Baseline metrics ([`docs/metrics.md`](docs/metrics.md)) and phased roadmap ([`docs/roadmap.md`](docs/roadmap.md)).
- Architecture Decision Records 0001–0004.
- Contribution workflow, pull request template, issue forms and security policy.

### Changed
- Laravel 11.10 → 12.70; Tinker, Collision and PHPUnit constraints raised to Laravel 12 minimums.
- `config/database.php` uses `Pdo\Mysql::ATTR_SSL_CA` on PHP 8.5+ (the `PDO::MYSQL_*` constants are deprecated there).

### Security
- Upgrading off Laravel 11 resolves 5 framework advisories that have no fix on the 11.x line (TokenGuard authentication bypass, debug page XSS, signed URL path confusion, CRLF injection in the e-mail rule).

## [1.0-academic] — 2024-06-27

Original version delivered as the final project for the Programming II and Database II courses at Unimontes. See the [release notes](https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic) and [`docs/legacy`](docs/legacy/README.md).

[Unreleased]: https://github.com/lopesDilceu/ReniceProject/compare/v1.0-academic...HEAD
[1.0-academic]: https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic
