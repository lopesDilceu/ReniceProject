<?php

namespace App\Http\Requests\Catalog;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

/**
 * v1 accepted reviews from anyone, for any product, with any rating
 * (SEC-05). Now ReviewPolicy decides who may review and the rules below
 * bound the content.
 */
class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product && $this->user()?->can('review', $product) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['rating' => 'nota', 'comment' => 'comentário'];
    }
}
