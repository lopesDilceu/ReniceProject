# Showcase

What Renice looks like today, next to the 2024 version it came from. Every v2 picture comes from the production Docker image running the demo seed. [Run it yourself](#run-it-yourself) to click through it.

## A purchase, start to finish

Catalog → cart → checkout → Pix QR code → payment confirmed. The payment is simulated: the demo uses a local fake gateway, which goes through the same code path as a Mercado Pago webhook ([payments](payments.md)).

![Purchase flow: product, cart, checkout, Pix and payment confirmed](images/v2/purchase-flow.gif)

## Before and after

On the left, [v1.0-academic](legacy/README.md) (June 2024). On the right, v2.

| v1 (2024) | v2 (2026) |
|---|---|
| ![v1 home](legacy/images/01-home.jpg) | ![v2 home](images/v2/01-home.jpg) |
| **Home** | Rebuilt in React with Tailwind and shadcn/ui; light, dark and system themes. |
| ![v1 catalog](legacy/images/03-product-catalog.jpg) | ![v2 catalog](images/v2/03-product-catalog.jpg) |
| **Catalog**: two products showed R$ 0,00 ([BUG-04](audit/initial-audit.md)) | Search, sorting, an in-stock filter and ratings. Semantic search when an AI provider is set ([search](search.md)). |
| ![v1 cart](legacy/images/04-cart.jpg) | ![v2 cart](images/v2/04-cart.jpg) |
| **Cart** | Quantities checked against stock on the server; money in integer cents. |
| ![v1 order confirmation](legacy/images/06-order-confirmation.jpg) | ![v2 Pix payment](images/v2/06-order-pix.jpg) |
| **Order confirmation**: the order was marked completed at once, with no payment | A Pix charge with a QR code. Stock stays reserved for 60 minutes; unpaid orders are cancelled and the stock released. |
| ![v1 my orders](legacy/images/07-my-orders.jpg) | ![v2 my orders](images/v2/07-my-orders.jpg) |
| **My orders** | |
| ![v1 order details](legacy/images/08-order-details.jpg) | ![v2 order details](images/v2/08-order-details.jpg) |
| **Order details** | A timeline of every status change, and reviews only for products the customer received. |
| ![v1 product review](legacy/images/09-product-review.jpg) | ![v2 product page](images/v2/05-product.jpg) |
| **Reviews** | Rating distribution on the product page, and an AI pros-and-cons summary when a provider is set ([review summaries](review-summaries.md)). |
| ![v1 admin dashboard](legacy/images/12-admin-dashboard.jpg) | ![v2 admin dashboard](images/v2/12-admin-dashboard.jpg) |
| **Admin dashboard** | Revenue against the previous 30 days, best sellers, low stock and recent orders, updated live over WebSockets. |
| ![v1 admin products](legacy/images/13-admin-products.jpg) | ![v2 admin products](images/v2/13-admin-products.jpg) |
| **Admin products** | |
| ![v1 new product](legacy/images/14-admin-new-product.jpg) | ![v2 new product](images/v2/14-admin-new-product.jpg) |
| **New product** | Validated on the server, with an image upload; prices are stored as integer cents. |

## New in v2

| | |
|---|---|
| ![Order paid](images/v2/06-order-paid.jpg) | ![Admin order](images/v2/16-admin-order.jpg) |
| **Paid**: the order page turns to "Pago" by itself when the Pix is confirmed. | **Admin order**: who changed what, when and why; refunds and cancellations need a reason. |
| ![Stock ledger](images/v2/17-admin-inventory.jpg) | ![Account security](images/v2/11-account-security.jpg) |
| **Stock ledger**: append-only; every unit in or out has a source, and the balance never goes below zero, even under concurrent checkouts. | **Security**: passkeys and two-factor authentication. |
| ![Admin orders](images/v2/15-admin-orders.jpg) | ![Admin reviews](images/v2/18-admin-reviews.jpg) |
| **Orders**: filter by status, search by number or customer, live updates. | **Reviews**: admins can remove abusive ones. |
| ![API docs](images/v2/20-api-docs.jpg) | ![Pulse](images/v2/19-pulse.jpg) |
| **REST API**: OpenAPI 3.1 generated from the code and contract-tested ([API](api/README.md)). | **Monitoring**: Laravel Pulse for admins, next to `/health` ([observability](observability.md)). |
| ![Home in dark mode](images/v2/21-home-dark.jpg) | ![Catalog in dark mode](images/v2/22-catalog-dark.jpg) |
| **Dark mode.** | |

| | |
|---|---|
| ![Catalog on a phone](images/v2/23-catalog-phone.jpg) | ![Product on a phone](images/v2/24-product-phone.jpg) |
| **On a phone.** | |

Not in the pictures: the [MCP server](mcp.md) for AI agents, the [tests and CI](metrics.md), and the [decisions](adr/README.md) behind all of it. The [case study](case-study.md) tells the whole story.

## Run it yourself

Only Docker is needed. From a clone of the repository:

```bash
docker compose -f compose.demo.yaml up --build
```

Open http://localhost:8000 and sign in as `cliente@renice.test` or `admin@renice.test` (password `password`). It builds the production image, starts PostgreSQL with pgvector and loads fresh demo data on every start. Payments are simulated. Stop it with Ctrl+C; `docker compose -f compose.demo.yaml down -v` also deletes the database.

For development (hot reload, Mailpit, queue, Reverb), see [Running locally](../README.md#running-locally).

## How the pictures are made

[`scripts/showcase/`](../scripts/showcase) drives the installed Chrome with Playwright against that demo:

```bash
node scripts/showcase/capture.mjs   # screenshots into docs/images/v2
node scripts/showcase/record.mjs    # the purchase GIF (needs ffmpeg)
```

Restart the demo between the two (`docker compose -f compose.demo.yaml restart app`): each script places an order as the demo customer.
