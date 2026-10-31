<?php

namespace App\Payments\MercadoPago;

use Illuminate\Http\Request;

/**
 * Checks the x-signature header Mercado Pago puts on webhook notifications:
 * an HMAC-SHA256 of "id:{data.id};request-id:{x-request-id};ts:{ts};" with
 * the webhook secret. Parts missing from the request are left out of the
 * manifest; an alphanumeric data.id is lowercased.
 */
final class WebhookSignature
{
    public function __construct(private readonly string $secret) {}

    public function isValid(Request $request): bool
    {
        if ($this->secret === '') {
            return false;
        }

        $parts = [];

        foreach (explode(',', (string) $request->header('x-signature')) as $pair) {
            [$key, $value] = array_pad(explode('=', trim($pair), 2), 2, '');
            $parts[$key] = $value;
        }

        if (empty($parts['ts']) || empty($parts['v1'])) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', self::manifest($request, $parts['ts']), $this->secret), $parts['v1']);
    }

    public static function manifest(Request $request, string $ts): string
    {
        $dataId = self::dataId($request);
        $requestId = (string) $request->header('x-request-id');

        return ($dataId !== '' ? 'id:'.(ctype_alnum($dataId) ? strtolower($dataId) : $dataId).';' : '')
            .($requestId !== '' ? "request-id:{$requestId};" : '')
            ."ts:{$ts};";
    }

    /**
     * The notified resource id: the data.id query parameter (PHP turns the
     * dot into an underscore), falling back to the body.
     */
    public static function dataId(Request $request): string
    {
        return (string) ($request->query('data_id') ?? $request->input('data.id', ''));
    }
}
