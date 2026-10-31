<?php

namespace App\Http\Requests\Catalog;

use App\Enums\CatalogSort;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::enum(CatalogSort::class)],
            'available' => ['nullable', 'boolean'],
        ];
    }

    public function search(): ?string
    {
        $search = trim((string) $this->validated('q'));

        return $search === '' ? null : $search;
    }

    public function sort(): CatalogSort
    {
        return CatalogSort::tryFrom((string) $this->validated('sort')) ?? CatalogSort::Featured;
    }

    public function onlyAvailable(): bool
    {
        return $this->boolean('available');
    }
}
