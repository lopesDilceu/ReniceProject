# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- **Passkeys** (WebAuthn), through Fortify's passkey feature and `@laravel/passkeys`:
  - sign in with a passkey from the login page;
  - confirm the password with a passkey;
  - add and remove passkeys under "Minha conta → Segurança", with the authenticator's name and last use.
- `/.well-known/passkey-endpoints`, so password managers can link to that page.
- **Two-factor authentication** with TOTP:
  - Set up by scanning a QR code; it is enabled only after the first valid code. A setup that is never confirmed is discarded.
  - Eight one-time recovery codes, which can be viewed and regenerated.
  - Signing in with a password asks for the code, or for a recovery code.
- **Rate limits:** 5 two-factor codes per minute per account being signed in, and 10 passkey ceremonies per minute per credential and IP.
- **Password confirmation** is required to open the security page and to change two-factor or passkeys.
- pt-BR messages for the two-factor and passkey errors.

### Changed
- Closing an account (soft delete) deletes its passkeys, so it keeps no working credentials.

## [2.0.0-beta.2] — 2026-10-31

End of Phase 4 (Commerce core). The store takes Pix payments through Mercado Pago, with signed and idempotent webhooks. Orders follow an audited lifecycle, stock is reserved at checkout and cannot be oversold under concurrency, and customers and admins get queued e-mails.

### Added
- Order timeline: every status change is recorded in the append-only `order_status_changes` table, with the previous status, the actor (or the system) and a reason. Placing an order writes the first entry. Admins see who made each change; customers see when and why, under "Acompanhamento" on the order page.
- Cancelling or refunding an order requires a reason.
- An invalid transition throws `InvalidOrderTransition`.
- Stock reservation:
  - Checkout reserves the units until `orders.payment_due_at`, which is `SHOP_PAYMENT_WINDOW_MINUTES` after the order (60 by default).
  - `orders:expire-unpaid` runs every minute on the scheduler. It cancels overdue orders as the system and releases their stock.
  - The customer's order page says until when the items are reserved.
