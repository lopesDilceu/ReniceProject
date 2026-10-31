<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierPurchaseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $cost = str_replace(['R$', ' ', '.'], '', (string) $this->input('unit_cost'));
        $cost = str_replace(',', '.', $cost);

        $this->merge(['unit_cost_cents' => is_numeric($cost) ? (int) round(((float) $cost) * 100) : null]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')->withoutTrashed()],
            'supplier' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'between:1,10000'],
            'unit_cost' => ['required', 'string'],
            'unit_cost_cents' => ['required', 'integer', 'min:0', 'max:10000000'],
            'purchased_at' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['unit_cost_cents.required' => 'Informe um custo válido, por exemplo 4,50.'];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'produto',
            'supplier' => 'fornecedor',
            'quantity' => 'quantidade',
            'unit_cost' => 'custo unitário',
            'purchased_at' => 'data da compra',
        ];
    }
}
