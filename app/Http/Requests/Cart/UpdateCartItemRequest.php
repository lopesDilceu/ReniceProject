<?php

namespace App\Http\Requests\Cart;

use App\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Quantity must be a positive whole number within available stock. v1
 * accepted anything, including zero and negative values (SEC-03).
 */
class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $item instanceof CartItem && $this->user()?->can('update', $item) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var CartItem $item */
        $item = $this->route('item');

        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:'.max(1, $item->product->stock)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.max' => 'Só temos :max unidades disponíveis.',
        ];
    }
}
