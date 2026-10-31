# 0002 — Move business rules from the database to the application

- **Status:** Accepted
- **Date:** 2026-10-30
- **Issues:** [#11](https://github.com/lopesDilceu/ReniceProject/issues/11), [#12](https://github.com/lopesDilceu/ReniceProject/issues/12), [#13](https://github.com/lopesDilceu/ReniceProject/issues/13)

## Context

v1 was built for a Database II course, which required stored procedures, triggers and views. As a result, checkout (`finalizar_venda`), stock updates (`atualizar_estoque_produto` and four triggers) and cascading deletes (four triggers) live in MySQL. Those objects were never versioned, so the project cannot run without the original database, which was not preserved. Two of the rules are also wrong ([audit](../audit/initial-audit.md), BUG-01 and BUG-06), and none of them can be tested with the application's test suite.

## Decision

Implement business rules in PHP, in the application layer:

- Checkout becomes a `PlaceOrder` action running in a database transaction with row locks.
- Stock changes become entries in an append-only inventory movements ledger, written in the same transaction as the operation that causes them.
- Cascades become foreign keys with explicit `ON DELETE` behaviour, or soft deletes.
- Reports become Eloquent queries or dedicated query classes.

The database keeps what it is best at: constraints (foreign keys, unique, check, not null) that protect integrity no matter which code path writes.

## Alternatives considered

| Option | Why not |
|---|---|
| Keep procedures and triggers, version them in migrations with `DB::unprepared` | Runs again, but the rules stay MySQL-specific, invisible to the test suite and hard to review; the bugs would still need fixing in SQL |
| Model events / observers instead of explicit actions | Same "hidden side effect" problem as triggers, one layer up |

## Consequences

- Rules are tested with Pest, run on SQLite in tests and on any supported database in production.
- The database is no longer protected against writes that bypass the application. This is accepted for a single-application database, and constraints still cover integrity.
- Concurrency must be handled explicitly (transactions and `lockForUpdate`), which is tested in [#34](https://github.com/lopesDilceu/ReniceProject/issues/34).
- The original SQL remains documented in [`docs/legacy`](../legacy/README.md) as the reference for behaviour that must be preserved.
