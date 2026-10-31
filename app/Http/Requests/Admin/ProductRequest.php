<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

/**
 * Create and update products. The price is typed in reais ("11,25") and
 * stored in cents. v1's create form had its price validation commented out
 * and never saved the price (BUG-04); stock is not editable here, it only
 * changes through inventory movements.
 */
class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $price = (string) $this->input('price');
        $normalized = str_replace(['R$', ' ', '.'], '', $price);
        $normalized = str_replace(',', '.', $normalized);

        $this->merge([
            'price_cents' => is_numeric($normalized) ? (int) round(((float) $normalized) * 100) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique(Product::class)->ignore($product instanceof Product ? $product->getKey() : null)->withoutTrashed(),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'string'],
            'price_cents' => ['required', 'integer', 'min:1', 'max:10000000'],
            'image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(3 * 1024)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price_cents.required' => 'Informe um preço válido, por exemplo 11,25.',
            'price_cents.min' => 'O preço deve ser maior que zero.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nome', 'description' => 'descrição', 'price' => 'preço', 'image' => 'imagem'];
    }
}
