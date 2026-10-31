<?php

namespace App\Http\Requests\Cart;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')->withoutTrashed()],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function product(): Product
    {
        return Product::query()->findOrFail($this->integer('product_id'));
    }
}
