<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaymentNotification;
use App\Models\WebhookCall;
use App\Payments\MercadoPago\WebhookSignature;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Receives Mercado Pago notifications. Answers fast (the provider waits 22
 * seconds, then retries every 15 minutes): verify the signature, store the
 * notification once, queue the work.
 */
class MercadoPagoWebhookController extends Controller
{
    public function __invoke(Request $request, WebhookSignature $signature): Response
    {
        if (! $signature->isValid($request)) {
            return response('Invalid signature.', 401);
        }

        $type = (string) ($request->input('type') ?? $request->query('type'));
        $paymentId = WebhookSignature::dataId($request);

        if ($type !== 'payment' || $paymentId === '') {
            return response('Ignored.', 200);
        }

        // The notification id is the same on every retry of it.
        $key = ['provider' => 'mercadopago', 'event_key' => (string) ($request->input('id') ?: $request->header('x-request-id'))];

        // ON CONFLICT DO NOTHING: a repeated notification inserts nothing,
        // without an exception that would abort a surrounding transaction.
        $inserted = WebhookCall::query()->insertOrIgnore([
            ...$key,
            'type' => $type,
            'payload' => json_encode($request->all(), JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);

        if ($inserted === 0) {
            return response('Already received.', 200);
        }

        ProcessPaymentNotification::dispatch(WebhookCall::query()->where($key)->sole(), $paymentId);

        return response('Received.', 200);
    }
}
