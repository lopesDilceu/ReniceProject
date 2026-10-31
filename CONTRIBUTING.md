# Contributing

This is a personal portfolio project, but it follows the workflow of a team project so the history is easy to follow.

## Quality checks

```bash
composer check          # everything CI runs: lint, analyse, test
composer format         # fix code style
composer test:coverage  # tests with coverage (needs pcov or Xdebug)
```

Larastan runs at level 6. Legacy errors live in `phpstan-baseline.neon`. New code must not add entries. When a rewrite removes legacy code, regenerate the baseline so it only shrinks:

```bash
vendor/bin/phpstan analyse --memory-limit=1G --generate-baseline
```

## Workflow

1. Every change starts from an **issue** in a [milestone](https://github.com/lopesDilceu/ReniceProject/milestones).
2. Create a **branch** from `main` named `<type>/<short-description>`, e.g. `fix/cart-idor`, `feat/pix-payments`, `docs/initial-audit`.
3. Open a **pull request** using the template. Link the issue with `Closes #N`.
4. CI must pass. PRs are **squash-merged**, so the PR title becomes the commit on `main` and must follow Conventional Commits.

## Commit messages

[Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <summary in the imperative>
```

| Type | Use for |
|---|---|
| `feat` | New behaviour |
| `fix` | Bug or security fix |
| `refactor` | Code change with no behaviour change |
| `test` | Tests only |
| `docs` | Documentation and ADRs |
| `build` | Dependencies, upgrades, tooling |
| `ci` | Pipeline |
| `chore` | Anything else |

Scopes follow the modules: `auth`, `catalog`, `cart`, `checkout`, `orders`, `inventory`, `admin`, `account`, `reviews`, `payments`, `search`, `api`, `db`, `deps`.

## Decisions

When a change has real alternatives and would be expensive to reverse, write an [ADR](docs/adr/README.md) in the same PR.

## Security fixes

Write the test that reproduces the issue first, watch it fail, then fix. The test stays as a regression guard.

## Releases

Each phase ends with a pre-release tag (`v2.0.0-alpha.N`, `-beta.N`, `-rc.N`). Before tagging:

1. Update [`CHANGELOG.md`](CHANGELOG.md): move *Unreleased* entries under the new version.
2. Update the *Current* column in [`docs/metrics.md`](docs/metrics.md).
3. Tag on `main` and publish the GitHub release with the changelog section as notes.
