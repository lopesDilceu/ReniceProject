# 0003 — Use English names following Laravel conventions in the schema

- **Status:** Accepted
- **Date:** 2026-10-30
- **Issue:** [#11](https://github.com/lopesDilceu/ReniceProject/issues/11)

## Context

v1 uses Portuguese table names with a per-table column prefix (`us_id`, `pr_nome`, `ve_id_usuario`). Every model overrides `$table` and `$primaryKey`, and every relation passes custom keys. Code and schema mix two languages (`User` model with `us_cpf`; `name` and `email` unprefixed because the auth scaffolding expects them).

No production data exists, and the schema has to be rebuilt as migrations anyway ([ADR 0002](0002-business-rules-in-the-application.md)). This is the cheapest moment a rename will ever have.

## Decision

Use English, snake_case, plural table names and Laravel conventions everywhere: `id` primary keys, `<singular>_id` foreign keys, `created_at` / `updated_at` / `deleted_at`. User-facing text stays in Brazilian Portuguese through localization files.

| v1 | v2 |
|---|---|
| `users.us_id`, `us_cpf`, `us_adm` | `users.id`, `cpf`, `is_admin` (not mass-assignable) |
| `telefones` | `phones` |
| `enderecos` | `addresses` |
| `produtos` | `products` |
| `estoque` | `inventory_movements` (ledger) + cached `products.stock` |
| `compras` | `supplier_purchases` |
| `carrinho`, `itenscarrinho` | `carts`, `cart_items` |
| `vendas`, `itensvenda` | `orders`, `order_items` |
| `avaliacoes` | `reviews` |

Brazil-specific concepts keep their established names (`cpf`, `cep`) because there is no accurate English equivalent.

## Alternatives considered

| Option | Why not |
|---|---|
| Keep v1 names | Ongoing friction with the framework; every new relation needs custom keys |
| Portuguese without prefixes | Removes the friction, but mixes languages with framework code and limits readability for an international audience |

## Consequences

- Models need almost no configuration, and relations use defaults.
- Every query and view is rewritten. That was going to happen anyway with the frontend rewrite ([ADR 0001](0001-frontend-stack-inertia-react.md)).
- The mapping table above is the reference for anyone comparing v1 and v2.
