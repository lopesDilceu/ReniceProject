<?php

namespace App\Http\Requests\Checkout;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The only input checkout takes is which of the customer's own addresses to
 * ship to. The cart is resolved from the authenticated user: v1 took a cart
 * id from the URL and interpolated it into SQL (SEC-02).
 */
class PlaceOrderRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                Rule::exists(Address::class, 'id')->where('user_id', $this->user()?->getKey()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address_id.required' => 'Escolha um endereço de entrega.',
            'address_id.exists' => 'Escolha um endereço de entrega.',
        ];
    }

    public function address(): Address
    {
        return $this->user()->addresses()->findOrFail($this->integer('address_id'));
    }
}
