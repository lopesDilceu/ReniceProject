<?php

namespace App\Http\Resources;

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
            ...self::format($this->toSnapshot()),
        ];
    }

    /**
     * Shared by saved addresses and the snapshot stored on orders.
     *
     * @param  array<string, mixed>  $address
     * @return array<string, mixed>
     */
    public static function format(array $address): array
    {
        $cep = (string) ($address['cep'] ?? '');

        return [
            ...$address,
            'cep' => strlen($cep) === 8 ? substr($cep, 0, 5).'-'.substr($cep, 5) : $cep,
        ];
    }
}
