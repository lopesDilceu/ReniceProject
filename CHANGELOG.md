# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- Inertia 3 + React 19 + TypeScript (strict) frontend, following Laravel's official React starter kit: Tailwind 4, shadcn/ui components, React Compiler, Vite+ (build, lint, format), Wayfinder typed route helpers, light/dark/system appearance.
- Store layout (header, mobile menu, user menu, theme switcher, footer) in the Renice brand color. Links to pages still served by v1 Blade views use full page loads until each module is rewritten.
- Home and "Sobre o projeto" rewritten as React pages; the about page tells the modernization story and links to the audit, roadmap, ADRs and metrics.
- CI job: frontend build, lint/format and type check.
- Authentication with Laravel Fortify and React pages: sign in (5 attempts per minute per e-mail and IP), sign up, forgot/reset password, e-mail verification (Mailpit locally) and password confirmation. Sign-up asks for name, e-mail, CPF (validated by check digits, masked as typed) and birth date (16+).
- `Cpf` validation rule and pt-BR translations for validation, auth and password messages.
- Catalog in React (`/produtos`): search across name and description, sorting (featured, price, rating, newest), "available only" filter, 12 per page, branded image placeholder, sold-out and low-stock badges. Exact stock is never sent to the browser.
- Product page (`/produtos/{slug}`): price, availability, description, rating summary with distribution and the latest reviews (author shown as first name and last initial).
- `Model::preventLazyLoading()` outside production, so N+1 queries fail in tests.
- Cart in React (`/carrinho`): add from the product page, quantity steppers capped at stock, remove, totals; header badge with the number of units.
- Admin area in React (`/admin`, `admin` gate): products with price in reais, image upload, stable unique slugs, soft delete and restore; per-product stock history with manual adjustments that require a reason; supplier purchases that restock without touching the sale price.
- Account area (`/conta`): personal data (CPF shown masked and not editable), photo upload, phones, addresses and password change; changing the e-mail asks for confirmation again.
- Order history (`/conta/pedidos`) and reviews from the order page, for delivered products only, one per product.
- Checkout (`/checkout`, requires a confirmed e-mail): saved addresses or a new one with CEP lookup (ViaCEP), order through `PlaceOrder`, order page (`/pedidos/{id}`) visible to its owner and admins.

### Changed
- Hero image re-encoded from a 5.9 MB PNG to a 342 KB WebP.
- Shared Inertia props expose only non-sensitive user fields (no CPF, birth date or addresses).
- v1 URLs `/usuarios/login` and `/usuarios/cadastro` redirect permanently to `/login` and `/register`.
- v1 `/produtos/pesquisa?query=…` redirects to `/produtos?q=…`; `/avaliacoes` redirects to the catalog (reviews now live on each product page).
- v1 `/usuarios/carrinho` and `/usuarios/pagamento` redirect to `/carrinho` and `/checkout`.
- v1 `/usuarios/minha-conta`, `/usuarios/editar-dados` and `/usuarios/minhas-compras` redirect to the account area.
- A test fails if the Larastan baseline ever includes a non-legacy file, so regenerating it cannot hide errors in new code.
- Production password policy: 12+ characters, mixed case, numbers, symbols and a breach check; destructive database commands are blocked in production.

### Removed
- v1 Blade home and about views, the unused Laravel welcome view and the unused axios bootstrap.
- v1 login, logout and sign-up (controller methods and Blade views), which had no rate limiting, no session regeneration and no password reset (SEC-06), and crashed on sign-up without a photo (BUG-05).
- v1 catalog, search and reviews pages (`indexHome`, `pesquisa`, `AvaliacaoController@index` and their Blade views), which loaded every product and stock row and matched them in nested loops.
- v1 cart, payment and checkout (`ItensCarrinhoController`, `CarrinhoController` and their Blade views), including `GET /usuarios/finalizar/{cart}`, which interpolated the cart id into `CALL finalizar_venda(...)`.
- v1 profile, purchase history and review pages, including the unauthenticated `PUT /usuarios/edit/{id}` and `POST /salvar-avaliacao`.

### Security
- Login throttling, session regeneration on login, session invalidation and CSRF token rotation on logout (SEC-06, with regression tests).
- SQL injection in checkout fixed: no route takes a cart id; the cart comes from the signed-in user (SEC-02, critical).
- Cart items are authorized by a policy, and quantities are validated against stock (SEC-03).
- Orders are placed only by a CSRF-protected POST; a double submit creates one order (SEC-04).
- Cart total counts only the customer's own items (BUG-03).
- Privilege escalation fixed: no route takes a user id, and the profile form can change only name, e-mail and birth date (SEC-01, critical).
- Reviews only from customers who received the product, once per product, rating 1–5 (SEC-05).
- Product creation requires a valid price (BUG-04: v1 never saved it).

## [2.0.0-alpha.3] — 2026-10-31

End of Phase 2 (Data layer redesign).

### Added
- [ADR 0005](docs/adr/0005-postgresql.md): PostgreSQL 18 with pgvector as the database, chosen for Laravel's native vector search and Laravel Cloud's serverless Postgres.
- Docker development environment on Laravel Sail (`compose.yaml`): PHP 8.5, PostgreSQL 18 + pgvector, Mailpit.
- Inventory movements ledger: append-only history of every stock change with signed quantity, running balance, source and actor; `RecordInventoryMovement` locks the product row and refuses negative stock. Replaces v1's `atualizar_estoque_produto` procedure and its triggers.
- `PlaceOrder`: turns the cart into a pending order in one transaction. Checks cart, address ownership and stock before writing; locks cart and products (in id order, avoiding deadlocks); snapshots name, price and address; takes stock out through the ledger; empties the cart. Replaces v1's `finalizar_venda` procedure (fixes BUG-02: no transaction, empty carts accepted, orders marked completed without payment).
- `BestSellingProducts` report: ranks by units sold, then revenue at the price charged, counting only paid, shipped and delivered orders. Fixes BUG-06: v1's `vw_ProdutosMaisVendidos` counted order lines (`COUNT(*)`) instead of units.
- Demo seeder: the original five products, admin and customer demo accounts, nine customers with addresses and phones, about twenty orders across all statuses, stock history and reviews. Built through the domain actions (`RecordSupplierPurchase`, `PlaceOrder`).
- `RecordSupplierPurchase`: restocks through the ledger and records unit cost without touching the sale price (fixes BUG-01, where every restock overwrote the price with cost).
- Versioned domain schema: users, phones, addresses, products, supplier purchases, carts, orders and reviews, in English with Laravel conventions. Money in integer cents, order snapshots (product name, unit price, shipping address), unique and foreign-key rules, and PostgreSQL CHECK constraints. Documented with an ER diagram in [`docs/database.md`](docs/database.md).
- Eloquent models, factories and the `OrderStatus` enum for the new schema.
- Schema tests (factories, uniqueness, snapshots, delete restrictions, cart totals scoped to one cart) and PostgreSQL-only constraint tests.
- CI job running migrations (up, down, up) and the full suite on PostgreSQL 18 + pgvector.

### Changed
- `.env.example` targets the Sail services and the Brazilian locale (`pt_BR`, also for Faker).
- Framework `cache` and `jobs` migrations restored (they were commented out in v1).
- The legacy admin middleware and layout read `is_admin` / `avatar_path` from the new users table.

### Removed
- Legacy migrations that altered tables never created in the repository (`produtos`, and the separate `users` soft-delete migration).

## [2.0.0-alpha.2] — 2026-10-30

End of Phase 1 (Framework upgrade). The application code is still the legacy code; the platform underneath is now current.

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

[Unreleased]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-alpha.3...HEAD
[2.0.0-alpha.3]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-alpha.2...v2.0.0-alpha.3
[2.0.0-alpha.2]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-alpha.1...v2.0.0-alpha.2
[2.0.0-alpha.1]: https://github.com/lopesDilceu/ReniceProject/compare/v1.0-academic...v2.0.0-alpha.1
[1.0-academic]: https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic
