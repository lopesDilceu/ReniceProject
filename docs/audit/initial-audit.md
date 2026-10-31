# Initial audit — v1.0-academic

| | |
|---|---|
| **Audited version** | [`v1.0-academic`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic) (commit `c0834e6`, June 2024) |
| **Audit date** | 2026-10-30 |
| **Scope** | Application code, routes, models, views, dependencies and the database design described in the course report |
| **Method** | Manual code review, `composer audit`, `composer outdated`, route inspection, review of the SQL in the Database II report |

All code links point to the frozen tag, so they stay valid while the code changes. Each finding links to the issue that tracks its fix.

## Summary

| Severity | Count | Meaning |
|---|---:|---|
| Critical | 3 | Exploitable by an anonymous visitor, or the project cannot run |
| High | 6 | Exploitable through a user's session, or corrupts business data |
| Medium | 13 | Wrong behaviour, weak design or significant maintenance cost |
| Low | 6 | Hygiene |
| **Total** | **28** | |

The most important conclusions:

1. **An anonymous visitor can become an admin** with one HTTP request ([SEC-01](#sec-01--privilege-escalation-via-mass-assignment)).
2. **The project cannot be set up from the repository.** The domain schema, stored procedures and triggers live only in the PDF report.
3. **Laravel 11 has five known advisories with no fix on the 11.x line.** Upgrading is a security task, not only a cosmetic one.
4. **Business rules sit in the database** (triggers and procedures) and two of them are wrong: purchases overwrite the sale price, and the best-sellers view counts rows instead of units.

## Security

### SEC-01 — Privilege escalation via mass assignment
**Critical** · [#17](https://github.com/lopesDilceu/ReniceProject/issues/17)

`PUT /usuarios/edit/{us_id}` has no `auth` middleware and no ownership check ([web.php#L65](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/routes/web.php#L65)). The controller calls `$user->fill($request->except('us_foto'))` ([UserController.php#L176](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/app/Http/Controllers/UserController.php#L176)) and `us_adm` is in `$fillable` ([User.php#L28](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/app/Models/User.php#L28)).

Sending `us_adm=1` with any user id grants admin rights. The same request can change anyone's e-mail or password.

### SEC-02 — SQL injection in checkout
**Critical** · [#18](https://github.com/lopesDilceu/ReniceProject/issues/18)

`DB::statement("CALL finalizar_venda($id)")` interpolates a route parameter into SQL ([ItensCarrinhoController.php#L164](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/app/Http/Controllers/ItensCarrinhoController.php#L164)). The route has no constraint and no authentication.

### SEC-03 — IDOR on cart items
**High** · [#19](https://github.com/lopesDilceu/ReniceProject/issues/19)

`PATCH /carrinho/{id}` and `DELETE /usuarios/carrinho/{ic_id}` do not require login and do not check that the item belongs to the caller ([web.php#L57](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/routes/web.php#L57), [#L132](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/routes/web.php#L132)). Quantity is not validated: zero, negative and above-stock values are accepted.

### SEC-04 — Order placement through a GET request
**High** · [#20](https://github.com/lopesDilceu/ReniceProject/issues/20)

`GET /usuarios/finalizar/{id}` places an order ([web.php#L59](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/routes/web.php#L59)). GET is not CSRF-protected, so any page can trigger it for a logged-in user (for example with an `<img>` tag).

### SEC-05 — Unauthenticated, unvalidated reviews
**Medium** · [#21](https://github.com/lopesDilceu/ReniceProject/issues/21)

`POST /salvar-avaliacao` ([ProdutoController.php#L89](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/app/Http/Controllers/ProdutoController.php#L89)) does not require login, does not validate the rating and does not check that the reviewer bought the product.

### SEC-06 — Weak authentication flow
**Medium** · [#22](https://github.com/lopesDilceu/ReniceProject/issues/22)

Login has no rate limiting and does not regenerate the session (session fixation). Logout does not invalidate the session ([UserController.php#L204-L228](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/app/Http/Controllers/UserController.php#L204-L228)). There is no password reset or e-mail verification.

### SEC-07 — Framework with unpatched advisories
**High** · [#7](https://github.com/lopesDilceu/ReniceProject/issues/7)

`composer audit` reports five advisories against `laravel/framework` 11.x: TokenGuard authentication bypass, XSS in the debug page, temporary signed URL path confusion and CRLF injection in the e-mail rule (two entries). None is fixed on the 11.x line.

## Correctness

### BUG-01 — Supplier purchase overwrites the sale price
**High** · [#14](https://github.com/lopesDilceu/ReniceProject/issues/14)

Trigger `after_compra_insert` sets `pr_preco = co_preco_unitario`. Every restock makes the store sell at cost price.

### BUG-02 — Checkout without transaction or pre-checks
**High** · [#13](https://github.com/lopesDilceu/ReniceProject/issues/13)

Procedure `finalizar_venda` does not open a transaction, accepts empty carts (total `NULL`) and only learns about missing stock when a trigger fails halfway. Every order is created as *Concluída* (completed) without a payment step.

### BUG-03 — Cart total includes every user's cart
**High** · [#23](https://github.com/lopesDilceu/ReniceProject/issues/23)

The total is computed over `ItensCarrinho::all()` ([ItensCarrinhoController.php#L129](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/app/Http/Controllers/ItensCarrinhoController.php#L129)).

### BUG-04 — Product creation ignores the price
**Medium** · [#25](https://github.com/lopesDilceu/ReniceProject/issues/25)

Price validation is commented out and the field is not saved ([ProdutoController.php#L44-L66](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/app/Http/Controllers/ProdutoController.php#L44-L66)). The v1 screenshots show products listed at R$ 0,00.

### BUG-05 — Sign-up crashes without a photo
**Medium** · [#24](https://github.com/lopesDilceu/ReniceProject/issues/24)

The upload runs before validation and assumes a file is present ([UserController.php#L89](https://github.com/lopesDilceu/ReniceProject/blob/v1.0-academic/app/Http/Controllers/UserController.php#L89)). The stored path is never saved on the user.

### BUG-06 — Best-sellers view counts rows, not units
**Medium** · [#15](https://github.com/lopesDilceu/ReniceProject/issues/15)

`vw_ProdutosMaisVendidos` uses `COUNT(*)` over order items.

## Data model

The relational design from the Database II report is mostly sound: foreign keys everywhere, a `CHECK` on the rating, and unit price snapshotted on order items. These parts are kept. The following are changed in [#11](https://github.com/lopesDilceu/ReniceProject/issues/11) and [#12](https://github.com/lopesDilceu/ReniceProject/issues/12):

| ID | Finding | Severity | Decision |
|---|---|---|---|
| DM-01 | Domain schema, 2 procedures, 8 triggers and 2 views are not versioned; only the PDF describes them | Critical | Rebuild as migrations; move rules to the application ([ADR 0002](../adr/0002-business-rules-in-the-application.md)) |
| DM-02 | `Telefones` uses a composite primary key, which Eloquent does not support (the model declares an array key) | Medium | Surrogate `id` plus unique index |
| DM-03 | `en_numero INT(5)` rejects "S/N" or "12A"; `en_estado VARCHAR(100)` for a two-letter code | Low | String number; `char(2)` state |
| DM-04 | No unique constraint on CPF, on the user's cart or on one review per user per product | Medium | Add unique indexes |
| DM-05 | `Estoque.es_nome_produto` duplicates the product name and is synchronised by a trigger | Low | Drop the column |
| DM-06 | Stock is a single mutable number with no history | Medium | Inventory movements ledger |
| DM-07 | Delete triggers remove purchase history (`before_produto_delete_compras`) | Medium | FKs with explicit `ON DELETE` behaviour; soft deletes for products |
| DM-08 | `us_data_criacao`, `pr_data_criacao` etc. duplicate `created_at` | Low | Use Laravel timestamps |
| DM-09 | Prefixed Portuguese column names (`us_`, `pr_`, `ve_`) force custom keys on every relation | Low | English names following Laravel conventions ([ADR 0003](../adr/0003-schema-naming-conventions.md)) |

## Maintainability and performance

| ID | Finding | Severity | Issue |
|---|---|---|---|
| MNT-01 | Controllers load whole tables (`Produto::all()`, `User::all()`, `ItensVenda::all()`) and views join them with nested `@foreach` loops, giving O(n·m) work and N+1 queries | Medium | [#26](https://github.com/lopesDilceu/ReniceProject/issues/26) |
| MNT-02 | No tests beyond the framework examples, which fail | Medium | [#4](https://github.com/lopesDilceu/ReniceProject/issues/4) |
| MNT-03 | No static analysis, no enforced code style, no CI | Medium | [#3](https://github.com/lopesDilceu/ReniceProject/issues/3), [#5](https://github.com/lopesDilceu/ReniceProject/issues/5) |
| MNT-04 | 56 empty resource-controller methods; 8 routes declared twice, once as a closure and once with a controller (e.g. `/usuarios/carrinho`, `/adm/produtos`) | Low | [#31](https://github.com/lopesDilceu/ReniceProject/issues/31) |
| MNT-05 | Validation inline in controllers; no Form Requests or Policies | Medium | [#31](https://github.com/lopesDilceu/ReniceProject/issues/31) |
| MNT-06 | Abandoned dependency `yoeunes/toastr`; frontend libraries loaded from CDNs (Bootstrap, jQuery, jQuery Mask, MaskMoney) | Low | [#9](https://github.com/lopesDilceu/ReniceProject/issues/9), [#27](https://github.com/lopesDilceu/ReniceProject/issues/27) |

## What was done well

These deserve credit and are carried forward:

- A complete, normalized relational model with referential integrity, designed and documented with a data dictionary.
- Unit price copied into order items at purchase time, so historical orders do not change when prices do.
- Soft deletes on products and users, preserving order history.
- A cohesive visual identity and a complete customer journey (catalog → cart → checkout → order history → review), plus a full admin back office.