- Ledger movement types `reservation`, `release` (cancellation or expiry) and `return` (refund). They replace `sale`, and the `adjustment` that cancellations used to write.
- Transactional e-mails in Portuguese, through queued notifications (5 tries, backoff of 10 s, 1 min and 5 min). They go out only after the transaction commits, so a rolled-back change never e-mails anyone:
  - The customer gets an e-mail when the order is placed (with the reservation deadline in the store's timezone), paid, shipped (with the delivery address) and cancelled (with the reason).
  - Admins get a low-stock alert when a product drops to `SHOP_LOW_STOCK_THRESHOLD` units (5 by default). It is sent once per crossing; a restock arms it again. The admin dashboard uses the same threshold.
- Pix payments through Mercado Pago ([ADR 0006](docs/adr/0006-pix-with-mercado-pago.md), [`docs/payments.md`](docs/payments.md)):
  - **Checkout:** creates the charge with an idempotency key, one per order. The order page shows the QR code and the "copia e cola", and reloads every 5 s until the payment is confirmed. If the provider is down, the order is kept and the customer can generate the Pix again ("Gerar Pix").
  - **Webhook:** `POST /webhooks/mercadopago` verifies the HMAC-SHA256 signature (401 otherwise) and stores each notification once (`webhook_calls`, `ON CONFLICT DO NOTHING`). A queued job then reads the status back from the API instead of trusting the body.
  - **Effect on the order:** an approved Pix marks the order paid, by the system. A refund in the provider panel marks it refunded and returns the stock. Cancelling or expiring an order cancels its charge at the provider. A payment that arrives after expiry is recorded and flagged for refund.
  - **Admin:** the admin order page shows the payment.
  - **Local fake gateway:** the default (`PAYMENT_GATEWAY=fake`). It makes real QR codes and adds a "Simular pagamento" button, so the store and the demo run without an account. The demo seed pays its orders through it.
- `OrderStatusChanged` event, fired for every timeline entry. The `EmailCustomerAboutOrder` listener picks which e-mail to send.
- Concurrency test on PostgreSQL: eight PHP processes check out the last unit at the same instant, and exactly one succeeds. With the row locks removed, the test sold that unit three times.

### Changed
- `ChangeOrderStatus` is now the only way an order's status changes. The demo seeder takes each order through it with realistic dates, so seeded cancellations now put stock back too. Seeded orders that still await payment are recent, so they sit inside the payment window. The seed renders its e-mails but sends none, and queues none for Mailpit.
- The e-mail sender in `.env.example` is `loja@renice.test`.
- The default payment window is 60 minutes. Mercado Pago requires at least 30 minutes for a Pix expiration.
- Tests refuse any HTTP request that is not faked (`Http::preventStrayRequests`), and always run with the fake payment gateway.

## [2.0.0-beta.1] — 2026-10-31

End of Phase 3 (Inertia + React rewrite). Every screen of the store and the admin is rewritten in React on Inertia 3, all v1 code is gone, and every finding of the initial audit is resolved with a regression test.

### Added
- Inertia 3 + React 19 + TypeScript (strict) frontend, following Laravel's official React starter kit: Tailwind 4, shadcn/ui components, React Compiler, Vite+ (build, lint, format), Wayfinder typed route helpers, light/dark/system appearance.
- Store layout (header, mobile menu, user menu, theme switcher, footer) in the Renice brand color.
- Home and "Sobre o projeto" rewritten as React pages; the about page tells the modernization story and links to the audit, roadmap, ADRs and metrics.
- CI job: frontend build, lint/format and type check.
- Authentication with Laravel Fortify and React pages: sign in (5 attempts per minute per e-mail and IP), sign up, forgot/reset password, e-mail verification (Mailpit locally) and password confirmation. Sign-up asks for name, e-mail, CPF (validated by check digits, masked as typed) and birth date (16+).
- `Cpf` validation rule and pt-BR translations for validation, auth and password messages.
- Catalog in React (`/produtos`): search across name and description, sorting (featured, price, rating, newest), "available only" filter, 12 per page, branded image placeholder, sold-out and low-stock badges. Exact stock is never sent to the browser.
- Product page (`/produtos/{slug}`): price, availability, description, rating summary with distribution and the latest reviews (author shown as first name and last initial).
- `Model::preventLazyLoading()` outside production, so N+1 queries fail in tests.
- Cart in React (`/carrinho`): add from the product page, quantity steppers capped at stock, remove, totals; header badge with the number of units.
- Admin area in React (`/admin`, `admin` gate): products with price in reais, image upload, stable unique slugs, soft delete and restore; per-product stock history with manual adjustments that require a reason; supplier purchases that restock without touching the sale price.
- Admin dashboard (revenue and orders for the last 30 days against the previous 30, orders to ship, low stock, best sellers, recent orders), orders with status transitions (cancel or refund restocks through the ledger), customers with an explicit grant/remove admin action (never on yourself) and review moderation.
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
- Larastan level 6 runs with no baseline; a test fails if a baseline or ignored errors are added back.
- Production password policy: 12+ characters, mixed case, numbers, symbols and a breach check; destructive database commands are blocked in production.

### Removed
- v1 Blade home and about views, the unused Laravel welcome view and the unused axios bootstrap.
- v1 login, logout and sign-up (controller methods and Blade views), which had no rate limiting, no session regeneration and no password reset (SEC-06), and crashed on sign-up without a photo (BUG-05).
- v1 catalog, search and reviews pages (`indexHome`, `pesquisa`, `AvaliacaoController@index` and their Blade views), which loaded every product and stock row and matched them in nested loops.
- v1 cart, payment and checkout (`ItensCarrinhoController`, `CarrinhoController` and their Blade views), including `GET /usuarios/finalizar/{cart}`, which interpolated the cart id into `CALL finalizar_venda(...)`.
- v1 profile, purchase history and review pages, including the unauthenticated `PUT /usuarios/edit/{id}` and `POST /salvar-avaliacao`.
- All remaining v1 code: admin controllers and Blade views, the ten v1 models, the `CheckAdmin` middleware, `app/helpers.php`, jQuery, Bootstrap and every CDN script, the `php-flasher` package, unused images, and the Larastan baseline (static analysis now ignores nothing). `/adm/*` redirects to `/admin`.

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

[Unreleased]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-beta.2...HEAD
[2.0.0-beta.2]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-beta.1...v2.0.0-beta.2
[2.0.0-beta.1]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-alpha.3...v2.0.0-beta.1
[2.0.0-alpha.3]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-alpha.2...v2.0.0-alpha.3
[2.0.0-alpha.2]: https://github.com/lopesDilceu/ReniceProject/compare/v2.0.0-alpha.1...v2.0.0-alpha.2
[2.0.0-alpha.1]: https://github.com/lopesDilceu/ReniceProject/compare/v1.0-academic...v2.0.0-alpha.1
[1.0-academic]: https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic
