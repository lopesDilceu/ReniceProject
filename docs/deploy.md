# Deploy

There is no public instance: hosting a demo costs money every month, so the project stops at a deployable image. To run that image locally with the demo data, use [`compose.demo.yaml`](../compose.demo.yaml) ([showcase](showcase.md#run-it-yourself)). Everything below is what a real deploy takes.

The app ships as **one Docker image** ([`Dockerfile`](../Dockerfile)), run as four processes:

| Role | Command | Notes |
|---|---|---|
| web | (default) `apache2-foreground` | Port **8080**, unprivileged. Set `RUN_MIGRATIONS=true` on this one only |
| queue | `php artisan queue:work --tries=3` | E-mails, payment notifications, broadcasts, embeddings, review summaries |
| scheduler | `php artisan schedule:work` | Expires unpaid orders, writes the health heartbeat, resets the demo nightly |
| reverb | `php artisan reverb:start --host=0.0.0.0 --port=8080` | WebSockets. Optional: without it, pages poll |

Every role caches config, routes, views and events from the real environment on start (`php artisan optimize`, in [`docker/entrypoint.sh`](../docker/entrypoint.sh)). Changing an environment variable therefore means restarting the containers.

## From `main` to production

[`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml) runs **after CI passes on `main`**:

1. It builds the image and pushes `ghcr.io/lopesdilceu/reniceproject:<sha>` and `:latest` to the GitHub Container Registry, using the workflow's own token, so no extra account is needed.
2. If the repository secret **`DEPLOY_HOOK_URL`** is set, it POSTs to it. Laravel Cloud, Render, Railway, Coolify and similar platforms give one per service, and it tells the platform to pull the new image.

The `Production image builds` CI job builds the same Dockerfile on every pull request and boots it with production settings. A broken image fails the PR, not the deploy.

**Build-time values** are repository *variables*, because Vite compiles them into the JavaScript: `VITE_REVERB_APP_KEY`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT`, `VITE_REVERB_SCHEME`.

## Environment

The minimum for production:

```dotenv
APP_NAME=Renice
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...            # php artisan key:generate --show
APP_URL=https://renice.example
TRUSTED_PROXIES=*             # TLS ends at the platform's proxy: https and signed URLs need it

DB_CONNECTION=pgsql           # PostgreSQL 16+ with the pgvector extension
DB_HOST=... DB_DATABASE=... DB_USERNAME=... DB_PASSWORD=...

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
LOG_CHANNEL=json              # one JSON line per entry on stderr, with request ids

MAIL_MAILER=smtp              # plus MAIL_* for a real provider
```

Optional, each feature switched on by its own variables:

| Feature | Variables | Guide |
|---|---|---|
| Pix | `PAYMENT_GATEWAY=mercadopago`, `MERCADOPAGO_ACCESS_TOKEN`, `MERCADOPAGO_WEBHOOK_SECRET` | [payments](payments.md) |
| Realtime | `BROADCAST_CONNECTION=reverb`, `REVERB_*` (server), `VITE_REVERB_*` (build) | README |
| Semantic search | `SEARCH_EMBEDDINGS_PROVIDER`, provider key | [search](search.md) |
| Review summaries | `REVIEW_SUMMARY_PROVIDER`, provider key | [review summaries](review-summaries.md) |

## The public demo

Set `DEMO_MODE=true`, and keep `PAYMENT_GATEWAY=fake` or point Mercado Pago at its sandbox. Then:

- Every page shows a banner saying it is a demo, that data resets nightly and that payments are simulated. The login page lists the demo accounts.
- **`php artisan demo:reset`** runs at 04:00 São Paulo time (`DEMO_RESET_AT`). It rebuilds the database from the demo seed and deletes uploaded files, which undoes whatever visitors changed. Run it once by hand after the first deploy to load the demo data.
- `demo:reset` refuses to run without `DEMO_MODE=true`. In production, destructive database commands stay blocked unless the app is the demo.

## Checks after a deploy

- `GET /health` should report `ok`. `degraded` means a worker or the scheduler is not running ([observability](observability.md)).
- `/pulse` (admins) shows errors and slow requests.

## Verified locally

The image was built and run against the PostgreSQL container with `APP_ENV=production`, `LOG_CHANNEL=json` and `DEMO_MODE=true`:

- the config caches;
- migrations run;
- `demo:reset` seeds the store;
- `/`, `/produtos`, `/login`, `/docs/api.json` and `/health` answer 200, and `/admin` redirects to the login;
- the API lists the 5 demo products;
- the `Server` header no longer shows the Apache version.

This run caught two deploy blockers that the test suite could not see, both fixed:

- **an object in `config/scramble.php`** made `config:cache` fail (`ConfigurationCacheTest` now guards against it);
- **the demo seed needs Faker**, which was a dev-only dependency.
