# 0006 — Take Pix payments through Mercado Pago, behind a gateway interface

- **Status:** Accepted
- **Date:** 2026-10-31
- **Issues:** [#33](https://github.com/lopesDilceu/ReniceProject/issues/33)

## Context

v1 had no payment step at all. The checkout procedure marked orders as completed immediately (BUG-02). Phase 4 needs real payments, and Pix is the method a Brazilian store cannot do without. The issue asked for a provider sandbox, Mercado Pago or Stripe, with the choice recorded here.

The project owner was offered both, with Mercado Pago recommended, and chose to proceed without a preference. This record is therefore the recommendation, accepted. Because it sits behind an interface, it is cheap to revisit.

## Decision

**Mercado Pago**, through its Payments API (`/v1/payments`). The code depends on an `App\Payments\PaymentGateway` interface and never calls Mercado Pago directly. Two implementations ship:

- `MercadoPagoGateway` (`PAYMENT_GATEWAY=mercadopago`), for the sandbox with test credentials and for production;
- `FakeGateway` (`PAYMENT_GATEWAY=fake`, the default). It generates real QR codes locally and lets the order page simulate the payment, so the store and its public demo run without any account.

## Why Mercado Pago

- **Pix is its home market.** Pix is created with the same endpoint as any payment (`payment_method_id: "pix"`). The response carries the "copia e cola" code and the QR image (`point_of_interaction.transaction_data`), so the store has nothing to render or host. ([Mercado Pago — Pix](https://www.mercadopago.com.br/developers/pt/docs/checkout-api-payments/integration-configuration/integrate-pix))
- **Everything this project needs to demonstrate is documented:**
  - an `X-Idempotency-Key` header for safe retries;
  - signed webhooks (an `x-signature` HMAC-SHA256 over the notified id, the request id and a timestamp);
  - retries until the endpoint answers. ([Mercado Pago — Webhooks](https://www.mercadopago.com.br/developers/en/docs/your-integrations/notifications/webhooks))
- **Audience.** For a portfolio aimed at the Brazilian market, it is the provider local recruiters recognize.
- **Stripe** would also work. Its strengths (cards worldwide, developer tooling) matter less for a Pix-only Brazilian store, and nothing in this design prevents adding it later as another `PaymentGateway`.

## Consequences

- **Webhooks are configured in the Mercado Pago panel ("Suas integrações"), not per payment.** Mercado Pago only signs notifications configured there. A `notification_url` sent with each payment would arrive without a verifiable signature. The endpoint is `POST /webhooks/mercadopago`.
- **The notification body is never trusted.** The webhook stores the notification once (unique per notification id) and queues a job. The job reads the payment's status back from `GET /v1/payments/{id}`, so a forged or replayed body cannot mark an order paid.
- **Pix expires with the order.** Mercado Pago requires a Pix expiration between 30 minutes and 30 days, so the default payment window is now 60 minutes. A shorter `SHOP_PAYMENT_WINDOW_MINUTES` still produces a 31-minute charge. When an order is cancelled or expires, its charge is cancelled at the provider.
- **Late payments.** If a Pix is approved after its order was cancelled (a race at the deadline), the order stays cancelled, the payment is recorded as approved, and a warning is logged. The refund is issued from the provider panel. Automatic refunds are future work.
- **Refunds made in the provider panel** reach the store through the same webhook: a refunded payment moves a paid order to "Reembolsado" and returns its stock.
