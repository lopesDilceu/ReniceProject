# Observability

## Health

**`GET /health`** is for uptime monitors and load balancers. It runs outside the web middleware group, so it sets no session cookie, and it is limited to 60 requests per minute.

```json
{
  "status": "degraded",
  "checks": {
    "database": { "status": "ok", "latency_ms": 2.1 },
    "cache": { "status": "ok", "latency_ms": 0.8 },
    "queue": { "status": "ok", "pending": 0, "oldest_pending_seconds": 0, "failed": 0 },
    "scheduler": { "status": "degraded", "last_run_seconds_ago": null }
  }
}
```

| Status | When | HTTP |
|---|---|---|
| `ok` | everything works | 200 |
| `degraded` | requests are served, but background work is late: a job waiting more than 5 minutes (no worker), or no scheduler heartbeat in 150 seconds | 200 |
| `fail` | the database or the cache is down | **503** |

- **Scheduler:** a scheduled task writes a heartbeat to the cache every minute. That is how `/health` knows the scheduler is running.
- **Failure details** (exception messages) go to the logs, never into the public response.
- **`/up`:** Laravel's own endpoint now also fails when the database is down. Before, it answered 200 as long as PHP ran.

## Logs

- **Request id:** every response carries an **`X-Request-Id`**. An incoming one is kept if it looks like an id: 8–64 characters from `A–Z a–z 0–9 . _ -`. Anything else is replaced, so it cannot inject into logs.
- **Context:** the id is added to Laravel's `Context`, so every log line of the request carries it. **Queued jobs dispatched by the request carry it too**: a Pix webhook, the job that processes it and the e-mail it sends share one id.
- **JSON channel:** `LOG_CHANNEL=json` writes one JSON object per line to stderr, for containers and log collectors. The request id is under `extra.request_id`:

```json
{"message":"Pix webhook rejected","context":{"provider":"mercadopago"},"level_name":"WARNING","channel":"production","datetime":"2026-11-01T12:00:00+00:00","extra":{"request_id":"01a1974b-8c23-71b0-92d1-8d8f7c9d7503"}}
```

Locally, the default `stack`/`single` channel is unchanged (`storage/logs/laravel.log`).

## Metrics and errors: Laravel Pulse

**`/pulse`** is open to admins only (`viewPulse` gate; it is linked as "Monitoramento" in the admin menu). It shows:

- slow requests, slow queries, slow jobs and slow outgoing requests (for example, to Mercado Pago or the AI providers);
- **exceptions**, grouped and counted;
- queue throughput;
- cache hit rate;
- the busiest users.

Data is stored in the application's own database (the `pulse_*` tables) and is trimmed automatically. `/health` and `/up` are excluded from request metrics, because monitors poll them constantly. The tests run with `PULSE_ENABLED=false`.
