# 0004 — Modernize in place and rewrite module by module

- **Status:** Accepted
- **Date:** 2026-10-30
- **Issue:** [#1](https://github.com/lopesDilceu/ReniceProject/issues/1)

## Context

The goal is to bring a 2024 coursework project to a 2026 production-grade standard and to make the journey itself visible. The audit found that most controllers and views must change: the schema is being redesigned and the UI is moving to React. The legacy code also has security issues that are cheaper to remove than to patch.

## Decision

1. **Same repository, history preserved.** The original state is frozen at the `v1.0-academic` tag, and all work happens through pull requests on `main`.
2. **Platform first, then features.** Tooling and CI (Phase 0) and the framework upgrade (Phase 1) come before any functional change, so every later change is tested and analysed.
3. **Data layer before UI.** The schema and business rules (Phase 2) are rebuilt before the screens that depend on them.
4. **Module-by-module rewrite.** Storefront, checkout, account and admin are rewritten one at a time (Phase 3). Each module PR removes the matching legacy controllers and views, and closes the related audit findings with regression tests.
5. **No patching of code that is about to be deleted.** Security findings in the legacy controllers are fixed by their replacement, and the regression test proves the vulnerability is gone. The application is not deployed while legacy code is live.

## Alternatives considered

| Option | Why not |
|---|---|
| New repository from a fresh starter kit | Loses the before/after story and the commit history that shows the process |
| Patch every legacy finding first, then rewrite | Double work on code with a short remaining life |
| Big-bang rewrite in one branch | Unreviewable, and nothing ships until everything ships |

## Consequences

- For a while, Blade and Inertia pages coexist.
- Audit findings stay open until their module is rewritten. The roadmap makes the order explicit.
- Each phase ends with a pre-release and an update to [`docs/metrics.md`](../metrics.md).
