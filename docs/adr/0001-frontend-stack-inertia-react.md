# 0001 — Use Inertia, React and TypeScript for the frontend

- **Status:** Accepted
- **Date:** 2026-10-30
- **Issue:** [#27](https://github.com/lopesDilceu/ReniceProject/issues/27)

## Context

v1 renders Blade views with Bootstrap and jQuery loaded from CDNs. Interactivity (cart quantities, masks, modals) is hand-written jQuery mixed into the templates, and several pages preload every record into hidden modals. The views have to be rewritten anyway, because the schema is changing ([ADR 0003](0003-schema-naming-conventions.md)) and most of them carry security or performance problems ([audit](../audit/initial-audit.md)).

This project is also a portfolio piece for a full-stack role, so the frontend has to show modern, typed, component-based work.

## Decision

Use **Inertia.js with React and TypeScript (strict)**, Tailwind CSS and shadcn/ui, following the structure of Laravel's official React starter kit. Routing, authorization and validation stay in Laravel; React only renders pages and handles client interactions.

## Alternatives considered

| Option | Why not |
|---|---|
| Keep Blade and modernize it (Alpine, Tailwind) | Least effort, but shows little frontend skill and keeps logic spread across templates |
| Livewire | Productive and idiomatic Laravel, but the UI is mostly PHP; weaker signal for a React/TypeScript job market |
| Separate SPA (Next.js or Vite) + JSON API | Two deployables, token or cookie auth across origins, duplicated routing and validation. That is more moving parts than the problem needs. A public API is still planned ([#39](https://github.com/lopesDilceu/ReniceProject/issues/39)), but as an addition rather than the UI's only backend |
| Vue | Equivalent technically; React chosen for market demand |

## Consequences

- A single deployable with session authentication, CSRF and server-side validation unchanged.
- Node toolchain in the build and in CI (type check, lint, build).
- jQuery, Bootstrap and the CDN scripts are removed once the last Blade page is gone.
- During the transition Blade and Inertia pages coexist; shared layout and flash messages must work in both until the cut-over.
