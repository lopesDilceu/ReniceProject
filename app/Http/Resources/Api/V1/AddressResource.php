<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Address
 */
class AddressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            ...self::fields($this->toSnapshot()),
        ];
    }

    /**
     * The address fields, shared with the snapshot stored on each order.
     *
     * @param  array<string, mixed>  $address
     * @return array{cep: string, street: string, number: string, complement: string|null, neighborhood: string, city: string, state: string}
     */
    public static function fields(array $address): array
    {
        $cep = (string) ($address['cep'] ?? '');

        return [
            /** Formatted as 00000-000. */
            'cep' => strlen($cep) === 8 ? substr($cep, 0, 5).'-'.substr($cep, 5) : $cep,
            'street' => (string) ($address['street'] ?? ''),
            'number' => (string) ($address['number'] ?? ''),
            'complement' => isset($address['complement']) ? (string) $address['complement'] : null,
            'neighborhood' => (string) ($address['neighborhood'] ?? ''),
            'city' => (string) ($address['city'] ?? ''),
            /** Two-letter state code (UF). */
            'state' => (string) ($address['state'] ?? ''),
        ];
    }
}
