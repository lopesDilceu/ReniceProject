# REST API (v1)

The store's catalog, cart and orders over JSON, at `/api/v1`. The contract is [`openapi.json`](openapi.json) (OpenAPI 3.1). It is generated from the code by [Scramble](https://scramble.dedoc.co) and browsable at **`/docs/api`** on any running instance.

## Authentication

Exchange the account's e-mail and password for a bearer token:

```bash
curl -X POST http://localhost/api/v1/tokens \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"email": "cliente@renice.test", "password": "password", "device_name": "curl"}'
# {"token": "1|AbC...", "abilities": ["read", "write"], "expires_at": "2026-11-30T18:00:00+00:00"}
```

- **Lifetime:** tokens expire after 30 days. `DELETE /api/v1/tokens/current` revokes one earlier. Closing the account revokes all of them.
- **Two-factor:** accounts with two-factor authentication on must also send `"code"`, the 6-digit code from the app. A token is never a way around the second factor.
- **Scopes:** `read` (GET) and `write` (cart changes and orders). Ask for fewer with `"abilities": ["read"]`. A missing scope answers 403.

Then send `Authorization: Bearer <token>`:

```bash
curl http://localhost/api/v1/cart -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN"
```

## Endpoints

| Method | Path | Auth | |
|---|---|---|---|
| POST | `/tokens` | — | Issue a token |
| DELETE | `/tokens/current` | token | Revoke the current token |
| GET | `/products` | — | Catalog: `q`, `sort`, `available`, `page`; 12 per page |
| GET | `/products/{slug}` | — | One product |
| GET | `/cart` | read | The token owner's cart |
| POST | `/cart/items` | write | Add a product (`product_id`, `quantity`) |
| PATCH | `/cart/items/{id}` | write | Change a quantity |
| DELETE | `/cart/items/{id}` | write | Remove an item |
| GET | `/addresses` | read | Saved addresses, for `address_id` |
| GET | `/orders` | read | Orders, newest first; 10 per page |
| GET | `/orders/{id}` | read | One order with items, timeline and Pix |
| POST | `/orders` | write | Place an order from the cart (`address_id`); needs a confirmed e-mail |

`POST /orders` answers `201` with the order and its Pix charge. Pay with `payment.pix_code` (copia e cola) or `payment.pix_qr_image` before `payment_due_at`. The order then moves to `paid` through the Mercado Pago webhook (see [payments](../payments.md)).

## Conventions

- **Money** is in integer cents with `currency: "BRL"`. Dates are ISO 8601 in UTC.
- **Exact stock is never exposed**, only `available` and `low_stock`.
- **Errors** are always JSON. The status codes are `401` (no or expired token), `403` (missing scope, someone else's resource, unconfirmed e-mail), `404`, `422` (`{"message", "errors": {field: [...]}}`) and `429`.
- **Rate limits:**
  - 60 requests per minute per token;
  - the public catalog, 60 per minute per IP;
  - `POST /tokens`, 5 per minute per e-mail and IP.
- **Versioning:** the API has its own resources (`App\Http\Resources\Api\V1`), separate from the web pages. Breaking changes go to `/api/v2`.

## Keeping the contract honest

- **Contract tests:** `tests/Feature/Api/V1/ApiContractTest.php` validates real responses (success and errors) against `openapi.json`.
- **Up-to-date check:** `OpenApiDocumentTest`, in the PostgreSQL CI job, regenerates the document and fails if the committed one is out of date. After changing an endpoint, regenerate it against PostgreSQL:

```bash
docker compose exec laravel.test php artisan scramble:export
```
